#!/usr/bin/env php
<?php
declare(strict_types=1);
use App\Core\Cloud\CloudPath;use App\Core\Database\PdoFactory;
$root=dirname(__DIR__);require_once $root.'/vendor/autoload.php';$app=require $root.'/bootstrap/app.php';$config=(array)($app['config']??[]);
$o=getopt('', ['tenant:','user:']);$t=(int)($o['tenant']??0);$u=(int)($o['user']??0);
$pdo=PdoFactory::make((array)($config['database']??[]));
$r=$pdo->prepare('SELECT id,root_prefix FROM cloud_user_roots WHERE tenant_id=:t AND user_id=:u ORDER BY id DESC LIMIT 1');$r->execute([':t'=>$t,':u'=>$u]);$rootRow=$r->fetch(PDO::FETCH_ASSOC)?:[];
$f=$pdo->prepare('SELECT id,name,parent_folder_id,prefix,folder_type,is_system FROM cloud_folders WHERE tenant_id=:t AND user_id=:u AND is_deleted=0 ORDER BY id');$f->execute([':t'=>$t,':u'=>$u]);$folders=$f->fetchAll(PDO::FETCH_ASSOC)?:[];
$issues=[];$expectedRoot=CloudPath::normalizeRootPrefix($u);if(($rootRow['root_prefix']??'')!==$expectedRoot){$issues[]='folder_prefix_missing_root';}
$uploads=false;foreach($folders as $row){$p=(string)($row['prefix']??'');if($p===$expectedRoot.'uploads/'){$uploads=true;} if(str_starts_with($p,$expectedRoot.$u.'/')){$issues[]='duplicated_user_segment: '.$p;} if(!str_starts_with($p,$expectedRoot)){$issues[]='folder_prefix_outside_root: '.$p;} if(!str_ends_with($p,'/')){$issues[]='marker_without_trailing_slash: '.$p;}}
if(!$uploads){$issues[]='uploads_folder_missing';}
$out=['ok'=>count($issues)===0,'cloud_user_root'=>$rootRow,'folders'=>$folders,'issues'=>$issues?:['ok']];
echo json_encode($out,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
