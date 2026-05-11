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
        $stmt = $this->pdo->prepare('SELECT id, direction, from_address, to_addresses, subject, has_attachments, is_read, read_at, read_count, is_starred, is_draft, is_deleted, received_at, sent_at, created_at FROM mail_messages WHERE tenant_id = :tenant_id AND user_id = :user_id AND is_deleted = 0 ORDER BY created_at DESC, id DESC LIMIT :limit');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findByIdForUser(int $tenantId, int $userId, int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, tenant_id, mailbox_id, folder_id, user_id, message_uuid, direction, mail_scope, from_address, to_addresses, cc_addresses, bcc_addresses, subject, body_text, body_html, has_attachments, is_read, read_at, read_count, is_starred, is_draft, is_deleted, received_at, sent_at, created_at, updated_at FROM mail_messages WHERE id = :id AND tenant_id = :tenant_id AND user_id = :user_id LIMIT 1');
        $stmt->execute([':id' => $id, ':tenant_id' => $tenantId, ':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function createDraft(array $data): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO mail_messages (tenant_id, mailbox_id, folder_id, user_id, message_uuid, direction, mail_scope, from_address, to_addresses, cc_addresses, bcc_addresses, subject, body_text, body_html, has_attachments, is_read, is_draft, is_deleted) VALUES (:tenant_id, :mailbox_id, :folder_id, :user_id, :message_uuid, :direction, :mail_scope, :from_address, :to_addresses, :cc_addresses, :bcc_addresses, :subject, :body_text, :body_html, :has_attachments, :is_read, :is_draft, :is_deleted)');
        return $stmt->execute($data);
    }

    public function markReadToggle(int $tenantId, int $userId, int $id): bool
    {
        $message = $this->findByIdForUser($tenantId, $userId, $id);
        if ($message === null) {
            return false;
        }
        $isRead = (int) ($message['is_read'] ?? 0) === 1;
        $stmt = $this->pdo->prepare('UPDATE mail_messages SET is_read = :is_read, read_at = :read_at, read_count = :read_count, updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id AND user_id = :user_id');
        $readCount = (int) ($message['read_count'] ?? 0);
        return $stmt->execute([
            ':is_read' => $isRead ? 0 : 1,
            ':read_at' => $isRead ? null : date('Y-m-d H:i:s'),
            ':read_count' => $isRead ? $readCount : $readCount + 1,
            ':id' => $id,
            ':tenant_id' => $tenantId,
            ':user_id' => $userId,
        ]);
    }

    public function toggleStar(int $tenantId, int $userId, int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE mail_messages SET is_starred = CASE WHEN is_starred = 1 THEN 0 ELSE 1 END, updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id AND user_id = :user_id');
        return $stmt->execute([':id' => $id, ':tenant_id' => $tenantId, ':user_id' => $userId]) && $stmt->rowCount() > 0;
    }

    public function moveToTrash(int $tenantId, int $userId, int $id): bool
    {
        $stmt = $this->pdo->prepare('UPDATE mail_messages SET is_deleted = 1, updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id AND user_id = :user_id');
        return $stmt->execute([':id' => $id, ':tenant_id' => $tenantId, ':user_id' => $userId]) && $stmt->rowCount() > 0;
    }
}
