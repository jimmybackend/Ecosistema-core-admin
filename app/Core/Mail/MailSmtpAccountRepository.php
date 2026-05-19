<?php

declare(strict_types=1);

namespace App\Core\Mail;

use PDO;

final readonly class MailSmtpAccountRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findActiveByMailboxForUser(int $tenantId, int $userId, int $mailboxId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT s.*, m.full_address AS mailbox_full_address FROM mail_smtp_accounts s INNER JOIN mail_mailboxes m ON m.id = s.mailbox_id WHERE s.tenant_id = :tenant_id_account AND s.mailbox_id = :mailbox_id AND s.status = :status_account AND m.tenant_id = :tenant_id_mailbox AND m.status = :status_mailbox AND (m.user_id = :user_id_mailbox OR s.created_by_user_id = :user_id_created_by OR s.available_to_everyone = 1 OR m.available_to_everyone = 1) ORDER BY s.id DESC LIMIT 1');
        $stmt->execute([':tenant_id_account' => $tenantId, ':mailbox_id' => $mailboxId, ':status_account' => 'active', ':tenant_id_mailbox' => $tenantId, ':status_mailbox' => 'active', ':user_id_mailbox' => $userId, ':user_id_created_by' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function findActiveByUserFallback(int $tenantId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT s.*, m.full_address AS mailbox_full_address FROM mail_smtp_accounts s LEFT JOIN mail_mailboxes m ON m.id = s.mailbox_id AND m.tenant_id = s.tenant_id WHERE s.tenant_id = :tenant_id_account AND s.status = :status_account AND (m.user_id = :user_id_mailbox OR s.created_by_user_id = :user_id_created_by OR s.available_to_everyone = 1 OR m.available_to_everyone = 1) ORDER BY s.available_to_everyone DESC, s.id DESC LIMIT 1');
        $stmt->execute([':tenant_id_account' => $tenantId, ':status_account' => 'active', ':user_id_mailbox' => $userId, ':user_id_created_by' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function listForUser(int $tenantId, int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT s.id, s.name, s.email_address, s.host_in, s.port_in, s.ssl_in, s.host_out, s.port_out, s.ssl_out, s.username, s.status, s.last_error, s.mailbox_id, s.max_daily_email, s.enable_limit, s.available_to_everyone, CASE WHEN TRIM(COALESCE(s.password_encrypted, '')) = '' THEN 'no' ELSE 'yes' END AS password_encrypted_present, m.full_address AS mailbox_full_address FROM mail_smtp_accounts s LEFT JOIN mail_mailboxes m ON m.id = s.mailbox_id AND m.tenant_id = s.tenant_id WHERE s.tenant_id = :tenant_id_account AND (m.user_id = :user_id_mailbox OR m.available_to_everyone = 1 OR s.created_by_user_id = :user_id_created_by OR s.available_to_everyone = 1) ORDER BY s.id DESC LIMIT 100");
        $stmt->execute([':tenant_id_account' => $tenantId, ':user_id_mailbox' => $userId, ':user_id_created_by' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


    public function listForImapSyncForUser(int $tenantId, int $userId): array
    {
        $sql = "SELECT s.id, s.tenant_id, s.mailbox_id, s.name, s.email_address, s.host_in, s.port_in, s.ssl_in, s.username, s.status, m.full_address AS mailbox_full_address, CASE WHEN TRIM(COALESCE(s.password_encrypted, '')) = '' THEN 0 ELSE 1 END AS password_encrypted_present FROM mail_smtp_accounts s LEFT JOIN mail_mailboxes m ON m.id = s.mailbox_id AND m.tenant_id = s.tenant_id WHERE s.tenant_id = :tenant_id AND s.status = 'active' AND TRIM(COALESCE(s.host_in, '')) <> '' AND s.port_in IS NOT NULL AND TRIM(COALESCE(s.username, '')) <> '' AND TRIM(COALESCE(s.password_encrypted, '')) <> '' AND (s.created_by_user_id = :created_by_user_id OR m.user_id = :mailbox_user_id OR s.available_to_everyone = 1 OR m.available_to_everyone = 1) ORDER BY s.id DESC LIMIT 100";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':tenant_id' => $tenantId, ':created_by_user_id' => $userId, ':mailbox_user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findForUserOrTenant(int $tenantId, int $userId, int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT s.*, m.full_address AS mailbox_full_address FROM mail_smtp_accounts s LEFT JOIN mail_mailboxes m ON m.id = s.mailbox_id AND m.tenant_id = s.tenant_id WHERE s.id = :id AND s.tenant_id = :tenant_id_account AND (m.user_id = :user_id_mailbox OR m.available_to_everyone = 1 OR s.created_by_user_id = :user_id_created_by OR s.available_to_everyone = 1) LIMIT 1');
        $stmt->execute([':id' => $id, ':tenant_id_account' => $tenantId, ':user_id_mailbox' => $userId, ':user_id_created_by' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO mail_smtp_accounts (tenant_id, mailbox_id, created_by_user_id, name, email_address, host_in, port_in, ssl_in, host_out, port_out, ssl_out, username, password_encrypted, max_daily_email, enable_limit, available_to_everyone, status, created_at, updated_at) VALUES (:tenant_id, :mailbox_id, :created_by_user_id, :name, :email_address, :host_in, :port_in, :ssl_in, :host_out, :port_out, :ssl_out, :username, :password_encrypted, :max_daily_email, :enable_limit, :available_to_everyone, :status, NOW(), NOW())');
        $stmt->execute([
            ':tenant_id' => $data['tenant_id'], ':mailbox_id' => $data['mailbox_id'], ':created_by_user_id' => $data['created_by_user_id'], ':name' => $data['name'], ':email_address' => $data['email_address'], ':host_in' => $data['host_in'], ':port_in' => $data['port_in'], ':ssl_in' => $data['ssl_in'], ':host_out' => $data['host_out'], ':port_out' => $data['port_out'], ':ssl_out' => $data['ssl_out'], ':username' => $data['username'], ':password_encrypted' => $data['password_encrypted'], ':max_daily_email' => $data['max_daily_email'], ':enable_limit' => $data['enable_limit'], ':available_to_everyone' => $data['available_to_everyone'], ':status' => $data['status'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $tenantId, int $id, array $data): void
    {
        $stmt = $this->pdo->prepare('UPDATE mail_smtp_accounts SET name = :name, email_address = :email_address, host_in = :host_in, port_in = :port_in, ssl_in = :ssl_in, host_out = :host_out, port_out = :port_out, ssl_out = :ssl_out, username = :username, password_encrypted = :password_encrypted, max_daily_email = :max_daily_email, enable_limit = :enable_limit, available_to_everyone = :available_to_everyone, status = :status, updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id');
        $stmt->execute([':id' => $id, ':tenant_id' => $tenantId, ':name' => $data['name'], ':email_address' => $data['email_address'], ':host_in' => $data['host_in'], ':port_in' => $data['port_in'], ':ssl_in' => $data['ssl_in'], ':host_out' => $data['host_out'], ':port_out' => $data['port_out'], ':ssl_out' => $data['ssl_out'], ':username' => $data['username'], ':password_encrypted' => $data['password_encrypted'], ':max_daily_email' => $data['max_daily_email'], ':enable_limit' => $data['enable_limit'], ':available_to_everyone' => $data['available_to_everyone'], ':status' => $data['status']]);
    }

    public function disable(int $tenantId, int $id): void
    {
        $stmt = $this->pdo->prepare('UPDATE mail_smtp_accounts SET status = :status, updated_at = NOW() WHERE id = :id AND tenant_id = :tenant_id');
        $stmt->execute([':status' => 'disabled', ':id' => $id, ':tenant_id' => $tenantId]);
    }



    public function findAuthorizedById(int $tenantId, int $userId, int $accountId): ?array
    {
        $sql = 'SELECT s.id, s.status FROM mail_smtp_accounts s INNER JOIN mail_mailboxes m ON m.id = s.mailbox_id AND m.tenant_id = s.tenant_id WHERE s.tenant_id = :tenant_id AND s.id = :account_id AND (s.created_by_user_id = :created_by_user_id OR m.user_id = :mailbox_user_id OR s.available_to_everyone = 1 OR m.available_to_everyone = 1) LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenantId,
            ':account_id' => $accountId,
            ':created_by_user_id' => $userId,
            ':mailbox_user_id' => $userId,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function deleteForUser(int $tenantId, int $userId, int $accountId): bool
    {
        $account = $this->findAuthorizedById($tenantId, $userId, $accountId);
        if (!is_array($account)) {
            throw new \RuntimeException('Cuenta SMTP no encontrada o sin acceso.');
        }

        if ((string) ($account['status'] ?? '') !== 'disabled') {
            throw new \RuntimeException('Primero desactiva la cuenta SMTP antes de eliminarla.');
        }

        $stmt = $this->pdo->prepare('DELETE FROM mail_smtp_accounts WHERE id = :account_id AND tenant_id = :tenant_id');
        try {
            $stmt->execute([':account_id' => $accountId, ':tenant_id' => $tenantId]);
        } catch (\Throwable) {
            throw new \RuntimeException('No se puede eliminar porque tiene datos relacionados. Se mantendrá desactivada.');
        }

        return $stmt->rowCount() > 0;
    }

    public function countForTenant(int $tenantId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM mail_smtp_accounts WHERE tenant_id = :tenant_id');
        $stmt->execute([':tenant_id' => $tenantId]);
        return (int) $stmt->fetchColumn();
    }
}
