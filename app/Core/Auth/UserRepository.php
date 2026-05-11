<?php

declare(strict_types=1);

namespace App\Core\Auth;

use PDO;

final class UserRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function findActiveByEmail(string $email): ?array
    {
        $sql = 'SELECT id, tenant_id, email, password_hash, display_name FROM core_users WHERE email = :email AND status = :status LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':email' => $email,
            ':status' => 'active',
        ]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($user) ? $user : null;
    }

    public function updateLastLoginAt(int $userId): void
    {
        $sql = 'UPDATE core_users SET last_login_at = NOW() WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $userId]);
    }
}
