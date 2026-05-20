#!/usr/bin/env php
<?php
declare(strict_types=1);
use App\Core\Database\PdoFactory;use Aws\S3\S3Client;
$root=dirname(__DIR__);require_once $root.'/vendor/autoload.php';if(!class_exists(S3Client::class)){fwrite(STDERR,"AWS SDK no disponible\n");exit(2);} $app=require $root.'/bootstrap/app.php';$config=(array)($app['config']??[]);
$o=getopt('', ['tenant:','user:','dry-run','apply','delete-old']);$tenant=(int)($o['tenant']??0);$user=(int)($o['user']??0);$dry=array_key_exists('dry-run',$o);$apply=array_key_exists('apply',$o);$delete=array_key_exists('delete-old',$o);if($delete&&!$apply){fwrite(STDERR,"--delete-old requiere --apply\n");exit(1);} if(!$dry&&!$apply){$dry=true;}
$pdo=PdoFactory::make((array)($config['database']??[]));$s3cfg=(array)(($config['cloud']??[])['s3']??[]);$s3=new S3Client(['version'=>'latest','region'=>(string)($s3cfg['region']??'us-east-1'),'credentials'=>['key'=>(string)($s3cfg['access_key_id']??''),'secret'=>(string)($s3cfg['secret_access_key']??'')],'use_path_style_endpoint'=>(bool)($s3cfg['use_path_style_endpoint']??false)]);$bucket=(string)($s3cfg['bucket']??'');
$q=$pdo->prepare("SELECT id,original_name,stored_name,s3_key,uploaded_at FROM cloud_files WHERE tenant_id=:t AND user_id=:u AND s3_key NOT LIKE :p");$q->execute([':t'=>$tenant,':u'=>$user,':p'=>'users/'.$user.'/%']);$rows=$q->fetchAll(PDO::FETCH_ASSOC)?:[];
foreach($rows as $r){$dt=new DateTimeImmutable((string)($r['uploaded_at']?:'now'));$new='users/'.$user.'/uploads/'.$dt->format('Y').'/'.$dt->format('m').'/'.(string)$r['stored_name'];$old=(string)$r['s3_key'];$tailOld=substr($old,-24);$tailNew=substr($new,-24);echo json_encode(['file_id'=>(int)$r['id'],'original_name'=>(string)$r['original_name'],'safe_old_key_tail'=>$tailOld,'safe_new_key_tail'=>$tailNew],JSON_UNESCAPED_UNICODE).PHP_EOL; if($dry)continue;
try{$s3->headObject(['Bucket'=>$bucket,'Key'=>$old]);}catch(Throwable){echo json_encode(['file_id'=>(int)$r['id'],'ok'=>false,'error'=>'old_key_missing']).PHP_EOL;continue;}
$s3->copyObject(['Bucket'=>$bucket,'CopySource'=>$bucket.'/'.$old,'Key'=>$new,'ServerSideEncryption'=>'AES256']);$s3->headObject(['Bucket'=>$bucket,'Key'=>$new]);
$pdo->prepare('UPDATE cloud_files SET s3_key=:k, found_in_s3=1, status=:st, updated_at=NOW() WHERE id=:id AND tenant_id=:t AND user_id=:u')->execute([':k'=>$new,':st'=>'active',':id'=>(int)$r['id'],':t'=>$tenant,':u'=>$user]);
$pdo->prepare('UPDATE cloud_file_versions SET s3_key=:k WHERE file_id=:id AND tenant_id=:t')->execute([':k'=>$new,':id'=>(int)$r['id'],':t'=>$tenant]);
$pdo->prepare('INSERT INTO cloud_file_access_logs (tenant_id,file_id,user_id,action,metadata_json,created_at) VALUES (:t,:f,:u,:a,:m,NOW())')->execute([':t'=>$tenant,':f'=>(int)$r['id'],':u'=>$user,':a'=>$delete?'repair/delete_old':'repair',':m'=>json_encode(['safe_old_key_tail'=>$tailOld,'safe_new_key_tail'=>$tailNew])]);
if($delete){$s3->deleteObject(['Bucket'=>$bucket,'Key'=>$old]);}
}
