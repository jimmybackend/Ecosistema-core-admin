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
        $sql = 'SELECT COUNT(*)
            FROM core_user_roles ur
            INNER JOIN core_role_permissions rp
                ON rp.tenant_id = ur.tenant_id
               AND rp.role_id = ur.role_id
            INNER JOIN core_permissions p
                ON p.id = rp.permission_id
            WHERE ur.tenant_id = :tenant_id
              AND ur.user_id = :user_id
              AND p.code = :permission_code';

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':tenant_id' => $tenantId,
                ':user_id' => $userId,
                ':permission_code' => $permissionCode,
            ]);

            return (int) $stmt->fetchColumn() > 0;
        } catch (Throwable) {
            return false;
        }
    }
}
