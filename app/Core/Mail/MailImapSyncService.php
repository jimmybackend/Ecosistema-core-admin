<?php

declare(strict_types=1);

namespace App\Core\Mail;

use App\Support\SecretBox;
use PDO;
use Throwable;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Message;

final readonly class MailImapSyncService
{
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
            $safeContext = $this->buildSafeExceptionContext($account, $validationError, null);
            $safeError = $this->buildSafeImapErrorMessage($safeContext);
            $this->updateSyncStatus($account, false, $safeError, $safeContext);
            return ['ok' => false, 'imported' => 0, 'skipped' => 0, 'attachments_pending' => 0, 'errors' => [$safeError]];
        }

        $result = ['ok' => true, 'imported' => 0, 'skipped' => 0, 'attachments_pending' => 0, 'errors' => []];

        try {
            $clientManager = new ClientManager(['options' => ['fetch' => 2]]);
            $client = $clientManager->make($this->buildClientConfig($account, $password));

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
                $headers = (string) ($message->getHeader()->raw ?? '');
                $externalMessageId = $this->extractMessageId((string) ($message->getMessageId() ?? ''), $headers) ?? ('imap-uid:' . $uid);
                if ($this->existsMessage($tenantId, $mailboxId, $externalMessageId)) {
                    $result['skipped']++;
                    continue;
                }

                $plainBody = trim((string) $message->getTextBody());
                $htmlBody = trim((string) $message->getHTMLBody());
                if ($plainBody === '') {
                    $plainBody = trim(strip_tags($htmlBody));
                }

                $to = $this->normalizeAddressList($message->getTo());
                $cc = $this->normalizeAddressList($message->getCc());
                $bcc = $this->normalizeAddressList($message->getBcc());
                $from = $this->normalizeAddressList($message->getFrom());
                $isRead = (bool) $message->getFlag('seen');
                $isStarred = (bool) $message->getFlag('flagged');
                $receivedAt = $this->toMysqlDateTime((string) ($message->getDate()?->format(DATE_RFC2822) ?? '')) ?? date('Y-m-d H:i:s');

                $attachmentParts = [];
                foreach ($message->getAttachments() as $attachment) {
                    $filename = (string) ($attachment->name ?? 'attachment');
                    $attachmentParts[] = [
                        'legacy_attachment_id' => $uid . ':' . ((string) ($attachment->part_number ?? count($attachmentParts) + 1)),
                        'original_filename' => $filename,
                        'safe_filename' => $this->safeFileName($filename),
                        'mime_type' => (string) ($attachment->content_type ?? 'application/octet-stream'),
                        'size_bytes' => isset($attachment->size) ? (int) $attachment->size : null,
                    ];
                }

                $messageId = $this->insertMessage([
                    ':tenant_id' => $tenantId, ':mailbox_id' => $mailboxId, ':folder_id' => $folderId, ':user_id' => $ownerUserId,
                    ':message_uuid' => $this->uuidV4(), ':thread_uuid' => $this->uuidV4(), ':external_provider' => 'imap', ':external_message_id' => $externalMessageId,
                    ':direction' => 'inbound', ':mail_scope' => 'normal', ':from_address' => $from[0] ?? null,
                    ':to_addresses' => json_encode($to, JSON_UNESCAPED_UNICODE), ':cc_addresses' => $cc === [] ? null : json_encode($cc, JSON_UNESCAPED_UNICODE), ':bcc_addresses' => $bcc === [] ? null : json_encode($bcc, JSON_UNESCAPED_UNICODE),
                    ':subject' => (string) ($message->getSubject() ?? ''),
                    ':body_text' => $plainBody, ':body_html' => $htmlBody, ':raw_headers' => $headers,
                    ':has_attachments' => $attachmentParts === [] ? 0 : 1, ':is_read' => $isRead ? 1 : 0, ':read_at' => $isRead ? date('Y-m-d H:i:s') : null,
                    ':is_starred' => $isStarred ? 1 : 0, ':is_draft' => 0, ':is_spam' => 0, ':is_deleted' => 0,
                    ':received_at' => $receivedAt, ':sent_at' => $receivedAt,
                ]);
                if ($messageId <= 0) {
                    $result['skipped']++;
                    continue;
                }

                $this->insertRecipients($tenantId, $messageId, $to, 'to');
                $this->insertRecipients($tenantId, $messageId, $cc, 'cc');
                $this->insertRecipients($tenantId, $messageId, $bcc, 'bcc');
                $this->insertEvent($tenantId, $messageId, $ownerUserId, $uid, (string) $account['mailbox_full_address']);
                foreach ($attachmentParts as $attachment) {
                    $this->insertExternalAttachment($tenantId, $messageId, $attachment);
                    $result['attachments_pending']++;
                }

                $result['imported']++;
            }

            $client->disconnect();
            $this->updateSyncStatus($account, $result['errors'] === [], $result['errors'] === [] ? null : 'Error parcial de sincronización IMAP');
        } catch (Throwable $e) {
            $result['ok'] = false;
            $errorType = $this->classifyImapError($e, $password);
            $safeContext = $this->buildSafeExceptionContext($account, $errorType, $e);
            $safeError = $this->buildSafeImapErrorMessage($safeContext);
            $result['errors'][] = $safeError;
            $this->logSafeSyncError($account, $userId, $safeContext);
            $this->updateSyncStatus($account, false, $safeError, $safeContext);
        }

        return $result;
    }


    private function buildSafeImapErrorMessage(array $safeContext): string
    {
        $host = (string) ($safeContext['host'] ?? '');
        $port = (int) ($safeContext['port'] ?? 0);
        $sslIn = (string) ($safeContext['encryption'] ?? 'none');
        $errorType = (string) ($safeContext['type'] ?? 'unknown_imap_error');
        $exceptionClass = (string) ($safeContext['exception_class'] ?? 'n/a');
        $message = (string) ($safeContext['message'] ?? '');
        $suggestion = match ($errorType) {
            'auth_failed' => 'Verifica usuario y contraseña del buzón.',
            'connection_failed' => 'Verifica host, puerto y conectividad de red.',
            'ssl_tls_failed' => 'Verifica SSL/TLS y certificado del servidor.',
            'decrypt_failed' => 'Reingresa la contraseña del buzón y vuelve a guardar.',
            'mailbox_not_found' => 'Verifica que la carpeta INBOX exista en el servidor.',
            default => 'Reintenta la sincronización y revisa la configuración IMAP.',
        };

        return "No se pudo sincronizar IMAP. host_in={$host} port_in={$port} ssl_in={$sslIn}. Tipo: {$errorType}. exception_class={$exceptionClass}. detalle={$message}. {$suggestion}";
    }

    private function buildClientConfig(array $account, string $password): array {
        $enc = strtolower(trim((string) ($account['imap_encryption'] ?? '')));
        $encryption = match ($enc) {
            'ssl' => 'ssl',
            'tls' => 'tls',
            default => false,
        };

        return [
            'host' => (string) $account['imap_host'],
            'port' => (int) $account['imap_port'],
            'encryption' => $encryption,
            'validate_cert' => true,
            'username' => (string) $account['imap_username'],
            'password' => $password,
            'protocol' => 'imap',
            'authentication' => null,
            'timeout' => 30,
        ];
    }

    private function normalizeAddressList(mixed $addresses): array { $emails=[]; foreach ((array)$addresses as $address){ $email=(string)($address->mail ?? $address->address ?? ''); if($email!==''){$emails[] = strtolower(trim($email));}} return array_values(array_unique(array_filter($emails))); }

    private function findAuthorizedAccount(int $tenantId, int $userId, int $accountId): ?array
    {
        $sql = "SELECT 'inbound' AS source_type, ia.id AS source_id, ia.tenant_id, ia.mailbox_id, ia.username AS imap_username, ia.password_encrypted, ia.host AS imap_host, ia.port AS imap_port, ia.encryption AS imap_encryption, ia.status, ia.created_by_user_id, ia.available_to_everyone, m.user_id AS mailbox_user_id, m.full_address AS mailbox_full_address FROM mail_inbound_accounts ia INNER JOIN mail_mailboxes m ON m.id = ia.mailbox_id AND m.tenant_id = ia.tenant_id WHERE ia.id = :account_id AND ia.tenant_id = :tenant_id AND ia.status = 'active' AND m.status = 'active' AND TRIM(COALESCE(ia.host, '')) <> '' AND ia.port IS NOT NULL AND TRIM(COALESCE(ia.username, '')) <> '' AND TRIM(COALESCE(ia.password_encrypted, '')) <> '' AND (ia.created_by_user_id = :created_by_user_id OR m.user_id = :mailbox_user_id OR ia.available_to_everyone = 1 OR m.available_to_everyone = 1) LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':account_id' => $accountId, ':tenant_id' => $tenantId, ':created_by_user_id' => $userId, ':mailbox_user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (is_array($row)) {
            return $row;
        }

        $sql = "SELECT 'smtp' AS source_type, s.id AS source_id, s.tenant_id, s.mailbox_id, s.username AS imap_username, s.password_encrypted, s.host_in AS imap_host, s.port_in AS imap_port, s.ssl_in AS imap_encryption, s.status, s.created_by_user_id, s.available_to_everyone, m.user_id AS mailbox_user_id, m.full_address AS mailbox_full_address FROM mail_smtp_accounts s INNER JOIN mail_mailboxes m ON m.id = s.mailbox_id AND m.tenant_id = s.tenant_id WHERE s.id = :account_id AND s.tenant_id = :tenant_id AND s.status = 'active' AND m.status = 'active' AND TRIM(COALESCE(s.host_in, '')) <> '' AND s.port_in IS NOT NULL AND TRIM(COALESCE(s.username, '')) <> '' AND TRIM(COALESCE(s.password_encrypted, '')) <> '' AND (s.created_by_user_id = :created_by_user_id OR m.user_id = :mailbox_user_id OR s.available_to_everyone = 1 OR m.available_to_everyone = 1) LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':account_id' => $accountId, ':tenant_id' => $tenantId, ':created_by_user_id' => $userId, ':mailbox_user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }
    private function ensureInboxFolder(int $tenantId, int $mailboxId): int { $stmt=$this->pdo->prepare('SELECT id FROM mail_folders WHERE tenant_id=:tenant_id AND mailbox_id=:mailbox_id AND system_name=:system_name ORDER BY id ASC LIMIT 1'); $stmt->execute([':tenant_id'=>$tenantId,':mailbox_id'=>$mailboxId,':system_name'=>'inbox']); $id=$stmt->fetchColumn(); if($id!==false){return(int)$id;} $ins=$this->pdo->prepare("INSERT INTO mail_folders (tenant_id, mailbox_id, name, system_name, sort_order, created_at, updated_at) VALUES (:tenant_id,:mailbox_id,'INBOX','inbox',0,NOW(),NOW())"); $ins->execute([':tenant_id'=>$tenantId,':mailbox_id'=>$mailboxId]); return (int)$this->pdo->lastInsertId(); }
    private function existsMessage(int $tenantId,int $mailboxId,string $externalMessageId): bool { $s=$this->pdo->prepare('SELECT id FROM mail_messages WHERE tenant_id=:tenant_id AND mailbox_id=:mailbox_id AND external_provider=:provider AND external_message_id=:external_message_id LIMIT 1'); $s->execute([':tenant_id'=>$tenantId,':mailbox_id'=>$mailboxId,':provider'=>'imap',':external_message_id'=>$externalMessageId]); return $s->fetchColumn()!==false; }
    private function insertMessage(array $data): int { $sql='INSERT INTO mail_messages (tenant_id,mailbox_id,folder_id,user_id,message_uuid,thread_uuid,external_provider,external_message_id,direction,mail_scope,from_address,to_addresses,cc_addresses,bcc_addresses,subject,body_text,body_html,raw_headers,has_attachments,is_read,read_at,is_starred,is_draft,is_spam,is_deleted,received_at,sent_at,created_at,updated_at) VALUES (:tenant_id,:mailbox_id,:folder_id,:user_id,:message_uuid,:thread_uuid,:external_provider,:external_message_id,:direction,:mail_scope,:from_address,:to_addresses,:cc_addresses,:bcc_addresses,:subject,:body_text,:body_html,:raw_headers,:has_attachments,:is_read,:read_at,:is_starred,:is_draft,:is_spam,:is_deleted,:received_at,:sent_at,NOW(),NOW())'; $s=$this->pdo->prepare($sql); return $s->execute($data)?(int)$this->pdo->lastInsertId():0; }
    private function insertRecipients(int $tenantId, int $messageId, array $emails, string $type): void { if($emails===[]){return;} $stmt=$this->pdo->prepare('INSERT INTO mail_message_recipients (tenant_id,message_id,recipient_type,email_address,user_id,mailbox_id,delivery_status,created_at,updated_at) VALUES (:tenant_id,:message_id,:recipient_type,:email_address,NULL,NULL,:delivery_status,NOW(),NOW())'); foreach($emails as $email){ if(trim((string)$email)===''){continue;} $stmt->execute([':tenant_id'=>$tenantId,':message_id'=>$messageId,':recipient_type'=>$type,':email_address'=>strtolower(trim((string)$email)),':delivery_status'=>'delivered']); }}
    private function insertEvent(int $tenantId,int $messageId,int $userId,int $uid,string $mailbox): void { $stmt=$this->pdo->prepare('INSERT INTO mail_message_events (tenant_id,message_id,user_id,event_type,metadata_json,created_at) VALUES (:tenant_id,:message_id,:user_id,:event_type,:metadata_json,NOW())'); $stmt->execute([':tenant_id'=>$tenantId,':message_id'=>$messageId,':user_id'=>$userId,':event_type'=>'received',':metadata_json'=>json_encode(['provider'=>'imap','uid'=>$uid,'mailbox'=>$mailbox,'folder'=>'INBOX'], JSON_UNESCAPED_UNICODE)]); }
    private function insertExternalAttachment(int $tenantId,int $messageId,array $attachment): void { $stmt=$this->pdo->prepare('INSERT INTO mail_external_attachments (tenant_id,message_id,cloud_file_id,legacy_source,legacy_table,legacy_attachment_id,original_filename,mime_type,size_bytes,import_status,raw_payload_json,created_at,updated_at) VALUES (:tenant_id,:message_id,NULL,:legacy_source,:legacy_table,:legacy_attachment_id,:original_filename,:mime_type,:size_bytes,:import_status,:raw_payload_json,NOW(),NOW())'); $stmt->execute([':tenant_id'=>$tenantId,':message_id'=>$messageId,':legacy_source'=>'imap',':legacy_table'=>'imap',':legacy_attachment_id'=>(string)$attachment['legacy_attachment_id'],':original_filename'=>$attachment['original_filename'],':mime_type'=>$attachment['mime_type'],':size_bytes'=>$attachment['size_bytes'],':import_status'=>'pending',':raw_payload_json'=>json_encode($attachment, JSON_UNESCAPED_UNICODE)]); }
    private function updateInboundSyncStatus(array $account, bool $ok, ?string $error): void { if (($account['source_type'] ?? '') !== 'inbound') { return; } $stmt = $this->pdo->prepare('UPDATE mail_inbound_accounts SET last_sync_at = NOW(), last_error = :last_error, status = :status, updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id'); $stmt->execute([':last_error'=>$ok?null:$error, ':status'=>$ok?'active':'error', ':id'=>(int)$account['source_id'], ':tenant_id'=>(int)$account['tenant_id']]); }
    private function updateSmtpSyncStatus(array $account, ?string $error, ?array $safeContext = null): void { if (($account['source_type'] ?? '') !== 'smtp') { return; } $stmt = $this->pdo->prepare('UPDATE mail_smtp_accounts SET last_error = :last_error, updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id'); $payload = $error; if ($safeContext !== null) { $payload = (string) json_encode(['type'=>(string)($safeContext['type'] ?? 'unknown_imap_error'),'exception_class'=>(string)($safeContext['exception_class'] ?? ''),'message'=>(string)($safeContext['message'] ?? ''),'host'=>(string)($safeContext['host'] ?? ''),'port'=>(int)($safeContext['port'] ?? 0),'encryption'=>(string)($safeContext['encryption'] ?? 'none')], JSON_UNESCAPED_UNICODE); } $stmt->execute([':last_error'=>$payload, ':id'=>(int)$account['source_id'], ':tenant_id'=>(int)$account['tenant_id']]); }
    private function updateSyncStatus(array $account, bool $ok, ?string $error, ?array $safeContext = null): void { $this->updateInboundSyncStatus($account,$ok,$error); $this->updateSmtpSyncStatus($account,$ok?null:$error,$ok?null:$safeContext); }
    private function classifyImapError(Throwable $e, ?string $decryptedPassword = null): string { if ($decryptedPassword !== null && trim($decryptedPassword) === '') { return 'decrypt_failed'; } $raw=strtolower(trim($e->getMessage())); if($raw===''){return 'unknown_imap_error';} if(str_contains($raw,'authentication failed')||str_contains($raw,'login failed')||str_contains($raw,'invalid credentials')||str_contains($raw,'failed to authenticate')){return 'auth_failed';} if(str_contains($raw,'certificate')||str_contains($raw,'ssl')||str_contains($raw,'tls')||str_contains($raw,'crypto')){return 'ssl_tls_failed';} if(str_contains($raw,'timeout')||str_contains($raw,'timed out')){return 'timeout';} if(str_contains($raw,'connection refused')||str_contains($raw,'network')||str_contains($raw,'unreachable')){return 'connection_failed';} if(str_contains($raw,'folder')||str_contains($raw,'mailbox')||str_contains($raw,'inbox')){return 'mailbox_not_found';} return 'unknown_imap_error'; }
    private function resolveImapPassword(array $account): array { $encrypted=trim((string)($account['password_encrypted']??'')); $username=trim((string)($account['imap_username']??'')); if($encrypted===''||$username===''){ return [null,'decrypt_failed']; } $password=(string)$this->secretBox->decrypt($encrypted); if(trim($password)===''){ return [null,'decrypt_failed']; } return [$password,null]; }
    private function logSafeSyncError(array $account, int $userId, array $safeContext): void { error_log('[mail.imap.sync] tenant_id='.(int)($account['tenant_id']??0).' user_id='.$userId.' smtp_account_id='.(int)($account['source_type']==='smtp' ? ($account['source_id']??0):0).' host_in='.(string)($safeContext['host']??'').' port_in='.(int)($safeContext['port']??0).' ssl_in='.(string)($safeContext['encryption']??'').' error_type='.(string)($safeContext['type']??'unknown_imap_error').' exception_class='.(string)($safeContext['exception_class']??'n/a').' exception_message='.(string)($safeContext['message']??'').' previous_exception_class='.(string)($safeContext['previous_exception_class']??'').' previous_exception_message='.(string)($safeContext['previous_message']??'')); }
    private function buildSafeExceptionContext(array $account, string $errorType, ?Throwable $e): array { $host = trim((string)($account['imap_host'] ?? '')); $port = (int)($account['imap_port'] ?? 0); $enc = strtolower(trim((string)($account['imap_encryption'] ?? 'none'))) ?: 'none'; $message = $e ? $this->sanitizeSensitiveText($e->getMessage(), $account) : $this->sanitizeSensitiveText($errorType, $account); $previous = $e?->getPrevious(); return ['type'=>$errorType,'exception_class'=>$e ? get_class($e) : 'n/a','message'=>$this->shortText($message, 220),'host'=>$host,'port'=>$port,'encryption'=>$enc,'previous_exception_class'=>$previous ? get_class($previous) : null,'previous_message'=>$previous ? $this->shortText($this->sanitizeSensitiveText($previous->getMessage(), $account), 220) : null]; }
    private function sanitizeSensitiveText(string $text, array $account): string { $sanitized = $text; $tokens = [trim((string)($account['password_encrypted'] ?? '')), trim((string)($account['imap_username'] ?? '')), trim((string)($account['imap_host'] ?? ''))]; foreach ($tokens as $token) { if ($token !== '') { $sanitized = str_ireplace($token, '[redacted]', $sanitized); } } $sanitized = str_ireplace('.env', '[redacted]', $sanitized); $patterns = ['/(password_encrypted\s*[:=]\s*)([^,\s]+)/i', '/(password\s*[:=]\s*)([^,\s]+)/i', '/(token\s*[:=]\s*)([^,\s]+)/i', '/(bearer\s+)([a-z0-9\-\._~\+\/]+=*)/i', '/([a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,})/i']; foreach ($patterns as $pattern) { $sanitized = (string)preg_replace($pattern, '$1[redacted]', $sanitized); } return trim($sanitized); }
    private function shortText(string $text, int $max): string { $clean = trim(preg_replace('/\s+/', ' ', $text) ?? ''); if (mb_strlen($clean) <= $max) { return $clean; } return mb_substr($clean, 0, $max - 3).'...'; }
    private function extractMessageId(string $messageId, string $headers): ?string { $trim=trim($messageId); if($trim!==''){return $trim;} if(preg_match('/^Message-ID:\s*(.+)$/mi',$headers,$m)===1){ return trim((string)$m[1]); } return null; }
    private function safeFileName(string $name): string { $base = basename(str_replace('\\','/',$name)); $clean = preg_replace('/[^a-zA-Z0-9._-]/', '_', $base); return $clean !== '' ? $clean : 'attachment'; }
    private function toMysqlDateTime(string $value): ?string { $ts = strtotime($value); return $ts === false ? null : date('Y-m-d H:i:s', $ts); }
    private function uuidV4(): string { $data=random_bytes(16); $data[6]=chr((ord($data[6])&0x0f)|0x40); $data[8]=chr((ord($data[8])&0x3f)|0x80); return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)); }
}
