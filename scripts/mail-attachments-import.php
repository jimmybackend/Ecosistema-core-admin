<?php

declare(strict_types=1);

use App\Core\Database\PdoFactory;
use App\Core\Mail\MailAttachmentImportService;
use App\Support\SecretBox;

require __DIR__ . '/../bootstrap/app.php';

$options = getopt('', ['tenant:', 'user:', 'account::', 'message:', 'limit::']);
$tenantId = (int) ($options['tenant'] ?? 0);
$userId = (int) ($options['user'] ?? 0);
$messageId = (int) ($options['message'] ?? 0);
$limit = (int) ($options['limit'] ?? 5);
if ($tenantId <= 0 || $userId <= 0 || $messageId <= 0) {
    fwrite(STDERR, "Uso: php scripts/mail-attachments-import.php --tenant=1 --user=1 --account=1 --message=123 --limit=5\n");
    exit(1);
}
$config = require __DIR__ . '/../config/app.php';
$pdo = PdoFactory::make($config['database']);
$service = new MailAttachmentImportService($pdo, $config, new SecretBox());
$result = $service->importPendingForMessage($tenantId, $userId, $messageId, $limit);
if (($result['ok'] ?? false) !== true) {
    echo "error=" . (string)($result['error'] ?? 'No se pudo importar') . PHP_EOL;
    exit(2);
}
$c = (array) ($result['counts'] ?? []);
echo 'pending=' . (int) ($c['pending'] ?? 0) . PHP_EOL;
echo 'imported=' . (int) ($c['imported'] ?? 0) . PHP_EOL;
echo 'failed=' . (int) ($c['failed'] ?? 0) . PHP_EOL;
echo 'imap_pending=' . (int) ($c['imap_pending'] ?? 0) . PHP_EOL;
