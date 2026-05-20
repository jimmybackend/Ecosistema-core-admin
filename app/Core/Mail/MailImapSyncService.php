<?php

declare(strict_types=1);

namespace App\Core\Mail;

use App\Support\SecretBox;
use PDO;
use PDOException;
use Throwable;
use DateTimeImmutable;
use DateTimeInterface;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Message;
use Webklex\PHPIMAP\Attribute;
use Webklex\PHPIMAP\Address;

final class MailImapSyncService
{
    private const TABLES_WITH_UPDATED_AT = [
        'mail_smtp_accounts',
        'mail_mailboxes',
        'mail_inbound_accounts',
        'mail_messages',
        'mail_categories',
        'mail_classification_rules',
        'mail_domains',
        'mail_mailbox_counters',
        'mail_unified_inbox_views',
    ];

    private ?array $lastDbContext = null;

    public function __construct(private PDO $pdo, private SecretBox $secretBox)
    {
    }

    public function syncForUser(int $tenantId, int $userId, int $accountId, int $limit = 25): array
    {
        $limit = max(1, min(250, $limit));
        $account = $this->findAuthorizedAccount($tenantId, $userId, $accountId);
        if ($account === null) {
            return ['ok' => false, 'imported' => 0, 'skipped' => 0, 'attachments_pending' => 0, 'errors' => ['Cuenta IMAP no autorizada o inactiva.']];
        }

        if (!class_exists(ClientManager::class)) {
            return ['ok' => false, 'imported' => 0, 'skipped' => 0, 'attachments_pending' => 0, 'errors' => ['Cliente IMAP Composer no disponible en el servidor.']];
        }

        $mailboxId = (int) $account['mailbox_id'];
        $ownerUserId = (int) ($account['mailbox_user_id'] ?? 0) > 0 ? (int) $account['mailbox_user_id'] : $userId;
        $folderId = $this->ensureInboxFolder($tenantId, $mailboxId);
        if ($folderId <= 0) {
            return ['ok' => false, 'imported' => 0, 'skipped' => 0, 'attachments_pending' => 0, 'errors' => ['No se pudo preparar la carpeta INBOX.']];
        }

        [$password, $validationError] = $this->resolveImapPassword($account);
        if ($validationError !== null) {
            $safeContext = $this->buildSafeExceptionContext($account, $validationError, null, $tenantId, $userId, $accountId);
            $safeError = $this->buildSafeImapErrorMessage($safeContext);
            $this->updateSyncStatus($account, false, $safeError, $safeContext);
            return ['ok' => false, 'imported' => 0, 'skipped' => 0, 'attachments_pending' => 0, 'errors' => [$safeError]];
        }

        $result = ['ok' => true, 'imported' => 0, 'skipped' => 0, 'attachments_pending' => 0, 'errors' => []];

        try {
            $clientManager = new ClientManager(['options' => ['fetch' => 2]]);
            $client = $clientManager->make($this->buildClientConfig($account, (string) $password));
            $client->connect();

            $folder = $client->getFolder('INBOX');
            if ($folder === null) {
                throw new \RuntimeException('IMAP folder INBOX not found');
            }
            $messages = $folder->messages()->all()->setFetchOrder('desc')->limit($limit)->get();

            foreach ($messages as $message) {
                if (!$message instanceof Message) {
                    $result['skipped']++;
                    continue;
                }
                $uid = (int) ($message->getUid() ?? 0);
                try {
                    $headers = $this->webklexAttributeToString($message->getHeader()?->raw) ?? '';
                    $externalMessageId = $this->extractMessageId($this->webklexAttributeToString($message->getMessageId()) ?? '', $headers) ?? ('imap-uid:' . $uid);
                    if ($this->existsMessage($tenantId, $mailboxId, $externalMessageId)) {
                        $existingMessageId = $this->findMessageId($tenantId, $mailboxId, $externalMessageId);
                        if ($existingMessageId > 0) {
                            $this->refreshExternalAttachmentImapMetadata($tenantId, $existingMessageId, 'INBOX', $uid, $message);
                        }
                        $result['skipped']++;
                        continue;
                    }
                    $plainBody = trim((string) $message->getTextBody());
                    $htmlBody = trim((string) $message->getHTMLBody());
                    if ($plainBody === '') { $plainBody = trim(strip_tags($htmlBody)); }
                    $to = $this->webklexAddressesToEmailList($message->getTo());
                    $cc = $this->webklexAddressesToEmailList($message->getCc());
                    $bcc = $this->webklexAddressesToEmailList($message->getBcc());
                    $fromAddress = $this->extractFromAddress($message);
                    if ($fromAddress === null) {
                        $result['skipped']++;
                        $result['errors'][] = $this->safeJsonEncode([
                            'error_type' => 'imap_parse_error',
                            'stage' => 'message_parse',
                            'reason' => 'missing_from_address',
                            'folder' => 'INBOX',
                            'message_uid' => $uid,
                            'account_id' => $accountId,
                            'tenant_id' => $tenantId,
                            'user_id' => $userId,
                        ]);
                        continue;
                    }
                    $isRead = (bool) $message->getFlag('seen');
                    $isStarred = (bool) $message->getFlag('flagged');
                    $receivedDt = $this->webklexAttributeToDateTime($message->getDate()) ?? new DateTimeImmutable();
                    $receivedAt = $receivedDt->format('Y-m-d H:i:s');
                    $subject = $this->webklexAttributeToString($message->getSubject()) ?? '';
                    $attachmentParts = [];
                    foreach ($message->getAttachments() as $attachment) {
                        $filename = $this->webklexAttributeToString($attachment->name ?? null) ?? 'attachment';
                        $attachmentParts[] = [
                            'legacy_attachment_id' => $uid . ':' . ((string) ($attachment->part_number ?? count($attachmentParts) + 1)),
                            'imap_folder' => 'INBOX',
                            'imap_uid' => $uid,
                            'imap_part_number' => (string) ($attachment->part_number ?? count($attachmentParts) + 1),
                            'original_filename' => $filename,
                            'safe_filename' => $this->safeFileName($filename),
                            'mime_type' => $this->webklexAttributeToString($attachment->content_type ?? null) ?? 'application/octet-stream',
                            'size_bytes' => isset($attachment->size) ? (int) $attachment->size : null,
                        ];
                    }

                    $payload = [
                    ':tenant_id' => $tenantId, ':mailbox_id' => $mailboxId, ':folder_id' => $folderId, ':user_id' => $ownerUserId,
                    ':message_uuid' => $this->uuidV4(), ':thread_uuid' => $this->uuidV4(), ':external_provider' => 'imap', ':external_message_id' => $externalMessageId,
                    ':direction' => 'inbound', ':mail_scope' => 'normal', ':from_address' => $fromAddress,
                    ':to_addresses' => $this->safeJsonEncode($to), ':cc_addresses' => $cc === [] ? null : $this->safeJsonEncode($cc), ':bcc_addresses' => $bcc === [] ? null : $this->safeJsonEncode($bcc),
                    ':subject' => $subject,
                    ':body_text' => $plainBody, ':body_html' => $htmlBody, ':raw_headers' => $headers,
                    ':has_attachments' => $attachmentParts === [] ? 0 : 1, ':is_read' => $isRead ? 1 : 0, ':read_at' => $isRead ? date('Y-m-d H:i:s') : null,
                    ':is_starred' => $isStarred ? 1 : 0, ':is_draft' => 0, ':is_spam' => 0, ':is_deleted' => 0,
                    ':received_at' => $receivedAt, ':sent_at' => $receivedAt,
                ];
                    if (!$this->isValidInboundPayload($payload)) { $result['skipped']++; continue; }
                    $messageId = $this->insertMessage($payload);
                    if ($messageId <= 0) { $result['skipped']++; continue; }
                    $this->insertRecipients($tenantId, $messageId, $to, 'to');
                    $this->insertRecipients($tenantId, $messageId, $cc, 'cc');
                    $this->insertRecipients($tenantId, $messageId, $bcc, 'bcc');
                    $this->insertEvent($tenantId, $messageId, $ownerUserId, $uid, (string) $account['mailbox_full_address']);
                    foreach ($attachmentParts as $attachment) { $this->insertExternalAttachment($tenantId, $messageId, $attachment); $result['attachments_pending']++; }
                    $result['imported']++;
                } catch (Throwable $messageError) {
                    throw new \RuntimeException('message_parse stage=message_parse folder=INBOX uid='.$uid.' '.$messageError->getMessage(), 0, $messageError);
                }
            }

            $client->disconnect();
            $this->updateSyncStatus($account, true, null, null);
        } catch (Throwable $e) {
            $result['ok'] = false;
            $errorType = $this->classifyImapError($e);
            $safeContext = $this->buildSafeExceptionContext($account, $errorType, $e, $tenantId, $userId, $accountId);
            $safeError = $this->buildSafeImapErrorMessage($safeContext);
            $result['errors'][] = $safeError;
            $this->logSafeSyncError($safeContext);
            $this->updateSyncStatus($account, false, $safeError, $safeContext);
        }

        return $result;
    }

