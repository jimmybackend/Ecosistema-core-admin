#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Core\Cloud\CloudPath;
use App\Core\Database\PdoFactory;
use Aws\S3\S3Client;

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

$options = getopt('', ['tenant:', 'user:', 'dry-run', 'apply']);
$tenantId = (int)($options['tenant'] ?? 0);
$userId = (int)($options['user'] ?? 0);
$apply = array_key_exists('apply', $options);
$retentionDays = 15;

if ($tenantId <= 0 || $userId <= 0) {
    echo json_encode([
        'ok' => false,
        'message' => 'Parámetros inválidos. Usa --tenant y --user con enteros > 0.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit(1);
}

$pdo = PdoFactory::make((array)($config['database'] ?? []));

$s3Config = (array)(($config['cloud'] ?? [])['s3'] ?? []);
$bucket = (string)($s3Config['bucket'] ?? '');
$s3 = null;
if ($apply) {
    $s3 = new S3Client([
        'version' => 'latest',
        'region' => (string)($s3Config['region'] ?? 'us-east-1'),
        'credentials' => [
            'key' => (string)($s3Config['access_key_id'] ?? ''),
            'secret' => (string)($s3Config['secret_access_key'] ?? ''),
        ],
    ]);
}

$stmt = $pdo->prepare('SELECT id, s3_key FROM cloud_files WHERE tenant_id = :t AND user_id = :u AND status = :status AND deleted_at <= (NOW() - INTERVAL 15 DAY)');
$stmt->execute([':t' => $tenantId, ':u' => $userId, ':status' => 'deleted']);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

$summary = [
    'ok' => true,
    'tenant_id' => $tenantId,
    'user_id' => $userId,
    'mode' => $apply ? 'apply' : 'dry-run',
    'retention_days' => $retentionDays,
    'candidates' => count($rows),
    'purged' => 0,
    'skipped' => 0,
    'errors' => 0,
];

$trashPrefix = CloudPath::normalizeRootPrefix($userId) . 'trash/';

foreach ($rows as $row) {
    $fileId = (int)($row['id'] ?? 0);
    $s3Key = (string)($row['s3_key'] ?? '');

    if (!str_starts_with($s3Key, $trashPrefix)) {
        $summary['skipped']++;
        continue;
    }

    if (!$apply) {
        continue;
    }

    try {
        $s3?->deleteObject(['Bucket' => $bucket, 'Key' => $s3Key]);

        $pdo->prepare('UPDATE cloud_files SET found_in_s3 = 0, updated_at = NOW() WHERE id = :id AND tenant_id = :t AND user_id = :u')
            ->execute([':id' => $fileId, ':t' => $tenantId, ':u' => $userId]);

        $pdo->prepare('INSERT INTO cloud_file_access_logs (tenant_id, file_id, user_id, action, metadata_json, created_at) VALUES (:t, :f, :u, :a, :m, NOW())')
            ->execute([
                ':t' => $tenantId,
                ':f' => $fileId,
                ':u' => $userId,
                ':a' => 'delete',
                ':m' => json_encode(['reason' => 'trash_purge_15d'], JSON_UNESCAPED_UNICODE),
            ]);

        $summary['purged']++;
    } catch (Throwable $e) {
        $summary['errors']++;
    }
}

echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
