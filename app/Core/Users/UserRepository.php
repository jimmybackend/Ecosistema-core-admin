<?php

declare(strict_types=1);

namespace App\Core\Users;

use PDO;

final readonly class UserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<int,array<string,mixed>> */
    public function listRecent(int $limit = 100): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.id, u.tenant_id, u.email, u.username, u.display_name, u.user_type, u.status, u.last_login_at, u.created_at,
                    t.name AS tenant_name, t.slug AS tenant_slug
             FROM core_users u
             LEFT JOIN core_tenants t ON t.id = u.tenant_id
             ORDER BY u.id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<string,mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, tenant_id, email, username, display_name, first_name, last_name, phone, user_type, status, created_at, updated_at
             FROM core_users
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($user) ? $user : null;
    }

    /** @return array<int,array<string,mixed>> */
    public function listTenants(): array
    {
        $stmt = $this->pdo->query('SELECT id, name, slug, status FROM core_tenants ORDER BY id DESC LIMIT 200');
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function tenantExists(int $tenantId): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM core_tenants WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $tenantId]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO core_users (tenant_id, email, username, password_hash, display_name, first_name, last_name, phone, user_type, status)
             VALUES (:tenant_id, :email, :username, :password_hash, :display_name, :first_name, :last_name, :phone, :user_type, :status)'
        );

        return $stmt->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $data[':id'] = $id;
        $stmt = $this->pdo->prepare(
            'UPDATE core_users
             SET tenant_id = :tenant_id,
                 email = :email,
                 username = :username,
                 display_name = :display_name,
                 first_name = :first_name,
                 last_name = :last_name,
                 phone = :phone,
                 user_type = :user_type,
                 status = :status,
                 updated_at = NOW()
             WHERE id = :id'
        );
        return $stmt->execute($data);
    }

    public function updatePassword(int $id, string $passwordHash): bool
    {
        $stmt = $this->pdo->prepare('UPDATE core_users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([':id' => $id, ':password_hash' => $passwordHash]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare('UPDATE core_users SET status = :status, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([':id' => $id, ':status' => $status]);
    }
}
