#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Core\Cloud\CloudPath;
use App\Core\Cloud\CloudS3Service;
use App\Core\Database\PdoFactory;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    echo json_encode([
        'ok' => false,
        'message' => 'vendor/autoload.php faltante. Ejecuta composer install.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit(2);
}
require_once $autoload;

$app = require $root . '/bootstrap/app.php';
$config = is_array($app['config'] ?? null) ? $app['config'] : [];

$options = getopt('', ['tenant:', 'user:', 'file:', 'head-object']);
$tenantId = (int)($options['tenant'] ?? 0);
$userId = (int)($options['user'] ?? 0);
$fileId = (int)($options['file'] ?? 0);

if ($tenantId <= 0 || $userId <= 0 || $fileId <= 0) {
    echo json_encode([
        'ok' => false,
        'message' => 'Parámetros inválidos. Usa --tenant --user --file con enteros > 0.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit(1);
}

$databaseConfig = is_array($config['database'] ?? null) ? $config['database'] : [];
$pdo = PdoFactory::make($databaseConfig);

$stmt = $pdo->prepare('SELECT id, original_name, status, found_in_s3, checksum_sha256, etag, s3_key FROM cloud_files WHERE tenant_id = :t AND user_id = :u AND id = :id LIMIT 1');
$stmt->execute([':t' => $tenantId, ':u' => $userId, ':id' => $fileId]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!is_array($row)) {
    echo json_encode([
        'ok' => false,
        'message' => 'Archivo no encontrado.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit(1);
}

$s3Key = (string)($row['s3_key'] ?? '');
$scope = CloudPath::keyScope($userId, $s3Key);

$output = [
    'ok' => true,
    'file_id' => (int)$row['id'],
    'original_name' => (string)$row['original_name'],
    'status' => (string)$row['status'],
    'found_in_s3' => ((int)($row['found_in_s3'] ?? 0)) === 1,
    'key_scope' => $scope,
    'duplicated_user_segment' => $scope === 'duplicated_user_segment',
    'expected_prefix' => 'users/' . $userId . '/uploads/',
    'checksum_present' => trim((string)($row['checksum_sha256'] ?? '')) !== '',
    'etag_present' => trim((string)($row['etag'] ?? '')) !== '',
];

if (array_key_exists('head-object', $options)) {
    $head = (new CloudS3Service($config))->getObject($s3Key);
    $output['head_object_ok'] = (bool)($head['ok'] ?? false);
}

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
