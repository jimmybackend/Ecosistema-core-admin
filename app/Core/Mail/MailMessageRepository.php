<?php

declare(strict_types=1);

namespace App\Core\Mail;

use PDO;

final readonly class MailMessageRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listByUser(int $tenantId, int $userId, int $limit = 100): array
    {
        return $this->listMessagesForMailbox($tenantId, $userId, 0, $limit);
    }

    public function listMessagesForMailbox(int $tenantId, int $userId, int $mailboxId, int $limit, ?string $folder = null): array
    {
        $sql = 'SELECT mm.id, mm.tenant_id, mm.mailbox_id, mm.folder_id, mm.user_id, mm.direction, mm.from_address, mm.to_addresses, mm.cc_addresses, mm.bcc_addresses, mm.subject, mm.has_attachments, mm.is_read, mm.is_starred, mm.is_deleted, mm.is_spam, mm.is_draft, mm.sent_at, mm.received_at, mm.created_at, f.name AS folder_name, f.system_name AS folder_system_name, mb.full_address AS mailbox_address FROM mail_messages mm LEFT JOIN mail_folders f ON f.id = mm.folder_id AND f.tenant_id = mm.tenant_id INNER JOIN mail_mailboxes mb ON mb.id = mm.mailbox_id AND mb.tenant_id = mm.tenant_id LEFT JOIN mail_smtp_accounts sa ON sa.mailbox_id = mb.id AND sa.tenant_id = mb.tenant_id WHERE mm.tenant_id = :tenant_id AND COALESCE(mm.is_deleted,0)=0 AND (mm.user_id = :user_id_msg OR mb.user_id = :user_id_mailbox OR mb.available_to_everyone = 1 OR sa.available_to_everyone = 1)';
        if ($mailboxId > 0) {
            $sql .= ' AND mm.mailbox_id = :mailbox_id';
        }
        if (is_string($folder) && trim($folder) !== '') {
            $sql .= ' AND (f.system_name = :folder_system_name OR f.name = :folder_name)';
        }
        $sql .= ' ORDER BY COALESCE(mm.received_at, mm.sent_at, mm.created_at) DESC, mm.id DESC LIMIT :limit_rows';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id_msg', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id_mailbox', $userId, PDO::PARAM_INT);
        if ($mailboxId > 0) { $stmt->bindValue(':mailbox_id', $mailboxId, PDO::PARAM_INT); }
        if (is_string($folder) && trim($folder) !== '') {
            $folderValue = trim($folder);
            $stmt->bindValue(':folder_system_name', $folderValue);
            $stmt->bindValue(':folder_name', $folderValue);
        }
        $stmt->bindValue(':limit_rows', max(1, min(250, $limit)), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findByIdForUser(int $tenantId, int $userId, int $id): ?array
    {
        return $this->findMessageForRead($tenantId, $userId, $id);
    }

    public function findMessageForRead(int $tenantId, int $userId, int $messageId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT mm.id, mm.tenant_id, mm.mailbox_id, mm.folder_id, mm.user_id, mm.message_uuid, mm.direction, mm.mail_scope, mm.from_address, mm.to_addresses, mm.cc_addresses, mm.bcc_addresses, mm.subject, mm.body_text, mm.body_html, mm.has_attachments, mm.is_read, mm.read_at, mm.read_count, mm.is_starred, mm.is_draft, mm.is_deleted, mm.is_spam, mm.received_at, mm.sent_at, mm.created_at, mm.updated_at, f.name AS folder_name, f.system_name AS folder_system_name, mb.full_address AS mailbox_address FROM mail_messages mm LEFT JOIN mail_folders f ON f.id = mm.folder_id AND f.tenant_id = mm.tenant_id INNER JOIN mail_mailboxes mb ON mb.id = mm.mailbox_id AND mb.tenant_id = mm.tenant_id LEFT JOIN mail_smtp_accounts sa ON sa.mailbox_id = mb.id AND sa.tenant_id = mb.tenant_id WHERE mm.id = :message_id AND mm.tenant_id = :tenant_id AND (mm.user_id = :user_id_msg OR mb.user_id = :user_id_mailbox OR mb.available_to_everyone = 1 OR sa.available_to_everyone = 1) LIMIT 1');
        $stmt->execute([':message_id' => $messageId, ':tenant_id' => $tenantId, ':user_id_msg' => $userId, ':user_id_mailbox' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function createDraft(array $data): bool { /* unchanged */
        $stmt = $this->pdo->prepare('INSERT INTO mail_messages (tenant_id, mailbox_id, folder_id, user_id, message_uuid, direction, mail_scope, from_address, to_addresses, cc_addresses, bcc_addresses, subject, body_text, body_html, has_attachments, is_read, is_draft, is_deleted) VALUES (:tenant_id, :mailbox_id, :folder_id, :user_id, :message_uuid, :direction, :mail_scope, :from_address, :to_addresses, :cc_addresses, :bcc_addresses, :subject, :body_text, :body_html, :has_attachments, :is_read, :is_draft, :is_deleted)');
        return $stmt->execute($data);
    }
    public function markReadToggle(int $tenantId, int $userId, int $id): bool { $message=$this->findByIdForUser($tenantId,$userId,$id); if($message===null){return false;} $isRead=(int)($message['is_read']??0)===1; $stmt=$this->pdo->prepare('UPDATE mail_messages SET is_read = :is_read, read_at = :read_at, read_count = :read_count, updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id'); $readCount=(int)($message['read_count']??0); return $stmt->execute([':is_read'=>$isRead?0:1,':read_at'=>$isRead?null:date('Y-m-d H:i:s'),':read_count'=>$isRead?$readCount:$readCount+1,':id'=>$id,':tenant_id'=>$tenantId]); }
    public function toggleStar(int $tenantId, int $userId, int $id): bool { $stmt=$this->pdo->prepare('UPDATE mail_messages SET is_starred = CASE WHEN is_starred = 1 THEN 0 ELSE 1 END, updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id'); return $stmt->execute([':id'=>$id,':tenant_id'=>$tenantId,':user_id'=>$userId]) && $stmt->rowCount() > 0; }
    public function moveToTrash(int $tenantId, int $userId, int $id): bool { $stmt=$this->pdo->prepare('UPDATE mail_messages SET is_deleted = 1, updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id'); return $stmt->execute([':id'=>$id,':tenant_id'=>$tenantId,':user_id'=>$userId]) && $stmt->rowCount() > 0; }
    public function markDraftAsSent(int $tenantId, int $userId, int $id): bool { $stmt=$this->pdo->prepare('UPDATE mail_messages SET is_draft = 0, sent_at = NOW(), updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id AND is_draft = 1 AND is_deleted = 0'); return $stmt->execute([':id'=>$id,':tenant_id'=>$tenantId,':user_id'=>$userId]) && $stmt->rowCount() > 0; }
}
