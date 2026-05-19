<?php

declare(strict_types=1);

namespace App\Core\Mail;

use App\Support\SecretBox;
use PDO;
use Throwable;

final readonly class MailImapSyncService
{
    public function __construct(private PDO $pdo, private SecretBox $secretBox)
    {
    }

    public function syncForUser(int $tenantId, int $userId, int $accountId, int $limit = 25): array
    {
        if (!extension_loaded('imap')) {
            return ['ok' => false, 'imported' => 0, 'skipped' => 0, 'attachments_pending' => 0, 'errors' => ['PHP IMAP extension not loaded.']];
        }

        $limit = max(1, min(250, $limit));
        $account = $this->findAuthorizedAccount($tenantId, $userId, $accountId);
        if ($account === null) {
            return ['ok' => false, 'imported' => 0, 'skipped' => 0, 'attachments_pending' => 0, 'errors' => ['Cuenta IMAP no autorizada o inactiva.']];
        }

        $mailboxId = (int) $account['mailbox_id'];
        $ownerUserId = (int) ($account['mailbox_user_id'] ?? 0) > 0 ? (int) $account['mailbox_user_id'] : $userId;
        $folderId = $this->ensureInboxFolder($tenantId, $mailboxId);
        if ($folderId <= 0) {
            return ['ok' => false, 'imported' => 0, 'skipped' => 0, 'attachments_pending' => 0, 'errors' => ['No se pudo preparar la carpeta INBOX.']];
        }

        $password = $this->secretBox->decrypt((string) ($account['password_encrypted'] ?? ''));
        if (trim($password) === '') {
            return ['ok' => false, 'imported' => 0, 'skipped' => 0, 'attachments_pending' => 0, 'errors' => ['No se pudo resolver la contraseña IMAP.']];
        }

        $result = ['ok' => true, 'imported' => 0, 'skipped' => 0, 'attachments_pending' => 0, 'errors' => []];
        $imap = null;

        try {
            $imap = @imap_open($this->imapMailboxPath((string) $account['imap_host'], (int) $account['imap_port'], (string) $account['imap_encryption']), (string) $account['imap_username'], $password, 0, 1);
            if ($imap === false) {
                $result['ok'] = false;
                $result['errors'][] = 'No se pudo conectar al servidor IMAP.';
                $this->updateInboundSyncStatus($account, false, $result['errors'][0]);
                return $result;
            }

            $uids = imap_search($imap, 'ALL', SE_UID);
            if (!is_array($uids) || $uids === []) {
                $this->updateInboundSyncStatus($account, true, null);
                return $result;
            }

            rsort($uids, SORT_NUMERIC);
            $uids = array_slice($uids, 0, $limit);

            foreach ($uids as $uid) {
                $uid = (int) $uid;
                $overview = imap_fetch_overview($imap, (string) $uid, FT_UID);
                if (!is_array($overview) || !isset($overview[0])) { $result['skipped']++; continue; }
                $ov = $overview[0];
                $headers = (string) (imap_fetchheader($imap, (string) $uid, FT_UID) ?: '');
                $externalMessageId = $this->extractMessageId((string) ($ov->message_id ?? ''), $headers) ?? ('imap-uid:' . $uid);
                if ($this->existsMessage($tenantId, $mailboxId, $externalMessageId)) { $result['skipped']++; continue; }

                $structure = imap_fetchstructure($imap, (string) $uid, FT_UID);
                $attachmentParts = $this->collectAttachmentMetadata($structure, (string) $uid);
                $plainBody = $this->fetchBodyBySubtype($imap, $uid, 'PLAIN');
                $htmlBody = $this->fetchBodyBySubtype($imap, $uid, 'HTML');
                if ($plainBody === null) { $plainBody = trim(strip_tags((string) $htmlBody)); }

                $to = $this->extractAddressList((string) ($ov->to ?? ''));
                $cc = $this->extractAddressList((string) ($ov->cc ?? ''));
                $bcc = $this->extractAddressList((string) ($ov->bcc ?? ''));
                $from = $this->extractAddressList((string) ($ov->from ?? ''));
                $isRead = isset($ov->seen) && ((string) $ov->seen === '1' || (string) $ov->seen === 'S');
                $isStarred = isset($ov->flagged) && ((string) $ov->flagged === '1' || (string) $ov->flagged === 'F');
                $receivedAt = $this->toMysqlDateTime((string) ($ov->date ?? '')) ?? date('Y-m-d H:i:s');

                $messageId = $this->insertMessage([
                    ':tenant_id' => $tenantId, ':mailbox_id' => $mailboxId, ':folder_id' => $folderId, ':user_id' => $ownerUserId,
                    ':message_uuid' => $this->uuidV4(), ':thread_uuid' => $this->uuidV4(), ':external_provider' => 'imap', ':external_message_id' => $externalMessageId,
                    ':direction' => 'inbound', ':mail_scope' => 'normal', ':from_address' => $from[0] ?? null,
                    ':to_addresses' => json_encode($to, JSON_UNESCAPED_UNICODE), ':cc_addresses' => $cc === [] ? null : json_encode($cc, JSON_UNESCAPED_UNICODE), ':bcc_addresses' => $bcc === [] ? null : json_encode($bcc, JSON_UNESCAPED_UNICODE),
                    ':subject' => isset($ov->subject) ? imap_utf8((string) $ov->subject) : null,
                    ':body_text' => $plainBody, ':body_html' => $htmlBody, ':raw_headers' => $headers,
                    ':has_attachments' => $attachmentParts === [] ? 0 : 1, ':is_read' => $isRead ? 1 : 0, ':read_at' => $isRead ? date('Y-m-d H:i:s') : null,
                    ':is_starred' => $isStarred ? 1 : 0, ':is_draft' => 0, ':is_spam' => 0, ':is_deleted' => 0,
                    ':received_at' => $receivedAt, ':sent_at' => $receivedAt,
                ]);
                if ($messageId <= 0) { $result['skipped']++; continue; }

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

            $this->updateInboundSyncStatus($account, $result['errors'] === [], $result['errors'] === [] ? null : 'Error parcial de sincronización IMAP');
        } catch (Throwable) {
            $result['ok'] = false;
            $result['errors'][] = 'Falló la sincronización IMAP.';
            $this->updateInboundSyncStatus($account, false, 'Falló la sincronización IMAP.');
        } finally {
            if (is_resource($imap) || $imap instanceof \IMAP\Connection) { @imap_close($imap); }
        }

        return $result;
    }

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
    private function imapMailboxPath(string $host, int $port, string $encryption): string { $enc = strtolower(trim($encryption)); $flags='/imap'; if($enc==='ssl'||$enc==='tls'){ $flags.='/' . $enc . '/novalidate-cert'; } elseif($enc==='none'||$enc===''){ $flags.='/notls'; } return '{'.$host.':'.$port.$flags.'}INBOX'; }
    private function extractMessageId(string $messageId, string $headers): ?string { $trim=trim($messageId); if($trim!==''){return $trim;} if(preg_match('/^Message-ID:\s*(.+)$/mi',$headers,$m)===1){ return trim((string)$m[1]); } return null; }
    private function extractAddressList(string $raw): array { preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i',$raw,$m); return array_values(array_unique(array_map('strtolower',$m[0]??[]))); }
    private function collectAttachmentMetadata(mixed $structure, string $uid, string $prefix = ''): array { if(!is_object($structure)){return [];} $attachments=[]; $parts=is_array($structure->parts??null)?$structure->parts:[]; foreach($parts as $index=>$part){ $partNo = $prefix === '' ? (string) ($index + 1) : $prefix . '.' . ($index + 1); $filename = $this->extractPartFilename($part); $isAttachment = $filename !== null || ((int)($part->ifdisposition ?? 0) === 1 && in_array(strtolower((string)($part->disposition ?? '')), ['attachment','inline'], true)); if($isAttachment){ $attachments[]=['legacy_attachment_id'=>$uid . ':' . $partNo,'original_filename'=>$filename ?? ('attachment-' . $partNo),'mime_type'=>$this->resolveMimeType($part),'size_bytes'=>isset($part->bytes)?(int)$part->bytes:null,'part_number'=>$partNo,'uid'=>$uid]; } if(isset($part->parts) && is_array($part->parts)){ $attachments=array_merge($attachments,$this->collectAttachmentMetadata($part,$uid,$partNo)); }} return $attachments; }
    private function extractPartFilename(object $part): ?string { foreach (['dparameters','parameters'] as $prop){ if(!isset($part->{$prop}) || !is_array($part->{$prop})){continue;} foreach($part->{$prop} as $entry){ $attribute=strtolower((string)($entry->attribute ?? '')); if($attribute==='filename' || $attribute==='name'){ return (string) imap_utf8((string)($entry->value ?? '')); } } } return null; }
    private function resolveMimeType(object $part): string { $primary=['text','multipart','message','application','audio','image','video','other']; $type=(int)($part->type ?? 0); $main=$primary[$type] ?? 'application'; $sub=strtolower((string)($part->subtype ?? 'octet-stream')); return $main . '/' . $sub; }
    private function fetchBodyBySubtype($imap, int $uid, string $subtype): ?string { $structure=imap_fetchstructure($imap,(string)$uid,FT_UID); if(!is_object($structure) || !isset($structure->parts) || !is_array($structure->parts)){ return null;} foreach($structure->parts as $index=>$part){ if(strtoupper((string)($part->subtype ?? ''))!==$subtype){continue;} $body=imap_fetchbody($imap,(string)$uid,(string)($index+1),FT_UID); if(!is_string($body)){continue;} $enc=(int)($part->encoding ?? 0); if($enc===3){$body=base64_decode($body,true)?:'';} if($enc===4){$body=quoted_printable_decode($body);} return trim($body);} return null; }
    private function toMysqlDateTime(string $value): ?string { $ts = strtotime($value); return $ts === false ? null : date('Y-m-d H:i:s', $ts); }
    private function uuidV4(): string { $data=random_bytes(16); $data[6]=chr((ord($data[6])&0x0f)|0x40); $data[8]=chr((ord($data[8])&0x3f)|0x80); return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4)); }
}
