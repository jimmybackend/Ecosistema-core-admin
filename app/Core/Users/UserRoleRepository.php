<?php

declare(strict_types=1);

namespace App\Core\Users;

use PDO;

final readonly class UserRoleRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<string,mixed>|null */
    public function findUser(int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, tenant_id, email, username, display_name, status FROM core_users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($user) ? $user : null;
    }

    /** @return array<int,array<string,mixed>> */
    public function listRolesForTenant(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, tenant_id, name, slug, description, is_system, created_at, updated_at FROM core_roles WHERE tenant_id = :tenant_id ORDER BY name ASC, id ASC');
        $stmt->execute([':tenant_id' => $tenantId]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_map(static function (array $row): array {
            $row['code'] = $row['slug'] ?? '';
            $row['status'] = 'active';
            return $row;
        }, $rows);
    }

    /** @return array<int,int> */
    public function listAssignedRoleIds(int $tenantId, int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT role_id FROM core_user_roles WHERE tenant_id = :tenant_id AND user_id = :user_id');
        $stmt->execute([':tenant_id' => $tenantId, ':user_id' => $userId]);

        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return array_map(static fn (mixed $id): int => (int) $id, is_array($rows) ? $rows : []);
    }

    /** @return array<int,int> */
    public function filterValidRoleIdsForTenant(int $tenantId, array $roleIds): array
    {
        if ($roleIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($roleIds), '?'));
        $params = array_merge([$tenantId], $roleIds);

        $stmt = $this->pdo->prepare("SELECT id FROM core_roles WHERE tenant_id = ? AND id IN ({$placeholders})");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return array_map(static fn (mixed $id): int => (int) $id, is_array($rows) ? $rows : []);
    }

    public function replaceUserRoles(int $tenantId, int $userId, array $roleIds, ?int $assignedByUserId): void
    {
        $this->pdo->beginTransaction();

        try {
            $deleteStmt = $this->pdo->prepare('DELETE FROM core_user_roles WHERE tenant_id = :tenant_id AND user_id = :user_id');
            $deleteStmt->execute([':tenant_id' => $tenantId, ':user_id' => $userId]);

            if ($roleIds !== []) {
                $insertStmt = $this->pdo->prepare(
                    'INSERT INTO core_user_roles (tenant_id, user_id, role_id, assigned_by_user_id, assigned_at) VALUES (:tenant_id, :user_id, :role_id, :assigned_by_user_id, NOW())'
                );

                foreach ($roleIds as $roleId) {
                    $insertStmt->execute([
                        ':tenant_id' => $tenantId,
                        ':user_id' => $userId,
                        ':role_id' => $roleId,
                        ':assigned_by_user_id' => $assignedByUserId,
                    ]);
                }
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}
