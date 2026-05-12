<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use PDO;

final readonly class EcosistemaDriveFileRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function listMetadataByUser(int $tenantId, int $userId, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, original_name, mime_type, size_bytes, status, uploaded_at, origin_module
             FROM cloud_files
             WHERE tenant_id = :tenant_id
               AND user_id = :user_id
               AND status <> :deleted_status
             ORDER BY uploaded_at DESC, id DESC
             LIMIT :limit'
        );

        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':deleted_status', 'deleted');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<string,mixed>|null
     */
    public function findVisibleById(int $tenantId, int $userId, int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, original_name, mime_type, extension, size_bytes, status, virus_scan_status,
                    access_type, encrypted, found_in_s3, origin_module, origin_table, origin_id,
                    uploaded_by_user_id, uploaded_at, updated_at, deleted_at
             FROM cloud_files
             WHERE tenant_id = :tenant_id
               AND user_id = :user_id
               AND id = :id
               AND status <> :deleted_status
             LIMIT 1'
        );

        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':deleted_status', 'deleted');
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result !== false ? $result : null;
    }
}
