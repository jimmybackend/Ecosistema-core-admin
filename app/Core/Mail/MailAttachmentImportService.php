<?php

declare(strict_types=1);

namespace App\Core\Mail;

use App\Core\Cloud\CloudDriveRepository;
use App\Core\Cloud\CloudPath;
use App\Core\Cloud\CloudS3Service;
use App\Support\SecretBox;
use DateTimeImmutable;
use PDO;
use Throwable;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Message;

final class MailAttachmentImportService
{
    public function __construct(private PDO $pdo, private array $config, private SecretBox $secretBox)
    {
    }

    public function importPendingForMessage(int $tenantId, int $userId, int $messageId, int $limit = 5): array
    {
        $limit = max(1, min(25, $limit));
        $cloud = new CloudS3Service($this->config);
        if (!$cloud->isConfigured()) { return ['ok' => false, 'error' => 'Cloud/S3 no configurado.']; }
        $drive = new CloudDriveRepository($this->pdo, $this->config);
        $bucketId = (int) (($drive->findOrCreateDefaultBucket($tenantId)['id'] ?? 0));
        if ($bucketId <= 0) { return ['ok' => false, 'error' => 'No se pudo resolver bucket Cloud.']; }
        $items = $this->loadPending($tenantId, $userId, $messageId, $limit);
        $counts = ['pending' => count($items), 'imported' => 0, 'failed' => 0, 'skipped' => 0, 'errors' => []];
        foreach ($items as $row) {
            $id = (int) $row['id'];
            try {
                $meta = json_decode((string) ($row['raw_payload_json'] ?? ''), true);
                if (!is_array($meta)) { $meta = []; }
                $folder = trim((string) ($meta['imap_folder'] ?? ''));
                $uid = (int) ($meta['imap_uid'] ?? 0);
                $part = trim((string) ($meta['imap_part_number'] ?? ''));
                if ($uid <= 0 || $part === '') {
                    $legacy = (string) ($row['legacy_attachment_id'] ?? '');
                    if (preg_match('/^(\d+)\:(.+)$/', $legacy, $m)) { $uid = (int) $m[1]; $part = trim($m[2]); }
                }
                $missing = [];
                if ($folder === '') { $missing[] = 'imap_folder'; }
                if ($uid <= 0) { $missing[] = 'imap_uid'; }
                if ($part === '') { $missing[] = 'imap_part_number'; }
                if ($missing !== []) {
                    $counts['failed']++;
                    $this->markStatus($id, 'failed', 'missing_imap_attachment_metadata: imap_folder/imap_uid/imap_part_number not available');
                    $counts['errors'][] = ['external_attachment_id'=>$id,'reason'=>'missing_imap_attachment_metadata','missing'=>$missing];
                    continue;
                }

                $binary = $this->fetchAttachmentBinary($tenantId, (int) $row['mailbox_id'], $folder, $uid, $part);
                if ($binary === null) {
                    $counts['failed']++;
                    $this->markStatus($id, 'failed', 'imap_attachment_binary_not_found');
                    $counts['errors'][] = ['external_attachment_id'=>$id,'reason'=>'imap_attachment_binary_not_found'];
                    continue;
                }

                $filename = (string) ($row['original_filename'] ?: ('attachment-' . $id));
                $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename) ?: ('attachment-' . $id);
                $dt = new DateTimeImmutable();
                $key = CloudPath::buildInboundAttachmentKey($userId, $messageId, $safe, $dt);
                $tmp = tempnam(sys_get_temp_dir(), 'mail_att_');
                if (!is_string($tmp)) { throw new \RuntimeException('tmp_unavailable'); }
                file_put_contents($tmp, $binary);
                $mime = (string) ($row['mime_type'] ?? 'application/octet-stream');
                $put = $cloud->putFile($key, $tmp, $mime);
                @unlink($tmp);
                if (($put['ok'] ?? false) !== true) { throw new \RuntimeException((string) ($put['message'] ?? 'cloud_upload_error')); }

                $cloudId = $this->insertCloudFile($tenantId, $userId, $bucketId, $key, $filename, $mime, strlen($binary), $id);
                $this->insertMailAttachment($tenantId, $messageId, $cloudId, $filename, $mime, strlen($binary));
                $this->markImported($id, $cloudId);
                $counts['imported']++;
            } catch (Throwable $e) {
                $counts['failed']++;
                $reason=$this->sanitize($e->getMessage());
                $this->markFailed($id, 'failed', $reason);
                $counts['errors'][] = ['external_attachment_id'=>$id,'reason'=>$reason];
            }
        }
        return ['ok' => true, 'counts' => $counts];
    }

    private function loadPending(int $tenantId, int $userId, int $messageId, int $limit): array {
        $stmt = $this->pdo->prepare('SELECT ea.*, m.mailbox_id FROM mail_external_attachments ea INNER JOIN mail_messages m ON m.id=ea.message_id AND m.tenant_id=ea.tenant_id WHERE ea.tenant_id=:tenant_id AND m.user_id=:user_id AND ea.message_id=:message_id AND ea.cloud_file_id IS NULL AND ea.import_status IN ("pending","failed") ORDER BY ea.id ASC LIMIT :lim');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT); $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT); $stmt->bindValue(':message_id', $messageId, PDO::PARAM_INT); $stmt->bindValue(':lim', $limit, PDO::PARAM_INT); $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    private function fetchAttachmentBinary(int $tenantId, int $mailboxId, string $folder, int $uid, string $part): ?string {
        $acc = $this->pdo->prepare('SELECT host_in, port_in, ssl_in, username, password_encrypted FROM mail_smtp_accounts WHERE tenant_id=:tenant_id AND mailbox_id=:mailbox_id AND status="active" ORDER BY id DESC LIMIT 1');
        $acc->execute([':tenant_id'=>$tenantId, ':mailbox_id'=>$mailboxId]); $account = $acc->fetch(PDO::FETCH_ASSOC);
        if (!is_array($account)) { throw new \RuntimeException('mail_account_config_error'); }
        $host = trim((string) ($account['host_in'] ?? ''));
        $port = (int) ($account['port_in'] ?? 0);
        $encryptionRaw = mb_strtolower(trim((string) ($account['ssl_in'] ?? '')));
        $username = trim((string) ($account['username'] ?? ''));
        $encrypted = trim((string) ($account['password_encrypted'] ?? ''));
        $encryption = in_array($encryptionRaw, ['ssl', 'tls'], true) ? $encryptionRaw : false;
        if ($host === '' || $port <= 0 || $username === '' || $encrypted === '' || !in_array($encryptionRaw, ['ssl', 'tls', 'none', ''], true)) {
            throw new \RuntimeException('mail_account_config_error');
        }
        $pwd = (string) $this->secretBox->decrypt($encrypted);
        if (trim($pwd) === '') {
            throw new \RuntimeException('mail_account_config_error');
        }
        $client = (new ClientManager(['options' => ['fetch' => 2]]))->make(['host'=>$host,'port'=>$port,'encryption'=>$encryption,'validate_cert'=>true,'username'=>$username,'password'=>$pwd,'protocol'=>'imap']);
        $client->connect();
        $imapFolder = $client->getFolder($folder);
        if ($imapFolder === null) { $client->disconnect(); return null; }
        $message = $imapFolder->query()->getMessageByUid($uid);
        if (!$message instanceof Message) { $client->disconnect(); return null; }
        foreach ($message->getAttachments() as $attachment) {
            $pn = (string) ($attachment->part_number ?? '');
            if ($pn !== $part) { continue; }
            $content = (string) ($attachment->content ?? '');
            $client->disconnect();
            return $content !== '' ? $content : null;
        }
        $client->disconnect();
        return null;
    }
    private function insertCloudFile(int $tenantId,int $userId,int $bucketId,string $key,string $name,string $mime,int $size,int $externalAttachmentId): int {
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $stmt = $this->pdo->prepare('INSERT INTO cloud_files (tenant_id,user_id,bucket_id,original_name,stored_name,s3_key,mime_type,extension,size_bytes,status,access_type,encrypted,found_in_s3,origin_module,origin_table,origin_id,uploaded_by_user_id,uploaded_at,updated_at) VALUES (:tenant_id,:user_id,:bucket_id,:original_name,:stored_name,:s3_key,:mime_type,:extension,:size_bytes,"active","private",1,1,"mail","mail_external_attachments",:origin_id,:uploaded_by_user_id,NOW(),NOW())');
        $stmt->execute([':tenant_id'=>$tenantId,':user_id'=>$userId,':bucket_id'=>$bucketId,':original_name'=>$name,':stored_name'=>basename($key),':s3_key'=>$key,':mime_type'=>$mime,':extension'=>$ext !== '' ? mb_strtolower($ext) : null,':size_bytes'=>$size,':origin_id'=>$externalAttachmentId,':uploaded_by_user_id'=>$userId]);
        return (int) $this->pdo->lastInsertId();
    }
    private function insertMailAttachment(int $tenantId,int $messageId,int $cloudFileId,string $name,string $mime,int $size): void {
        $stmt = $this->pdo->prepare('INSERT INTO mail_attachments (tenant_id,message_id,cloud_file_id,original_filename,mime_type,size_bytes,created_at) VALUES (:tenant_id,:message_id,:cloud_file_id,:filename,:mime_type,:size_bytes,NOW())');
        $stmt->execute([':tenant_id'=>$tenantId,':message_id'=>$messageId,':cloud_file_id'=>$cloudFileId,':filename'=>$name,':mime_type'=>$mime,':size_bytes'=>$size]);
    }
    private function markImported(int $id, int $cloudFileId): void { $s=$this->pdo->prepare('UPDATE mail_external_attachments SET cloud_file_id=:cloud_file_id, import_status="imported", imported_at=NOW(), error_message=NULL WHERE id=:id'); $s->execute([':cloud_file_id'=>$cloudFileId,':id'=>$id]); }
    private function markFailed(int $id, string $status, string $error): void { $this->markStatus($id, $status, $error); }
    private function markStatus(int $id, string $status, string $error): void { $s=$this->pdo->prepare('UPDATE mail_external_attachments SET import_status=:status, error_message=:error WHERE id=:id'); $s->execute([':status'=>$status,':error'=>$this->sanitize($error),':id'=>$id]); }
    private function sanitize(string $text): string { $t = preg_replace('/\s+/', ' ', $text) ?? 'error'; $t = preg_replace('/([a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,})/i', '[redacted]', $t) ?? $t; return mb_substr(trim($t), 0, 180); }
}