    public function debugConnectForUser(int $tenantId, int $userId, int $accountId): array
    {
        $account = $this->findAuthorizedAccount($tenantId, $userId, $accountId);
        if ($account === null) {
            return ['ok' => false, 'error' => 'Cuenta IMAP no autorizada o inactiva.'];
        }
        [$password, $validationError] = $this->resolveImapPassword($account);
        if ($validationError !== null) {
            $ctx = $this->buildSafeExceptionContext($account, $validationError, null, $tenantId, $userId, $accountId);
            return ['ok' => false, 'error' => $this->buildSafeImapErrorMessage($ctx), 'context' => $ctx];
        }

        try {
            $client = (new ClientManager(['options' => ['fetch' => 2]]))->make($this->buildClientConfig($account, (string) $password));
            $client->connect();
            $folders = [];
            foreach ($client->getFolders() as $folder) {
                $folders[] = (string) ($folder->path ?? $folder->name ?? '');
            }
            $samples = [];
            $inbox = $client->getFolder('INBOX');
            if ($inbox !== null) {
                $messages = $inbox->messages()->all()->setFetchOrder('desc')->limit(5)->get();
                foreach ($messages as $message) {
                    if (!$message instanceof Message) { continue; }
                    $dt = $this->webklexAttributeToDateTime($message->getDate());
                    $samples[] = [
                        'uid' => (int) ($message->getUid() ?? 0),
                        'subject' => $this->webklexAttributeToString($message->getSubject(), 180),
                        'from' => $this->webklexAttributeToString($message->getFrom(), 180),
                        'date' => $dt?->format('Y-m-d H:i:s'),
                    ];
                }
            }
            $client->disconnect();
            return ['ok' => true, 'folders' => array_values(array_filter($folders)), 'samples' => $samples];
        } catch (Throwable $e) {
            $ctx = $this->buildSafeExceptionContext($account, $this->classifyImapError($e), $e, $tenantId, $userId, $accountId);
            return ['ok' => false, 'error' => $this->buildSafeImapErrorMessage($ctx), 'context' => $ctx];
        }
    }
    public function debugMessageForUser(int $tenantId, int $userId, int $accountId, string $folderName, int $uid): array
    {
        $account = $this->findAuthorizedAccount($tenantId, $userId, $accountId);
        if ($account === null) { return ['ok' => false, 'error' => 'Cuenta IMAP no autorizada o inactiva.']; }
        [$password, $validationError] = $this->resolveImapPassword($account);
        if ($validationError !== null) { return ['ok' => false, 'error' => $validationError]; }
        try {
            $client = (new ClientManager(['options' => ['fetch' => 2]]))->make($this->buildClientConfig($account, (string) $password));
            $client->connect();
            $folder = $client->getFolder($folderName);
            if ($folder === null) { return ['ok' => false, 'error' => 'Carpeta no encontrada']; }
            $message = $folder->query()->getMessageByUid($uid);
            if (!$message instanceof Message) { return ['ok' => false, 'error' => 'Mensaje no encontrado']; }
            $headersRaw = $this->webklexAttributeToString($message->getHeader()?->raw) ?? '';
            preg_match_all('/^([\w\-]+):/m', $headersRaw, $headerMatches);
            $headers = array_values(array_unique(array_map('strtolower', (array)($headerMatches[1] ?? []))));
            $parsedFrom = $this->extractFromAddress($message);
            return ['ok' => true, 'message' => [
                'uid' => (int) ($message->getUid() ?? 0),
                'folder' => $folderName,
                'subject' => $this->shortText((string)($this->webklexAttributeToString($message->getSubject()) ?? ''), 180),
                'date' => $this->webklexAttributeToDateTime($message->getDate())?->format('Y-m-d H:i:s'),
                'from' => $parsedFrom,
                'to' => $this->webklexAddressesToEmailList($message->getTo()),
                'cc' => $this->webklexAddressesToEmailList($message->getCc()),
                'bcc' => $this->webklexAddressesToEmailList($message->getBcc()),
                'missing_from_address' => $parsedFrom === null,
                'header_names' => $headers,
            ]];
        } catch (Throwable $e) {
            return ['ok' => false, 'error' => $this->shortText($this->sanitizeSensitiveText($e->getMessage(), $account), 220)];
        }
    }

