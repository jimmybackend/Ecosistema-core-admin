<?php
declare(strict_types=1);
require __DIR__ . '/../bootstrap/app.php';
use App\Core\Cloud\CloudS3Service;use App\Core\Cloud\UserCloudRootProvisioner;use App\Core\Cloud\CloudStorageService;use App\Core\Database\PdoFactory;
$config=require __DIR__.'/../config/app.php';
$tenant=(int)($_SERVER['argv'][1]??1);$user=(int)($_SERVER['argv'][2]??1);
$pdo=PdoFactory::make($config['database']);
$s3=new CloudS3Service($config);$chk=$s3->checkBucket();
$prov=(new UserCloudRootProvisioner($pdo,new CloudStorageService($config,class_exists('Aws\\S3\\S3Client')),$config))->provisionForUser($tenant,$user);
echo json_encode(['bucket'=>$config['cloud']['s3']['bucket']??'','region'=>$config['cloud']['s3']['region']??'','check_s3'=>$chk,'bucket_id'=>$prov['bucket_id']??null,'root_id'=>$prov['root_id']??null],JSON_PRETTY_PRINT).PHP_EOL;
