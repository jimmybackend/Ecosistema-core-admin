#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Core\Cloud\CloudFileRepository;
use App\Core\Cloud\CloudStorageService;
use App\Core\Cloud\CloudUploadService;
use App\Core\Database\PdoFactory;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "vendor/autoload.php faltante. Ejecuta composer install.\n");
    exit(1);
}
require_once $autoload;
if (!class_exists(\Aws\S3\S3Client::class)) {
    echo json_encode([
        'ok' => false,
        'error_code' => 'AWS_SDK_MISSING',
        'message' => 'AWS SDK no está disponible después de Composer autoload.',
    ], JSON_PRETTY_PRINT) . PHP_EOL;
    exit(2);
}

$app = require $root . '/bootstrap/app.php';
$config = is_array($app['config'] ?? null) ? $app['config'] : [];
$options = getopt('', ['tenant::', 'user::', 'file::', 'folder::']);
$tenant = (int)($options['tenant'] ?? 1);
$user = (int)($options['user'] ?? 1);
$file = (string)($options['file'] ?? '/tmp/test-cloud.txt');

passthru('php ' . escapeshellarg(__DIR__ . '/cloud-schema-debug.php') . ' --tenant=' . $tenant . ' --user=' . $user, $schemaCode);
if ($schemaCode !== 0) {
    echo "cloud_schema_error: schema incompatible\n";
    exit(1);
}

if (!is_file($file)) {
    file_put_contents($file, 'test ' . date('c'));
}

$databaseConfig = is_array($config['database'] ?? null) ? $config['database'] : [];
if ($databaseConfig === []) {
    echo json_encode([
        'ok' => false,
        'error_code' => 'CLOUD_CONFIG_ERROR',
        'message' => 'Configuración de base de datos no disponible.',
    ], JSON_PRETTY_PRINT) . PHP_EOL;
    exit(1);
}

try {
    $pdo = PdoFactory::make($databaseConfig);
} catch (Throwable $e) {
    echo json_encode([
        'ok' => false,
        'error_code' => 'CLOUD_CONFIG_ERROR',
        'message' => 'No fue posible inicializar la conexión PDO.',
    ], JSON_PRETTY_PRINT) . PHP_EOL;
    exit(1);
}
$service = new CloudUploadService(new CloudFileRepository($pdo), new CloudStorageService($config, class_exists('Aws\\S3\\S3Client')), $config);
$result = $service->upload($tenant, $user, [
    'name' => basename($file),
    'tmp_name' => $file,
    'size' => filesize($file) ?: 0,
    'error' => 0,
    'type' => mime_content_type($file) ?: 'text/plain',
]);

$payload = [
    'ok' => (bool)($result['ok'] ?? false),
    'file_id' => $result['id'] ?? null,
    'original_name' => basename($file),
    'size' => filesize($file) ?: 0,
    'mime' => mime_content_type($file) ?: 'text/plain',
    'status' => ($result['ok'] ?? false) ? 'active' : 'error',
    'found_in_s3' => null,
    'checksum_present' => false,
    'etag_present' => false,
    'version_created' => false,
    'access_log_created' => false,
    'message' => $result['message'] ?? null,
];
if (($result['ok'] ?? false) && isset($result['id'])) {
    $fileId = (int)$result['id'];
    $f = $pdo->prepare('SELECT found_in_s3, checksum_sha256, etag FROM cloud_files WHERE id = :id AND tenant_id = :tenant AND user_id = :user LIMIT 1');
    $f->execute([':id'=>$fileId, ':tenant'=>$tenant, ':user'=>$user]);
    $fileRow = $f->fetch(PDO::FETCH_ASSOC) ?: [];
    $payload['found_in_s3'] = isset($fileRow['found_in_s3']) ? (int)$fileRow['found_in_s3'] : null;
    $payload['checksum_present'] = trim((string)($fileRow['checksum_sha256'] ?? '')) !== '';
    $payload['etag_present'] = trim((string)($fileRow['etag'] ?? '')) !== '';

    $v = $pdo->prepare('SELECT COUNT(*) FROM cloud_file_versions WHERE file_id = :file_id AND tenant_id = :tenant');
    $v->execute([':file_id'=>$fileId, ':tenant'=>$tenant]);
    $payload['version_created'] = ((int)$v->fetchColumn()) > 0;

    $a = $pdo->prepare('SELECT COUNT(*) FROM cloud_file_access_logs WHERE file_id = :file_id AND tenant_id = :tenant AND action = :action');
    $a->execute([':file_id'=>$fileId, ':tenant'=>$tenant, ':action'=>'upload']);
    $payload['access_log_created'] = ((int)$a->fetchColumn()) > 0;
}

echo json_encode($payload, JSON_PRETTY_PRINT) . PHP_EOL;
