<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use PDO;

final readonly class EcosistemaDriveRootRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<int,array<string,mixed>> */
    public function listVisible(int $tenantId, int $userId, int $limit = 20): array
    {
        $stmt = $this->pdo->prepare('SELECT id, bucket_id, display_name, quota_bytes, used_bytes, file_count, status, created_at, updated_at FROM cloud_user_roots WHERE tenant_id = :tenant_id AND user_id = :user_id AND status = :status ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':status', 'active');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
