#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Core\Cloud\CloudS3Service;
use App\Core\Cloud\CloudStorageService;
use App\Core\Cloud\UserCloudRootProvisioner;
use App\Core\Database\PdoFactory;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    echo "VENDOR_AUTOLOAD_MISSING: ejecuta composer install\n";
    exit(2);
}
require_once $autoload;
if (!class_exists(\Aws\S3\S3Client::class)) {
    echo "AWS_SDK_MISSING: ejecuta composer require aws/aws-sdk-php:^3.325\n";
    exit(2);
}
$app = require $root . '/bootstrap/app.php';
$config = is_array($app['config'] ?? null) ? $app['config'] : [];
$options = getopt('', ['tenant::', 'user::', 'check-s3', 'ensure-root']);
$tenant = (int)($options['tenant'] ?? 1);
$user = (int)($options['user'] ?? 1);

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
$s3 = new CloudS3Service($config);
$check = isset($options['check-s3']) ? $s3->checkBucket() : ['ok' => null, 'message' => 'skip'];

$provision = ['bucket_id' => null, 'root_id' => null];
if (isset($options['ensure-root'])) {
    $provision = (new UserCloudRootProvisioner($pdo, new CloudStorageService($config, true), $config))
        ->provisionForUser($tenant, $user);
}

$bucket = (string)($config['cloud']['s3']['bucket'] ?? '');
$safeBucket = $bucket === '' ? '' : substr($bucket, 0, 3) . '***';

echo json_encode([
    'tenant_id' => $tenant,
    'user_id' => $user,
    'bucket' => $safeBucket,
    'region' => (string)($config['cloud']['s3']['region'] ?? ''),
    'head_bucket_ok' => (bool)($check['ok'] ?? false),
    'error_type' => $check['error_type'] ?? null,
    'message' => $check['message'] ?? null,
    'bucket_id' => $provision['bucket_id'] ?? null,
    'root_id' => $provision['root_id'] ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
