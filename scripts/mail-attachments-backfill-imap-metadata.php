#!/usr/bin/env php
<?php
declare(strict_types=1);

use App\Core\Database\PdoFactory;
use App\Core\Mail\MailSmtpAccountRepository;
use App\Support\SecretBox;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Message;

$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';

$o = getopt('', ['tenant:', 'user:', 'account:', 'message:', 'dry-run', 'apply', 'verbose-safe']);
$tenantId = (int) ($o['tenant'] ?? 0);
$userId = (int) ($o['user'] ?? 0);
$accountId = (int) ($o['account'] ?? 0);
$messageId = (int) ($o['message'] ?? 0);
$dryRun = isset($o['dry-run']) || !isset($o['apply']);
$verboseSafe = isset($o['verbose-safe']);

if ($tenantId <= 0 || $userId <= 0 || $accountId <= 0 || $messageId <= 0) {
    fwrite(STDERR, "Uso: php scripts/mail-attachments-backfill-imap-metadata.php --tenant=1 --user=1 --account=1 --message=16 --dry-run|--apply [--verbose-safe]\n");
    exit(1);
}

/**
 * @return never
 */
function failJson(string $error, string $stage, int $messageId, int $exitCode = 2, ?Throwable $exception = null): void
{
    $payload = ['ok' => false, 'message_id' => $messageId, 'error' => $error, 'stage' => $stage];
    if ($exception !== null) {
        $payload['exception_class'] = get_class($exception);
        $payload['message'] = mb_substr(trim(preg_replace('/\s+/', ' ', $exception->getMessage()) ?? ''), 0, 220);
    }
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit($exitCode);
}

$pdo = PdoFactory::make((array) ($app['config']['database'] ?? []));
try {
    $m = $pdo->prepare("SELECT m.*, f.name AS folder_name, f.system_name AS folder_system_name FROM mail_messages m LEFT JOIN mail_folders f ON f.id = m.folder_id AND f.tenant_id = m.tenant_id AND f.mailbox_id = m.mailbox_id WHERE m.tenant_id = :t AND m.user_id = :u AND m.id = :id LIMIT 1");
    $m->execute([':t' => $tenantId, ':u' => $userId, ':id' => $messageId]);
    $msg = $m->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    failJson('db_query_error', 'resolve_message', $messageId, 2, $e);
}
if (!is_array($msg)) { failJson('message_not_found', 'resolve_message', $messageId); }

try {
    $account = (new MailSmtpAccountRepository($pdo))->findForUserOrTenant($tenantId, $userId, $accountId);
} catch (PDOException $e) {
    failJson('db_query_error', 'resolve_account', $messageId, 2, $e);
}
if (!is_array($account) || (string) ($account['status'] ?? '') !== 'active') { failJson('mail_account_not_found', 'resolve_account', $messageId); }
if (trim((string) ($account['host_in'] ?? '')) === '' || (int) ($account['port_in'] ?? 0) <= 0 || trim((string) ($account['username'] ?? '')) === '' || trim((string) ($account['password_encrypted'] ?? '')) === '') {
    failJson('mail_account_imap_incomplete', 'resolve_account', $messageId);
}

$folderSystem = mb_strtolower(trim((string) ($msg['folder_system_name'] ?? '')));
$folderName = trim((string) ($msg['folder_name'] ?? ''));
$imapFolder = $folderSystem === 'inbox' ? 'INBOX' : ($folderName !== '' ? $folderName : 'INBOX');
$subject = trim((string) ($msg['subject'] ?? ''));
$fromAddress = mb_strtolower(trim((string) ($msg['from_address'] ?? '')));
$receivedAt = trim((string) ($msg['received_at'] ?? ''));
$headers = (string) ($msg['raw_headers'] ?? '');
$externalMessageId = trim((string) ($msg['external_message_id'] ?? ''));
$messageIdHeader = null;
if (preg_match('/^Message-ID:\s*<([^>]+)>/mi', $headers, $mh)) { $messageIdHeader = '<' . trim($mh[1]) . '>'; }

