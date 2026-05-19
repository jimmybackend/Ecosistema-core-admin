<?php

declare(strict_types=1);

namespace App\Core\Mail;

use PDO;

final readonly class MailboxRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listActiveForUser(int $tenantId, int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, tenant_id, user_id, full_address, display_name, status, available_to_everyone FROM mail_mailboxes WHERE tenant_id = :tenant_id AND status = :status AND (user_id = :user_id OR available_to_everyone = 1) ORDER BY available_to_everyone DESC, id DESC LIMIT 100');
        $stmt->execute([':tenant_id' => $tenantId, ':user_id' => $userId, ':status' => 'active']);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findActiveForUserById(int $tenantId, int $userId, int $mailboxId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, tenant_id, user_id, full_address, status, available_to_everyone FROM mail_mailboxes WHERE id = :id AND tenant_id = :tenant_id AND status = :status AND (user_id = :user_id OR available_to_everyone = 1) LIMIT 1');
        $stmt->execute([':id' => $mailboxId, ':tenant_id' => $tenantId, ':user_id' => $userId, ':status' => 'active']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function findDraftFolderId(int $tenantId, int $mailboxId): ?int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM mail_folders WHERE tenant_id = :tenant_id AND mailbox_id = :mailbox_id AND system_name = :system_name ORDER BY id ASC LIMIT 1');
        $stmt->execute([':tenant_id' => $tenantId, ':mailbox_id' => $mailboxId, ':system_name' => 'drafts']);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int) $id;
    }
}
