<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use PDO;

final readonly class EcosistemaDriveFolderRepository
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
            'SELECT id, name, folder_type, access_type, found_in_s3, is_system,
                    parent_folder_id, root_id, created_at, updated_at, deleted_at
             FROM cloud_folders
             WHERE tenant_id = :tenant_id
               AND user_id = :user_id
               AND is_deleted = :is_deleted
             ORDER BY is_system DESC, folder_type ASC, name ASC, id DESC
             LIMIT :limit'
        );

        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':is_deleted', 0, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
