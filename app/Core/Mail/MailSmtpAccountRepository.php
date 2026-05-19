<?php

declare(strict_types=1);

namespace App\Core\Mail;

use PDO;

final readonly class MailSmtpAccountRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findActiveByMailbox(int $tenantId, int $mailboxId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM mail_smtp_accounts WHERE tenant_id = :tenant_id AND mailbox_id = :mailbox_id AND status = :status ORDER BY id DESC LIMIT 1');
        $stmt->execute([':tenant_id' => $tenantId, ':mailbox_id' => $mailboxId, ':status' => 'active']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function findActiveByUserFallback(int $tenantId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT s.* FROM mail_smtp_accounts s LEFT JOIN mail_mailboxes m ON m.id = s.mailbox_id WHERE s.tenant_id = :tenant_id AND s.status = :status AND (m.user_id = :user_id OR s.available_to_everyone = 1) ORDER BY s.available_to_everyone DESC, s.id DESC LIMIT 1');
        $stmt->execute([':tenant_id' => $tenantId, ':status' => 'active', ':user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function listForUser(int $tenantId, int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT s.id, s.name, s.email_address, s.host_out, s.port_out, s.ssl_out, s.username, s.status, s.last_error, s.mailbox_id FROM mail_smtp_accounts s LEFT JOIN mail_mailboxes m ON m.id = s.mailbox_id WHERE s.tenant_id = :tenant_id AND (m.user_id = :user_id OR s.created_by_user_id = :user_id OR s.available_to_everyone = 1) ORDER BY s.id DESC LIMIT 100');
        $stmt->execute([':tenant_id' => $tenantId, ':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
