#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Core\Database\PdoFactory;
use App\Core\Mail\MailImapSyncService;
use App\Support\SecretBox;

require_once dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$config = $app['config'] ?? [];

$options = getopt('', ['tenant:', 'user:', 'account:']);
$tenantId = (int) ($options['tenant'] ?? 0);
$userId = (int) ($options['user'] ?? 0);
$accountId = (int) ($options['account'] ?? 0);

if ($tenantId <= 0 || $userId <= 0 || $accountId <= 0) {
    fwrite(STDERR, "Uso: php scripts/mail-imap-debug.php --tenant=1 --user=1 --account=1\n");
    exit(1);
}

$pdo = PdoFactory::make($config['database'] ?? []);
$service = new MailImapSyncService($pdo, new SecretBox());
$result = $service->debugConnectForUser($tenantId, $userId, $accountId);

if (($result['ok'] ?? false) !== true) {
    echo "IMAP_DEBUG_ERROR " . (string) ($result['error'] ?? 'Error desconocido') . PHP_EOL;
    if (isset($result['context']) && is_array($result['context'])) {
        echo json_encode($result['context'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    }
    exit(2);
}

echo "IMAP_DEBUG_OK\n";
$folders = (array) ($result['folders'] ?? []);
if ($folders === []) {
    echo "FOLDERS: (sin carpetas)\n";
    exit(0);
}
foreach ($folders as $folder) {
    echo ' - ' . $folder . PHP_EOL;
}

$samples = (array) ($result['samples'] ?? []);
if ($samples !== []) {
    echo "SAMPLES:\n";
    foreach ($samples as $sample) {
        if (!is_array($sample)) {
            continue;
        }
        echo sprintf(
            " - uid=%d | date=%s | from=%s | subject=%s\n",
            (int) ($sample['uid'] ?? 0),
            (string) ($sample['date'] ?? 'n/a'),
            (string) ($sample['from'] ?? 'n/a'),
            (string) ($sample['subject'] ?? 'n/a')
        );
    }
}
