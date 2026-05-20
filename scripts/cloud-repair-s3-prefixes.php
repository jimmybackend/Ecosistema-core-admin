#!/usr/bin/env php
<?php
declare(strict_types=1);
use App\Core\Cloud\CloudPath;use App\Core\Database\PdoFactory;use Aws\S3\S3Client;
$root=dirname(__DIR__);require_once $root.'/vendor/autoload.php';$app=require $root.'/bootstrap/app.php';$config=(array)($app['config']??[]);
$o=getopt('', ['tenant:','user:','dry-run','apply','delete-old-markers','debug']);$tenant=(int)($o['tenant']??0);$user=(int)($o['user']??0);$dry=array_key_exists('dry-run',$o)||!array_key_exists('apply',$o);$apply=array_key_exists('apply',$o);$delMarkers=array_key_exists('delete-old-markers',$o);$debug=array_key_exists('debug',$o);
$pdo=PdoFactory::make((array)($config['database']??[]));$s3cfg=(array)(($config['cloud']??[])['s3']??[]);$bucket=(string)($s3cfg['bucket']??'');$s3=new S3Client(['version'=>'latest','region'=>(string)($s3cfg['region']??'us-east-1'),'credentials'=>['key'=>(string)($s3cfg['access_key_id']??''),'secret'=>(string)($s3cfg['secret_access_key']??'')]]);
$stmt=$pdo->prepare('SELECT id,original_name,stored_name,s3_key,uploaded_at FROM cloud_files WHERE tenant_id=:t AND user_id=:u');$stmt->execute([':t'=>$tenant,':u'=>$user]);$rows=$stmt->fetchAll(PDO::FETCH_ASSOC)?:[];
$wrongMarkers=['users/'.$user.'/'.$user.'/mail','users/'.$user.'/'.$user.'/products','users/'.$user.'/'.$user.'/campaigns','users/'.$user.'/'.$user.'/generated'];
foreach($rows as $r){$old=(string)$r['s3_key'];$scope=CloudPath::keyScope($user,$old);if($scope==='ok')continue; $dt=new DateTimeImmutable((string)($r['uploaded_at']?:'now'));$new='users/'.$user.'/uploads/'.$dt->format('Y').'/'.$dt->format('m').'/'.(string)$r['stored_name'];
 echo json_encode(['file_id'=>(int)$r['id'],'original_name'=>(string)$r['original_name'],'old_scope'=>$scope,'new_scope'=>'ok','safe_old_key_tail'=>substr($old,-28),'safe_new_key_tail'=>substr($new,-28)],JSON_UNESCAPED_UNICODE).PHP_EOL;
 if(!$apply)continue;
 try{$s3->headObject(['Bucket'=>$bucket,'Key'=>$old]);$s3->copyObject(['Bucket'=>$bucket,'CopySource'=>$bucket.'/'.$old,'Key'=>$new,'ServerSideEncryption'=>'AES256']);$s3->headObject(['Bucket'=>$bucket,'Key'=>$new]);}catch(Throwable){continue;}
 $pdo->prepare('UPDATE cloud_files SET s3_key=:k, found_in_s3=1, updated_at=NOW() WHERE id=:id AND tenant_id=:t AND user_id=:u')->execute([':k'=>$new,':id'=>(int)$r['id'],':t'=>$tenant,':u'=>$user]);
 $pdo->prepare('UPDATE cloud_file_versions SET s3_key=:k WHERE file_id=:id AND tenant_id=:t')->execute([':k'=>$new,':id'=>(int)$r['id'],':t'=>$tenant]);
 $pdo->prepare('INSERT INTO cloud_file_access_logs (tenant_id,file_id,user_id,action,metadata_json,created_at) VALUES (:t,:f,:u,:a,:m,NOW())')->execute([':t'=>$tenant,':f'=>(int)$r['id'],':u'=>$user,':a'=>'repair',':m'=>json_encode(['safe_old_key_tail'=>substr($old,-28),'safe_new_key_tail'=>substr($new,-28)])]);
}
if($apply){foreach(['uploads','mail','products','campaigns','generated'] as $p){$s3->putObject(['Bucket'=>$bucket,'Key'=>'users/'.$user.'/'.$p.'/.keep','Body'=>'','ServerSideEncryption'=>'AES256']);}}
foreach($wrongMarkers as $m){echo json_encode(['marker_report'=>$m,'marker_without_trailing_slash'=>true],JSON_UNESCAPED_UNICODE).PHP_EOL; if($apply&&$delMarkers){$ok='users/'.$user.'/'.basename($m).'/.keep'; try{$s3->headObject(['Bucket'=>$bucket,'Key'=>$ok]);$s3->deleteObject(['Bucket'=>$bucket,'Key'=>$m]);}catch(Throwable){}}}