    private function buildSafeImapErrorMessage(array $safeContext): string
    {
        $host = (string) ($safeContext['host_in'] ?? '');
        $port = (int) ($safeContext['port_in'] ?? 0);
        $sslIn = (string) ($safeContext['ssl_in'] ?? 'none');
        $type = (string) ($safeContext['error_type'] ?? 'unknown_imap_error');
        $class = (string) ($safeContext['exception_class'] ?? 'n/a');
        $detail = (string) ($safeContext['exception_message_sanitized'] ?? 'sin detalle');
        return "No se pudo sincronizar IMAP con {$host}:{$port} {$sslIn}. Tipo: {$type}. Clase: {$class}. Detalle: {$detail}.";
    }

    private function buildClientConfig(array $account, string $password): array {
        $enc = strtolower(trim((string) ($account['imap_encryption'] ?? '')));
        return ['host'=>(string) $account['imap_host'],'port'=>(int) $account['imap_port'],'encryption'=>$enc === 'ssl' ? 'ssl' : ($enc === 'tls' ? 'tls' : false),'validate_cert'=>true,'username'=>(string) $account['imap_username'],'password'=>$password,'protocol'=>'imap','authentication'=>null,'timeout'=>30];
    }
    private function normalizeAddressList(mixed $addresses): array { return $this->webklexAddressesToEmailList($addresses); }
    private function webklexAddressesToEmailList(mixed $value): array {
        $emails = [];
        $queue = [$value];
        while ($queue !== []) {
            $current = array_shift($queue);
            $email = $this->webklexAddressToEmail($current);
            if ($email !== null) { $emails[] = $email; continue; }
            if (is_array($current) || $current instanceof \Traversable) { foreach ($current as $item) { $queue[] = $item; } continue; }
            if ($current instanceof Attribute) { $queue[] = $current->first(); continue; }
            if (is_object($current) && method_exists($current, 'first')) { $queue[] = $current->first(); continue; }
            if (is_object($current) && method_exists($current, 'toArray')) { $queue[] = $current->toArray(); continue; }
        }
        return array_values(array_unique(array_filter($emails)));
    }
    private function webklexAddressToEmail(mixed $value): ?string {
        if ($value === null) { return null; }
        if (is_string($value)) { return $this->extractEmailFromString($value); }
        if ($value instanceof Address) {
            $mail = $this->normalizeEmail((string) ($value->mail ?? '')); if ($mail !== null) { return $mail; }
            $mailbox = trim((string) ($value->mailbox ?? '')); $host = trim((string) ($value->host ?? ''));
            if ($mailbox !== '' && $host !== '') { $built = $this->normalizeEmail($mailbox.'@'.$host); if ($built !== null) { return $built; } }
            $full = $this->extractEmailFromString((string) ($value->full ?? '')); if ($full !== null) { return $full; }
            return $this->extractEmailFromString((string) $value);
        }
        if ($value instanceof Attribute) { return $this->webklexAddressToEmail($value->first()); }
        if (is_object($value) && method_exists($value, 'toString')) { $fromString = $this->extractEmailFromString((string) $value->toString()); if ($fromString !== null) { return $fromString; } }
        if (is_object($value) && method_exists($value, '__toString')) { $fromCast = $this->extractEmailFromString((string) $value); if ($fromCast !== null) { return $fromCast; } }
        if (is_object($value)) {
            $mail = $this->normalizeEmail((string) ($value->mail ?? '')); if ($mail !== null) { return $mail; }
            $mailbox = trim((string) ($value->mailbox ?? '')); $host = trim((string) ($value->host ?? ''));
            if ($mailbox !== '' && $host !== '') { $built = $this->normalizeEmail($mailbox.'@'.$host); if ($built !== null) { return $built; } }
            return $this->extractEmailFromString((string) ($value->full ?? ''));
        }
        return null;
    }
    private function extractFromAddress(Message $message): ?string {
        $candidates = [$message->getFrom(), $message->from ?? null, $this->extractHeaderValue($message, 'from'), $this->extractHeaderValue($message, 'sender'), $this->extractHeaderValue($message, 'reply-to'), $this->extractHeaderValue($message, 'return-path')];
        foreach ($candidates as $candidate) {
            $list = $this->webklexAddressesToEmailList($candidate);
            if ($list !== []) { return $list[0]; }
        }
        return null;
    }
    private function extractHeaderValue(Message $message, string $headerName): ?string {
        $headers = $this->webklexAttributeToString($message->getHeader()?->raw) ?? '';
        if ($headers === '') { return null; }
        if (preg_match('/^'.preg_quote($headerName, '/').':\s*(.+)$/mi', $headers, $m) !== 1) { return null; }
        return trim((string) ($m[1] ?? ''));
    }
    private function extractEmailFromString(string $value): ?string {
        $trimmed = trim($value);
        if ($trimmed === '') { return null; }
        if (preg_match('/<([^>]+)>/', $trimmed, $m) === 1) { return $this->normalizeEmail($m[1]); }
        if (preg_match('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $trimmed, $m) === 1) { return $this->normalizeEmail($m[0]); }
        return $this->normalizeEmail($trimmed);
    }
    private function normalizeEmail(string $value): ?string { $email = mb_strtolower(trim($value)); return $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? $email : null; }
    private function safeJsonEncode(array $value): string { return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '[]'; }
    private function isValidInboundPayload(array $payload): bool {
        if ($this->normalizeEmail((string) ($payload[':from_address'] ?? '')) === null) { return false; }
        if (!is_string($payload[':to_addresses'] ?? null)) { return false; }
        json_decode((string) $payload[':to_addresses'], true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array(json_decode((string) $payload[':to_addresses'], true))) { return false; }
        if (trim((string)($payload[':message_uuid'] ?? '')) === '' || trim((string)($payload[':direction'] ?? '')) !== 'inbound') { return false; }
        return ((int)($payload[':tenant_id'] ?? 0) > 0) && ((int)($payload[':mailbox_id'] ?? 0) > 0) && ((int)($payload[':user_id'] ?? 0) > 0);
    }
    private function findAuthorizedAccount(int $tenantId, int $userId, int $accountId): ?array { /* unchanged */
        $sql = "SELECT 'inbound' AS source_type, ia.id AS source_id, ia.tenant_id, ia.mailbox_id, ia.username AS imap_username, ia.password_encrypted, ia.host AS imap_host, ia.port AS imap_port, ia.encryption AS imap_encryption, ia.status, ia.created_by_user_id, 0 AS available_to_everyone, m.user_id AS mailbox_user_id, m.full_address AS mailbox_full_address FROM mail_inbound_accounts ia INNER JOIN mail_mailboxes m ON m.id = ia.mailbox_id AND m.tenant_id = ia.tenant_id WHERE ia.id = :account_id AND ia.tenant_id = :tenant_id AND ia.status = 'active' AND m.status = 'active' AND TRIM(COALESCE(ia.host, '')) <> '' AND ia.port IS NOT NULL AND TRIM(COALESCE(ia.username, '')) <> '' AND TRIM(COALESCE(ia.password_encrypted, '')) <> '' AND (ia.created_by_user_id = :created_by_user_id OR m.user_id = :mailbox_user_id OR m.available_to_everyone = 1) LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':account_id' => $accountId, ':tenant_id' => $tenantId, ':created_by_user_id' => $userId, ':mailbox_user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (is_array($row)) { return $row; }
        $sql = "SELECT 'smtp' AS source_type, s.id AS source_id, s.tenant_id, s.mailbox_id, s.username AS imap_username, s.password_encrypted, s.host_in AS imap_host, s.port_in AS imap_port, s.ssl_in AS imap_encryption, s.status, s.created_by_user_id, s.available_to_everyone, m.user_id AS mailbox_user_id, m.full_address AS mailbox_full_address FROM mail_smtp_accounts s INNER JOIN mail_mailboxes m ON m.id = s.mailbox_id AND m.tenant_id = s.tenant_id WHERE s.id = :account_id AND s.tenant_id = :tenant_id AND s.status = 'active' AND m.status = 'active' AND TRIM(COALESCE(s.host_in, '')) <> '' AND s.port_in IS NOT NULL AND TRIM(COALESCE(s.username, '')) <> '' AND TRIM(COALESCE(s.password_encrypted, '')) <> '' AND (s.created_by_user_id = :created_by_user_id OR m.user_id = :mailbox_user_id OR s.available_to_everyone = 1 OR m.available_to_everyone = 1) LIMIT 1";
        $stmt = $this->pdo->prepare($sql); $stmt->execute([':account_id' => $accountId, ':tenant_id' => $tenantId, ':created_by_user_id' => $userId, ':mailbox_user_id' => $userId]); $row = $stmt->fetch(PDO::FETCH_ASSOC); return is_array($row) ? $row : null;
    }
    private function ensureInboxFolder(int $tenantId, int $mailboxId): int { $stmt=$this->pdo->prepare('SELECT id FROM mail_folders WHERE tenant_id=:tenant_id AND mailbox_id=:mailbox_id AND system_name=:system_name ORDER BY id ASC LIMIT 1'); $this->executeWithDbContext($stmt,[':tenant_id'=>$tenantId,':mailbox_id'=>$mailboxId,':system_name'=>'inbox'],'select','mail_folders',['id']); $id=$stmt->fetchColumn(); if($id!==false){return(int)$id;} $ins=$this->pdo->prepare("INSERT INTO mail_folders (tenant_id, mailbox_id, name, system_name, sort_order, created_at) VALUES (:tenant_id,:mailbox_id,'INBOX','inbox',0,NOW())"); $this->executeWithDbContext($ins,[':tenant_id'=>$tenantId,':mailbox_id'=>$mailboxId],'insert','mail_folders',['tenant_id','mailbox_id','name','system_name','sort_order','created_at']); return (int)$this->pdo->lastInsertId(); }

    private function findMessageId(int $tenantId,int $mailboxId,string $externalMessageId): int { $s=$this->pdo->prepare('SELECT id FROM mail_messages WHERE tenant_id=:tenant_id AND mailbox_id=:mailbox_id AND external_provider=:provider AND external_message_id=:external_message_id LIMIT 1'); $s->execute([':tenant_id'=>$tenantId,':mailbox_id'=>$mailboxId,':provider'=>'imap',':external_message_id'=>$externalMessageId]); return (int)($s->fetchColumn()?:0); }
    private function refreshExternalAttachmentImapMetadata(int $tenantId,int $messageId,string $folder,int $uid,Message $message): void { $q=$this->pdo->prepare('SELECT id, original_filename, raw_payload_json FROM mail_external_attachments WHERE tenant_id=:tenant_id AND message_id=:message_id AND cloud_file_id IS NULL ORDER BY id ASC'); $q->execute([':tenant_id'=>$tenantId,':message_id'=>$messageId]); $rows=$q->fetchAll(PDO::FETCH_ASSOC)?:[]; if($rows===[]){return;} $imap=[]; foreach($message->getAttachments() as $att){ $name=mb_strtolower((string)($this->webklexAttributeToString($att->name ?? null) ?? 'attachment')); if(!isset($imap[$name])){$imap[$name]=[];} $imap[$name][]=$att; } foreach($rows as $row){ $key=mb_strtolower((string)($row['original_filename']??'')); $att=$imap[$key][0]??null; if($att===null){continue;} $raw=json_decode((string)($row['raw_payload_json']??''),true); if(!is_array($raw)){$raw=[];} $raw['imap_folder']=$folder; $raw['imap_uid']=$uid; $raw['imap_part_number']=(string)($att->part_number ?? '1'); $raw['imap_attachment_name']=$this->webklexAttributeToString($att->name ?? null) ?? (string)($row['original_filename']??'attachment'); $raw['imap_content_id']=$this->webklexAttributeToString($att->id ?? null) ?? null; $raw['imap_mime_type']=$this->webklexAttributeToString($att->content_type ?? null) ?? 'application/octet-stream'; $u=$this->pdo->prepare('UPDATE mail_external_attachments SET raw_payload_json=:raw_payload_json, import_status=CASE WHEN import_status="imported" THEN import_status ELSE "pending" END, error_message=CASE WHEN error_message IN ("connection failed","missing_imap_attachment_metadata","missing_imap_attachment_metadata: imap_folder/imap_uid/imap_part_number not available") THEN NULL ELSE error_message END WHERE id=:id'); $u->execute([':raw_payload_json'=>json_encode($raw, JSON_UNESCAPED_UNICODE),':id'=>(int)$row['id']]); } }
    private function existsMessage(int $tenantId,int $mailboxId,string $externalMessageId): bool { $s=$this->pdo->prepare('SELECT id FROM mail_messages WHERE tenant_id=:tenant_id AND mailbox_id=:mailbox_id AND external_provider=:provider AND external_message_id=:external_message_id LIMIT 1'); $s->execute([':tenant_id'=>$tenantId,':mailbox_id'=>$mailboxId,':provider'=>'imap',':external_message_id'=>$externalMessageId]); return $s->fetchColumn()!==false; }
    private function insertMessage(array $data): int { $sql='INSERT INTO mail_messages (tenant_id,mailbox_id,folder_id,user_id,message_uuid,thread_uuid,external_provider,external_message_id,direction,mail_scope,from_address,to_addresses,cc_addresses,bcc_addresses,subject,body_text,body_html,raw_headers,has_attachments,is_read,read_at,is_starred,is_draft,is_spam,is_deleted,received_at,sent_at,created_at,updated_at) VALUES (:tenant_id,:mailbox_id,:folder_id,:user_id,:message_uuid,:thread_uuid,:external_provider,:external_message_id,:direction,:mail_scope,:from_address,:to_addresses,:cc_addresses,:bcc_addresses,:subject,:body_text,:body_html,:raw_headers,:has_attachments,:is_read,:read_at,:is_starred,:is_draft,:is_spam,:is_deleted,:received_at,:sent_at,NOW(),NOW())'; $s=$this->pdo->prepare($sql); return $this->executeWithDbContext($s,$data,'insert','mail_messages',['tenant_id','mailbox_id','folder_id','user_id','message_uuid','thread_uuid','external_provider','external_message_id','direction','mail_scope','from_address','to_addresses','cc_addresses','bcc_addresses','subject','body_text','body_html','raw_headers','has_attachments','is_read','read_at','is_starred','is_draft','is_spam','is_deleted','received_at','sent_at','created_at','updated_at'])?(int)$this->pdo->lastInsertId():0; }
    private function insertRecipients(int $tenantId, int $messageId, array $emails, string $type): void { if($emails===[]){return;} $stmt=$this->pdo->prepare('INSERT INTO mail_message_recipients (tenant_id,message_id,recipient_type,email_address,user_id,mailbox_id,delivery_status,created_at) VALUES (:tenant_id,:message_id,:recipient_type,:email_address,NULL,NULL,:delivery_status,NOW())'); foreach($emails as $email){ if(trim((string)$email)===''){continue;} $this->executeWithDbContext($stmt,[':tenant_id'=>$tenantId,':message_id'=>$messageId,':recipient_type'=>$type,':email_address'=>strtolower(trim((string)$email)),':delivery_status'=>'delivered'],'insert','mail_message_recipients',['tenant_id','message_id','recipient_type','email_address','user_id','mailbox_id','delivery_status','created_at']); }}
    private function insertEvent(int $tenantId,int $messageId,int $userId,int $uid,string $mailbox): void { $stmt=$this->pdo->prepare('INSERT INTO mail_message_events (tenant_id,message_id,user_id,event_type,metadata_json,created_at) VALUES (:tenant_id,:message_id,:user_id,:event_type,:metadata_json,NOW())'); $this->executeWithDbContext($stmt,[':tenant_id'=>$tenantId,':message_id'=>$messageId,':user_id'=>$userId,':event_type'=>'received',':metadata_json'=>json_encode(['provider'=>'imap','uid'=>$uid,'mailbox'=>$mailbox,'folder'=>'INBOX'], JSON_UNESCAPED_UNICODE)],'insert','mail_message_events',['tenant_id','message_id','user_id','event_type','metadata_json','created_at']); }
    private function insertExternalAttachment(int $tenantId,int $messageId,array $attachment): void { $stmt=$this->pdo->prepare('INSERT INTO mail_external_attachments (tenant_id,message_id,cloud_file_id,legacy_source,legacy_table,legacy_attachment_id,original_filename,mime_type,size_bytes,import_status,raw_payload_json,created_at) VALUES (:tenant_id,:message_id,NULL,:legacy_source,:legacy_table,:legacy_attachment_id,:original_filename,:mime_type,:size_bytes,:import_status,:raw_payload_json,NOW())'); $this->executeWithDbContext($stmt,[':tenant_id'=>$tenantId,':message_id'=>$messageId,':legacy_source'=>'imap',':legacy_table'=>'imap',':legacy_attachment_id'=>(string)$attachment['legacy_attachment_id'],':original_filename'=>$attachment['original_filename'],':mime_type'=>$attachment['mime_type'],':size_bytes'=>$attachment['size_bytes'],':import_status'=>'pending',':raw_payload_json'=>json_encode($attachment, JSON_UNESCAPED_UNICODE)],'insert','mail_external_attachments',['tenant_id','message_id','cloud_file_id','legacy_source','legacy_table','legacy_attachment_id','original_filename','mime_type','size_bytes','import_status','raw_payload_json','created_at']); }
    private function updateInboundSyncStatus(array $account, bool $ok, ?string $error): void { if (($account['source_type'] ?? '') !== 'inbound') { return; } $stmt = $this->pdo->prepare('UPDATE mail_inbound_accounts SET last_sync_at = NOW(), last_error = :last_error, status = :status, updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id'); $stmt->execute([':last_error'=>$ok?null:$error, ':status'=>$ok?'active':'error', ':id'=>(int)$account['source_id'], ':tenant_id'=>(int)$account['tenant_id']]); }
    private function updateSmtpSyncStatus(array $account, ?array $safeContext = null): void { if (($account['source_type'] ?? '') !== 'smtp') { return; } $stmt = $this->pdo->prepare('UPDATE mail_smtp_accounts SET last_error = :last_error, updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id'); $payload = null; if ($safeContext !== null) { $payload = (string) json_encode($safeContext, JSON_UNESCAPED_UNICODE); } $stmt->execute([':last_error'=>$payload, ':id'=>(int)$account['source_id'], ':tenant_id'=>(int)$account['tenant_id']]); }
    private function updateSyncStatus(array $account, bool $ok, ?string $error, ?array $safeContext = null): void { $this->updateInboundSyncStatus($account,$ok,$error); $this->updateSmtpSyncStatus($account,$ok ? null : $safeContext); }
    private function classifyImapError(Throwable $e): string { if ($e instanceof PDOException || $e->getPrevious() instanceof PDOException) { return 'db_query_error'; } $raw = strtolower(trim($e->getMessage().' '.($e->getPrevious()?->getMessage() ?? ''))); $map=['imap_parse_error'=>['attribute::format','message_parse','parse'],'auth_failed'=>['authentication failed','login failed','invalid credentials','invalid password','failed to authenticate'],'decrypt_failed'=>['decrypt','decryption','secretbox','sodium','empty password'],'ssl_tls_failed'=>['ssl','tls','certificate','crypto','peer certificate'],'timeout'=>['timeout','timed out'],'connection_failed'=>['connection refused','network unreachable','could not connect','failed to connect'],'mailbox_not_found'=>['mailbox','folder','inbox not found'],'config_error'=>['invalid configuration','invalid option','unsupported encryption']]; foreach($map as $type=>$keywords){ foreach($keywords as $k){ if(str_contains($raw,$k)){ return $type; } } } return 'unknown_imap_error'; }
    private function resolveImapPassword(array $account): array { $username=trim((string)($account['imap_username']??'')); $host=trim((string)($account['imap_host']??'')); $port=(int)($account['imap_port']??0); $encryption=strtolower(trim((string)($account['imap_encryption']??''))); $encrypted=trim((string)($account['password_encrypted']??'')); if($username===''||$host===''||$port<=0||!in_array($encryption,['ssl','tls','none',''],true)){return [null,'config_error'];} if($encrypted===''){return [null,'decrypt_failed'];} try { $password=(string)$this->secretBox->decrypt($encrypted);} catch (Throwable $e){ return [null,$this->classifyImapError($e) === 'unknown_imap_error' ? 'decrypt_failed' : $this->classifyImapError($e)]; } if(trim($password)===''){return [null,'decrypt_failed'];} return [$password,null]; }
    private function logSafeSyncError(array $ctx): void { error_log('[mail.imap.sync] '.json_encode($ctx, JSON_UNESCAPED_UNICODE)); }
    private function buildSafeExceptionContext(array $account, string $errorType, ?Throwable $e, int $tenantId, int $userId, int $accountId): array { $previous = $e?->getPrevious(); $message = $e?->getMessage() ?? $errorType; preg_match('/uid=(\d+)/i', $message, $uid); preg_match('/folder=([^\s]+)/i', $message, $folder); return ['error_type'=>$errorType,'exception_class'=>$e ? get_class($e) : 'n/a','exception_message_sanitized'=>$this->shortText($this->sanitizeSensitiveText($message, $account),220),'previous_exception_class'=>$previous ? get_class($previous) : null,'previous_exception_message_sanitized'=>$previous ? $this->shortText($this->sanitizeSensitiveText($previous->getMessage(), $account),220) : null,'host_in'=>trim((string)($account['imap_host'] ?? '')),'port_in'=>(int)($account['imap_port'] ?? 0),'ssl_in'=>strtolower(trim((string)($account['imap_encryption'] ?? 'none'))) ?: 'none','account_id'=>$accountId,'tenant_id'=>$tenantId,'user_id'=>$userId,'stage'=>str_contains(strtolower($message), 'message_parse') ? 'message_parse' : 'sync','message_uid'=>isset($uid[1]) ? (int)$uid[1] : null,'folder'=>$folder[1] ?? null] + $this->extractDbContext($e); }
    private function webklexAttributeToDateTime(mixed $value): ?DateTimeImmutable { if ($value === null) { return null; } if ($value instanceof DateTimeInterface) { return DateTimeImmutable::createFromInterface($value); } if ($value instanceof Attribute) { $candidate = $this->webklexAttributeToDateTime($value->first()); if ($candidate instanceof DateTimeImmutable) { return $candidate; } $value = (string) $value; } if (is_array($value)) { foreach ($value as $item) { $parsed = $this->webklexAttributeToDateTime($item); if ($parsed instanceof DateTimeImmutable) { return $parsed; } } return null; } if (is_object($value) && method_exists($value, 'toDate')) { return $this->webklexAttributeToDateTime($value->toDate()); } if (is_object($value) && method_exists($value, 'first')) { return $this->webklexAttributeToDateTime($value->first()); } if (is_object($value) && method_exists($value, 'toString')) { return $this->webklexAttributeToDateTime($value->toString()); } if (is_string($value)) { $trimmed = trim($value); if ($trimmed === '') { return null; } try { return new DateTimeImmutable($trimmed); } catch (Throwable) { return null; } } return null; }
    private function webklexAttributeToString(mixed $value, int $maxLen = 1000): ?string { if ($value === null) { return null; } if (is_string($value)) { $v = trim($value); return $v === '' ? null : mb_substr($v, 0, $maxLen); } if (is_scalar($value)) { return mb_substr(trim((string)$value), 0, $maxLen); } if ($value instanceof Attribute) { $first = $this->webklexAttributeToString($value->first(), $maxLen); if ($first !== null && $first !== '') { return $first; } return $this->webklexAttributeToString((string)$value, $maxLen); } if (is_array($value) || $value instanceof \Traversable) { $parts = []; foreach ($value as $item) { $str = $this->webklexAttributeToString($item, $maxLen); if ($str !== null && $str !== '') { $parts[] = $str; } } if ($parts === []) { return null; } return mb_substr(implode(', ', $parts), 0, $maxLen); } if (is_object($value) && method_exists($value, 'toString')) { return $this->webklexAttributeToString($value->toString(), $maxLen); } if (is_object($value) && method_exists($value, '__toString')) { return $this->webklexAttributeToString((string)$value, $maxLen); } return null; }

    private function executeWithDbContext(\PDOStatement $statement, array $params, string $operation, string $table, array $columns): bool {
        $this->lastDbContext = ['db_operation'=>$operation,'db_table'=>$table,'db_context'=>['columns'=>$columns]];
        return $statement->execute($params);
    }
    private function extractDbContext(?Throwable $e): array {
        if ($this->lastDbContext === null) { return []; }
        return $this->lastDbContext;
    }

    private function sanitizeSensitiveText(string $text, array $account): string { $sanitized = $text; $username = trim((string)($account['imap_username'] ?? '')); if ($username !== '') { $sanitized = str_ireplace($username, '[redacted_user]', $sanitized); } foreach ([trim((string)($account['password_encrypted'] ?? '')), '.env'] as $token) { if ($token !== '') { $sanitized = str_ireplace($token, '[redacted]', $sanitized); } } $patterns=['/(password_encrypted\s*[:=]\s*)([^,\s]+)/i','/(password\s*[:=]\s*)([^,\s]+)/i','/(token\s*[:=]\s*)([^,\s]+)/i','/(secret\s*[:=]\s*)([^,\s]+)/i','/(key\s*[:=]\s*)([^,\s]+)/i','/(aws[_-]?secret[_-]?access[_-]?key\s*[:=]\s*)([^,\s]+)/i','/(aws[_-]?access[_-]?key[_-]?id\s*[:=]\s*)([^,\s]+)/i']; foreach($patterns as $pattern){ $sanitized=(string)preg_replace($pattern,'$1[redacted]',$sanitized);} $sanitized=(string)preg_replace_callback('/([a-z0-9._%+\-]+)@([a-z0-9.\-]+\.[a-z]{2,})/i', static function(array $m): string { $local=$m[1]; $domain=$m[2]; $localMasked = strlen($local) <= 2 ? substr($local,0,1).'*' : substr($local,0,1).'***'.substr($local,-1); $domainParts = explode('.', $domain); $root = $domainParts[0] ?? ''; $rootMasked = strlen($root) <= 2 ? substr($root,0,1).'*' : substr($root,0,1).'***'.substr($root,-1); return $localMasked.'@'.$rootMasked.'.'.implode('.', array_slice($domainParts,1)); }, $sanitized); $sanitized=(string)preg_replace('/[A-Za-z0-9+\/=]{32,}/', '[redacted_blob]', $sanitized); return trim($sanitized); }
    private function shortText(string $text, int $max): string { $clean = trim(preg_replace('/\s+/', ' ', $text) ?? ''); return mb_strlen($clean) <= $max ? $clean : mb_substr($clean, 0, $max - 3).'...'; }
    private function extractMessageId(string $messageId, string $headers): ?string { $trim=trim($messageId); if($trim!==''){return $trim;} if(preg_match('/^Message-ID:\s*(.+)$/mi',$headers,$m)===1){ return trim((string)$m[1]); } return null; }
    private function safeFileName(string $name): string { $base = basename(str_replace('\\','/',$name)); $clean = preg_replace('/[^a-zA-Z0-9._-]/', '_', $base); return $clean !== '' ? $clean : 'attachment'; }
    private function toMysqlDateTime(string $value): ?string { $ts = strtotime($value); return $ts === false ? null : date('Y-m-d H:i:s', $ts); }
    private function uuidV4(): string { $data=random_bytes(16); $data[6]=chr((ord($data[6])&0x0f)|0x40); $data[8]=chr((ord($data[8])&0x3f)|0x80); return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)); }
}