try {
    $r = $pdo->prepare('SELECT id, original_filename, mime_type, size_bytes, raw_payload_json, error_message, import_status FROM mail_external_attachments WHERE tenant_id=:t AND message_id=:m ORDER BY id ASC');
    $r->execute([':t' => $tenantId, ':m' => $messageId]);
    $rows = $r->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    failJson('db_query_error', 'resolve_message', $messageId, 2, $e);
}
$targetNames = array_values(array_filter(array_map(fn($x)=>(string)($x['original_filename']??''), $rows)));

$uidHints = [];
if (preg_match('/imap-uid:(\d+)/', $externalMessageId, $mm)) { $uidHints[] = (int) $mm[1]; }
if (preg_match('/X-UID:\s*(\d+)/mi', $headers, $mx)) { $uidHints[] = (int) $mx[1]; }
if (preg_match('/X-UIDL:\s*(\d+)/mi', $headers, $mxl)) { $uidHints[] = (int) $mxl[1]; }
$uidHints = array_values(array_unique(array_filter($uidHints)));

$secretBox = new SecretBox();
$pwd = (string) $secretBox->decrypt((string) $account['password_encrypted']);
$encRaw = mb_strtolower(trim((string) ($account['ssl_in'] ?? '')));
$encryption = in_array($encRaw, ['ssl','tls'], true) ? $encRaw : false;
$client = (new ClientManager(['options'=>['fetch'=>2]]))->make([
    'host'=>(string)$account['host_in'],'port'=>(int)$account['port_in'],'encryption'=>$encryption,'validate_cert'=>true,
    'username'=>(string)$account['username'],'password'=>$pwd,'protocol'=>'imap'
]);

$verbose = [
    'folder_used'=>$imapFolder,
    'has_message_id'=>$messageIdHeader !== null,
    'has_external_message_id'=>$externalMessageId !== '',
    'search_strategy'=>[],
    'candidate_count'=>0,
    'matched_attachment_count'=>0,
    'matched_filenames'=>[],
];

