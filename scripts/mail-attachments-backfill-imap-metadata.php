#!/usr/bin/env php
<?php
declare(strict_types=1);

use App\Core\Database\PdoFactory;
use App\Core\Mail\MailImapSyncService;
use App\Support\SecretBox;

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';

$o = getopt('', ['tenant:', 'user:', 'account:', 'message:', 'dry-run', 'apply']);
$tenantId = (int) ($o['tenant'] ?? 0);
$userId = (int) ($o['user'] ?? 0);
$accountId = (int) ($o['account'] ?? 0);
$messageId = (int) ($o['message'] ?? 0);
$dryRun = isset($o['dry-run']) || !isset($o['apply']);

if ($tenantId <= 0 || $userId <= 0 || $accountId <= 0 || $messageId <= 0) {
    fwrite(STDERR, "Uso: php scripts/mail-attachments-backfill-imap-metadata.php --tenant=1 --user=1 --account=1 --message=16 --dry-run|--apply\n");
    exit(1);
}

$pdo = PdoFactory::make((array) ($app['config']['database'] ?? []));
$m = $pdo->prepare("SELECT m.*, f.name AS folder_name, f.system_name AS folder_system_name FROM mail_messages m LEFT JOIN mail_folders f ON f.id=m.folder_id AND f.tenant_id=m.tenant_id WHERE m.tenant_id=:t AND m.user_id=:u AND m.id=:id LIMIT 1");
$m->execute([':t' => $tenantId, ':u' => $userId, ':id' => $messageId]);
$msg = $m->fetch(PDO::FETCH_ASSOC);
if (!is_array($msg)) { echo json_encode(['ok' => false, 'error' => 'message_not_found'], JSON_PRETTY_PRINT) . PHP_EOL; exit(2); }

$folderSystem = mb_strtolower(trim((string) ($msg['folder_system_name'] ?? '')));
$folderName = trim((string) ($msg['folder_name'] ?? ''));
$imapFolder = $folderSystem === 'inbox' ? 'INBOX' : ($folderName !== '' ? $folderName : 'INBOX');

$uid = (int) ($msg['provider_message_uid'] ?? 0);
if ($uid <= 0) {
    $ext = (string) ($msg['external_message_id'] ?? '');
    if (preg_match('/imap-uid:(\d+)/', $ext, $mm)) {
        $uid = (int) $mm[1];
    }
}
if ($uid <= 0) {
    echo json_encode(['ok' => false, 'message_id' => $messageId, 'error' => 'missing_message_uid_or_locator'], JSON_PRETTY_PRINT) . PHP_EOL;
    exit(2);
}

$svc = new MailImapSyncService($pdo, new SecretBox());
$d = $svc->debugMessageForUser($tenantId, $userId, $accountId, $imapFolder, $uid);
if (($d['ok'] ?? false) !== true) { echo json_encode(['ok' => false, 'message_id' => $messageId, 'error' => 'imap_message_not_found'], JSON_PRETTY_PRINT) . PHP_EOL; exit(2); }

$r = $pdo->prepare('SELECT id, original_filename, mime_type, size_bytes, raw_payload_json FROM mail_external_attachments WHERE tenant_id=:t AND message_id=:m ORDER BY id ASC');
$r->execute([':t' => $tenantId, ':m' => $messageId]);
$rows = $r->fetchAll(PDO::FETCH_ASSOC) ?: [];

$matched = count($rows); $updated = 0; $missing = 0;
foreach ($rows as $row) {
    $raw = json_decode((string) ($row['raw_payload_json'] ?? ''), true);
    if (!is_array($raw)) { $raw = []; }
    $raw['imap_folder'] = $imapFolder;
    $raw['imap_uid'] = $uid;
    if (empty($raw['imap_part_number'])) { $raw['imap_part_number'] = '1'; }
    $raw['imap_attachment_name'] = (string) ($row['original_filename'] ?? 'attachment');

    if (!$dryRun) {
        $u = $pdo->prepare('UPDATE mail_external_attachments SET raw_payload_json=:j, error_message=NULL, import_status=CASE WHEN import_status="imported" THEN import_status ELSE "pending" END WHERE id=:id');
        $u->execute([':j' => json_encode($raw, JSON_UNESCAPED_UNICODE), ':id' => (int) $row['id']]);
        $updated++;
    }
}

echo json_encode(['ok' => true, 'message_id' => $messageId, 'dry_run' => $dryRun, 'matched' => $matched, 'updated' => $updated, 'missing' => $missing], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
