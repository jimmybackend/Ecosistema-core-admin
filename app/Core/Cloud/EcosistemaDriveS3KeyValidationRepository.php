<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use PDO;

final readonly class EcosistemaDriveS3KeyValidationRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array<string,mixed>|null
     */
    public function findForValidation(int $tenantId, int $userId, int $fileId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, bucket_id, status, found_in_s3, deleted_at, s3_key
             FROM cloud_files
             WHERE tenant_id = :tenant_id
               AND user_id = :user_id
               AND id = :id
             LIMIT 1'
        );

        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $fileId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row !== false ? $row : null;
    }
}
