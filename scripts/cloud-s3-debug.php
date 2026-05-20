#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Core\Cloud\CloudS3Service;
use App\Core\Cloud\CloudStorageService;
use App\Core\Cloud\UserCloudRootProvisioner;
use App\Core\Database\PdoFactory;

require __DIR__ . '/../bootstrap/app.php';
$config = require __DIR__ . '/../config/app.php';
$options = getopt('', ['tenant::', 'user::', 'check-s3', 'ensure-root']);
$tenant = (int)($options['tenant'] ?? 1);
$user = (int)($options['user'] ?? 1);

if (!class_exists('Aws\\S3\\S3Client')) {
    echo "AWS_SDK_MISSING: ejecuta composer require aws/aws-sdk-php:^3.325\n";
    exit(2);
}

$pdo = PdoFactory::make($config['database']);
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
