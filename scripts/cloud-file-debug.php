#!/usr/bin/env php
<?php
declare(strict_types=1);

use App\Core\Cloud\CloudS3Service;
use App\Core\Database\PdoFactory;

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';
if (!class_exists(\Aws\S3\S3Client::class)) { fwrite(STDERR, "AWS SDK no disponible\n"); exit(2);} 
$app = require $root . '/bootstrap/app.php';
$config = is_array($app['config'] ?? null) ? $app['config'] : [];
$options = getopt('', ['tenant:', 'user:', 'file:', 'head-object']);
$tenant = (int)($options['tenant'] ?? 0); $user=(int)($options['user'] ?? 0); $fileId=(int)($options['file'] ?? 0);
$pdo = PdoFactory::make((array)($config['database'] ?? []));
$stmt=$pdo->prepare('SELECT id, original_name, size_bytes, mime_type, status, found_in_s3, checksum_sha256, etag, bucket_id, root_id, folder_id, uploaded_at, s3_key FROM cloud_files WHERE tenant_id=:tenant AND user_id=:user AND id=:id LIMIT 1');
$stmt->execute([':tenant'=>$tenant,':user'=>$user,':id'=>$fileId]);
$row=$stmt->fetch(PDO::FETCH_ASSOC);
if (!is_array($row)) { echo json_encode(['ok'=>false,'message'=>'Archivo no encontrado.'], JSON_PRETTY_PRINT).PHP_EOL; exit(1);} 
$out=[
 'ok'=>true,'file_id'=>(int)$row['id'],'original_name'=>(string)$row['original_name'],'size_bytes'=>(int)$row['size_bytes'],'mime_type'=>(string)$row['mime_type'],'status'=>(string)$row['status'],'found_in_s3'=>(int)$row['found_in_s3'],'checksum_present'=>trim((string)($row['checksum_sha256']??''))!=='' ,'etag_present'=>trim((string)($row['etag']??''))!=='' ,'bucket_id'=>(int)$row['bucket_id'],'root_id'=>(int)$row['root_id'],'folder_id'=>isset($row['folder_id'])?(int)$row['folder_id']:null,'uploaded_at'=>(string)$row['uploaded_at']
];
if (array_key_exists('head-object',$options)) {
  $s3=new CloudS3Service($config);
  try { $obj=$s3->getObject((string)$row['s3_key']); $out['head_object_ok']=(bool)($obj['ok']??false);} catch (Throwable) { $out['head_object_ok']=false; $out['error_type']='cloud_s3_error'; }
}
echo json_encode($out, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
