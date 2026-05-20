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
require $root . '/bootstrap/app.php';
$config = require __DIR__ . '/../config/app.php';
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

$pdo = PdoFactory::make($config['database']);
$service = new CloudUploadService(new CloudFileRepository($pdo), new CloudStorageService($config, class_exists('Aws\\S3\\S3Client')), $config);
$result = $service->upload($tenant, $user, [
    'name' => basename($file),
    'tmp_name' => $file,
    'size' => filesize($file) ?: 0,
    'error' => 0,
    'type' => mime_content_type($file) ?: 'text/plain',
]);

echo json_encode([
    'ok' => (bool)($result['ok'] ?? false),
    'file_id' => $result['id'] ?? null,
    'original_name' => basename($file),
    'size' => filesize($file) ?: 0,
    'mime' => mime_content_type($file) ?: 'text/plain',
    'status' => ($result['ok'] ?? false) ? 'active' : 'error',
    'message' => $result['message'] ?? null,
], JSON_PRETTY_PRINT) . PHP_EOL;