try {
    $client->connect();
    $folder = $client->getFolder($imapFolder) ?? $client->getFolder('INBOX');
    if ($folder === null) { throw new RuntimeException('imap_folder_not_found'); }

    $candidates = [];
    foreach ($uidHints as $uid) {
        $message = $folder->query()->getMessageByUid($uid);
        if ($message instanceof Message) { $candidates[] = ['message'=>$message,'strategy'=>'uid_direct']; }
    }
    if ($candidates === [] && $messageIdHeader !== null) {
        $verbose['search_strategy'][] = 'message_id';
        $msgs = $folder->messages()->all()->since(date('d M Y', strtotime(($receivedAt ?: 'now') . ' -2 day')))->before(date('d M Y', strtotime(($receivedAt ?: 'now') . ' +2 day')))->limit(200)->get();
        foreach ($msgs as $mobj) {
            if (!$mobj instanceof Message) { continue; }
            $raw = (string) ($mobj->getHeader()?->raw ?? '');
            if (stripos($raw, 'Message-ID: ' . $messageIdHeader) !== false) { $candidates[] = ['message'=>$mobj,'strategy'=>'message_id']; }
        }
    }
    if ($candidates === []) {
        $verbose['search_strategy'][] = 'metadata_window';
        $since = date('d M Y', strtotime(($receivedAt ?: 'now') . ' -2 day'));
        $before = date('d M Y', strtotime(($receivedAt ?: 'now') . ' +2 day'));
        $msgs = $folder->messages()->all()->since($since)->before($before)->limit(300)->get();
        foreach ($msgs as $mobj) {
            if (!$mobj instanceof Message) { continue; }
            $fromStr = mb_strtolower((string) ($mobj->getFrom() ?? ''));
            $subj = trim((string) ($mobj->getSubject() ?? ''));
            $attachments = $mobj->getAttachments();
            if ($fromAddress !== '' && $fromStr !== '' && !str_contains($fromStr, $fromAddress)) { continue; }
            if ($subject !== '' && $subj !== '' && mb_strtolower($subject) !== mb_strtolower($subj)) { continue; }
            if (count($attachments) <= 0) { continue; }
            $names = [];
            foreach ($attachments as $att) { $names[] = (string) ($att->name ?? ''); }
            $intersect = array_intersect(array_map('mb_strtolower',$targetNames), array_map('mb_strtolower',$names));
            if ($targetNames !== [] && $intersect === []) { continue; }
            $candidates[] = ['message'=>$mobj,'strategy'=>'metadata_window'];
        }
    }

    $uniqueByUid = [];
    foreach ($candidates as $c) { $uniqueByUid[(string)((int)($c['message']->getUid() ?? 0))] = $c; }
    $candidates = array_values($uniqueByUid);
    $verbose['candidate_count'] = count($candidates);

    if (count($candidates) > 1) {
        echo json_encode(['ok'=>false,'message_id'=>$messageId,'error'=>'ambiguous_imap_message_match','candidate_count'=>count($candidates),'verbose_safe'=>$verbose], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
        $client->disconnect(); exit(2);
    }
    if ($candidates === []) {
        echo json_encode(['ok'=>false,'message_id'=>$messageId,'error'=>'imap_message_not_found','verbose_safe'=>$verbose], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
        $client->disconnect(); exit(2);
    }

    $chosen = $candidates[0];
    $message = $chosen['message'];
    $verbose['search_strategy'][] = $chosen['strategy'];
    $imapUid = (int) ($message->getUid() ?? 0);
    $attachmentMap = [];
    foreach ($message->getAttachments() as $att) {
        $name = (string) ($att->name ?? 'attachment');
        $attachmentMap[mb_strtolower($name)][] = $att;
    }

    $updated = 0;
    foreach ($rows as $row) {
        $nameKey = mb_strtolower((string) ($row['original_filename'] ?? ''));
        $match = $attachmentMap[$nameKey][0] ?? null;
        if ($match === null) { continue; }
        $raw = json_decode((string) ($row['raw_payload_json'] ?? ''), true); if (!is_array($raw)) { $raw = []; }
        $raw['imap_folder'] = (string) ($folder->path ?? $imapFolder);
        $raw['imap_uid'] = $imapUid;
        $raw['imap_part_number'] = (string) ($match->part_number ?? '1');
        $raw['imap_attachment_name'] = (string) ($match->name ?? $row['original_filename'] ?? 'attachment');
        $raw['imap_content_id'] = (string) ($match->id ?? '');
        $raw['imap_mime_type'] = (string) ($match->content_type ?? $row['mime_type'] ?? 'application/octet-stream');

        $verbose['matched_filenames'][] = (string) ($row['original_filename'] ?? '');
        if (!$dryRun) {
            $u = $pdo->prepare('UPDATE mail_external_attachments SET raw_payload_json=:j, error_message=NULL, import_status=CASE WHEN import_status="imported" THEN import_status ELSE "pending" END WHERE id=:id');
            $u->execute([':j'=>json_encode($raw, JSON_UNESCAPED_UNICODE), ':id'=>(int)$row['id']]);
            $updated++;
        }
    }
    $verbose['matched_attachment_count'] = count($verbose['matched_filenames']);
    $resp = ['ok'=>true,'message_id'=>$messageId,'dry_run'=>$dryRun,'updated'=>$updated,'matched'=>$verbose['matched_attachment_count']];
    if ($verboseSafe) { $resp['verbose_safe'] = $verbose; }
    echo json_encode($resp, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE).PHP_EOL;
    $client->disconnect();
} catch (PDOException $e) {
    failJson('db_query_error', 'search_imap', $messageId, 2, $e);
} catch (Throwable $e) {
    failJson('imap_message_not_found', 'search_imap', $messageId);
}
