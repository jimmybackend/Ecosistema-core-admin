<?php

declare(strict_types=1);

namespace App\Core\Auth;

use PDO;
use Throwable;

final class AuthorizationRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function userHasPermission(int $userId, int $tenantId, string $permissionCode): bool
    {
        $sql = 'SELECT 1
            FROM core_user_roles ur
            INNER JOIN core_roles r ON r.id = ur.role_id
            INNER JOIN core_role_permissions rp ON rp.role_id = r.id
            INNER JOIN core_permissions p ON p.id = rp.permission_id
            WHERE ur.user_id = :user_id
              AND ur.tenant_id = :tenant_id
              AND r.tenant_id = :tenant_id
              AND rp.tenant_id = :tenant_id
              AND p.code = :permission_code
            LIMIT 1';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $userId,
                ':tenant_id' => $tenantId,
                ':permission_code' => $permissionCode,
            ]);

            return $stmt->fetchColumn() !== false;
        } catch (Throwable) {
            return false;
        }
    }
}
