#!/usr/bin/env php
<?php
declare(strict_types=1);
use App\Core\Cloud\CloudPath;use App\Core\Cloud\UserCloudRootProvisioner;use App\Core\Cloud\CloudStorageService;use App\Core\Database\PdoFactory;use Aws\S3\S3Client;
$root=dirname(__DIR__);require_once $root.'/vendor/autoload.php';$app=require $root.'/bootstrap/app.php';$config=(array)($app['config']??[]);
$o=getopt('', ['tenant:','user:','dry-run','apply','delete-old-markers']);$tenant=(int)($o['tenant']??0);$user=(int)($o['user']??0);$apply=array_key_exists('apply',$o);$del=array_key_exists('delete-old-markers',$o);
$pdo=PdoFactory::make((array)($config['database']??[]));$storage=new CloudStorageService($config,class_exists('Aws\\S3\\S3Client'));$provisioner=new UserCloudRootProvisioner($pdo,$storage,$config);
$rootPrefix=CloudPath::normalizeRootPrefix($user);$report=['ok'=>true,'actions'=>[],'old_markers'=>[]];
if($apply){$provisioner->provisionForUser($tenant,$user);} // ensures uploads/trash and required system folders
$s3cfg=(array)(($config['cloud']??[])['s3']??[]);$bucket=(string)($s3cfg['bucket']??'');$s3=new S3Client(['version'=>'latest','region'=>(string)($s3cfg['region']??'us-east-1'),'credentials'=>['key'=>(string)($s3cfg['access_key_id']??''),'secret'=>(string)($s3cfg['secret_access_key']??'')]]);
foreach(['uploads','trash','mail','products','campaigns','generated'] as $p){$key=$rootPrefix.$p.'/.keep';$report['actions'][]=['marker'=>$p,'apply'=>$apply];if($apply){$s3->putObject(['Bucket'=>$bucket,'Key'=>$key,'Body'=>'','ServerSideEncryption'=>'AES256']);}}
$prefix=$rootPrefix.$user.'/';
$resp=$s3->listObjectsV2(['Bucket'=>$bucket,'Prefix'=>$prefix,'MaxKeys'=>200]);
foreach((array)($resp['Contents']??[]) as $obj){$key=(string)($obj['Key']??'');$report['old_markers'][]=['tail'=>substr($key,-40),'scope'=>'duplicated_user_segment'];if($apply&&$del){$s3->deleteObject(['Bucket'=>$bucket,'Key'=>$key]);}}
echo json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
