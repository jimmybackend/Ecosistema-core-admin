#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Core\Database\PdoFactory;
use App\Core\Mail\MailImapSyncService;
use App\Support\SecretBox;

require_once dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$config = $app['config'] ?? [];

$options = getopt('', ['tenant:', 'user:', 'account:', 'folder::', 'uid::', 'headers-only::']);
$tenantId = (int) ($options['tenant'] ?? 0);
$userId = (int) ($options['user'] ?? 0);
$accountId = (int) ($options['account'] ?? 0);

if ($tenantId <= 0 || $userId <= 0 || $accountId <= 0) {
    fwrite(STDERR, "Uso: php scripts/mail-imap-debug.php --tenant=1 --user=1 --account=1 [--folder=INBOX --uid=73 --headers-only]\n");
    exit(1);
}

$pdo = PdoFactory::make($config['database'] ?? []);
$service = new MailImapSyncService($pdo, new SecretBox());
$folder = (string) ($options['folder'] ?? '');
$uid = (int) ($options['uid'] ?? 0);
$headersOnly = array_key_exists('headers-only', $options);
$result = ($folder !== '' && $uid > 0)
    ? $service->debugMessageForUser($tenantId, $userId, $accountId, $folder, $uid)
    : $service->debugConnectForUser($tenantId, $userId, $accountId);

if (($result['ok'] ?? false) !== true) {
    echo "IMAP_DEBUG_ERROR " . (string) ($result['error'] ?? 'Error desconocido') . PHP_EOL;
    if (isset($result['context']) && is_array($result['context'])) {
        echo json_encode($result['context'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    }
    exit(2);
}

echo "IMAP_DEBUG_OK\n";
if (isset($result['message']) && is_array($result['message'])) {
    $m = $result['message'];
    echo sprintf("uid=%d\n", (int)($m['uid'] ?? 0));
    echo sprintf("folder=%s\n", (string)($m['folder'] ?? ''));
    echo sprintf("subject=%s\n", (string)($m['subject'] ?? ''));
    echo sprintf("date=%s\n", (string)($m['date'] ?? ''));
    echo sprintf("from=%s\n", (string)($m['from'] ?? ''));
    echo "to=" . json_encode((array)($m['to'] ?? []), JSON_UNESCAPED_UNICODE) . PHP_EOL;
    echo "cc=" . json_encode((array)($m['cc'] ?? []), JSON_UNESCAPED_UNICODE) . PHP_EOL;
    echo "bcc=" . json_encode((array)($m['bcc'] ?? []), JSON_UNESCAPED_UNICODE) . PHP_EOL;
    echo "missing_from_address=" . (($m['missing_from_address'] ?? false) ? 'true' : 'false') . PHP_EOL;
    echo "header_names=" . json_encode((array)($m['header_names'] ?? []), JSON_UNESCAPED_UNICODE) . PHP_EOL;
    if ($headersOnly) {
        exit(0);
    }
}
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
