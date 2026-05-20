<?php
declare(strict_types=1);
require __DIR__ . '/../bootstrap/app.php';
use App\Core\Cloud\CloudUploadService;use App\Core\Cloud\CloudFileRepository;use App\Core\Cloud\CloudStorageService;use App\Core\Database\PdoFactory;
$config=require __DIR__.'/../config/app.php';
$tenant=1;$user=1;$file='/tmp/test-cloud.txt';file_put_contents($file,'test '.date('c'));
$pdo=PdoFactory::make($config['database']);
$service=new CloudUploadService(new CloudFileRepository($pdo),new CloudStorageService($config,class_exists('Aws\\S3\\S3Client')),$config);
$r=$service->upload($tenant,$user,['name'=>'test-cloud.txt','tmp_name'=>$file,'size'=>filesize($file),'error'=>0,'type'=>'text/plain']);
echo json_encode($r,JSON_PRETTY_PRINT).PHP_EOL;
