<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use PDO;

final readonly class EcosistemaDriveRootRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array<string,mixed>|null
     */
    public function findForUser(int $tenantId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, bucket_id, display_name, quota_bytes, used_bytes, file_count, status, created_at, updated_at
             FROM cloud_user_roots
             WHERE tenant_id = :tenant_id
               AND user_id = :user_id
               AND status <> :deleted_status
             ORDER BY id DESC
             LIMIT 1'
        );

        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':deleted_status', 'deleted');
        $stmt->execute();

        $root = $stmt->fetch(PDO::FETCH_ASSOC);
        return $root !== false ? $root : null;
    }
}
