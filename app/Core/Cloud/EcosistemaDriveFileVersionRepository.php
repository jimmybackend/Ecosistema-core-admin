<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use PDO;

final readonly class EcosistemaDriveFileVersionRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function listForFile(int $tenantId, int $fileId, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, file_id, bucket_id, version_no, s3_key, s3_version_id, size_bytes, checksum_sha256, created_by_user_id, created_at
             FROM cloud_file_versions
             WHERE tenant_id = :tenant_id
               AND file_id = :file_id
             ORDER BY version_no DESC, id DESC
             LIMIT :limit'
        );

        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':file_id', $fileId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
