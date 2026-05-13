<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use PDO;

final readonly class EcosistemaDriveAccessLogRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<int,array<string,mixed>> */
    public function listRecentForTenant(int $tenantId, int $limit = 100): array
    {
        $tenantId = $this->assertPositiveInt($tenantId, 'tenant_id');
        $limit = $this->sanitizeLimit($limit);

        $sql = 'SELECT l.id, l.file_id, l.user_id, l.action, l.ip_address, l.user_agent, l.country, l.region, l.city, l.metadata_json, l.created_at, f.original_name AS file_original_name, u.email AS user_email
                FROM cloud_file_access_logs l
                LEFT JOIN cloud_files f ON f.id = l.file_id AND f.tenant_id = l.tenant_id
                LEFT JOIN core_users u ON u.id = l.user_id
                WHERE l.tenant_id = :tenant_id
                ORDER BY l.created_at DESC, l.id DESC
                LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<int,array<string,mixed>> */
    public function listForFile(int $tenantId, int $fileId, int $limit = 100): array
    {
        $tenantId = $this->assertPositiveInt($tenantId, 'tenant_id');
        $fileId = $this->assertPositiveInt($fileId, 'file_id');
        $limit = $this->sanitizeLimit($limit);

        $sql = 'SELECT l.id, l.file_id, l.user_id, l.action, l.ip_address, l.user_agent, l.country, l.region, l.city, l.metadata_json, l.created_at, f.original_name AS file_original_name, u.email AS user_email
                FROM cloud_file_access_logs l
                LEFT JOIN cloud_files f ON f.id = l.file_id AND f.tenant_id = l.tenant_id
                LEFT JOIN core_users u ON u.id = l.user_id
                WHERE l.tenant_id = :tenant_id AND l.file_id = :file_id
                ORDER BY l.created_at DESC, l.id DESC
                LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':file_id', $fileId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<int,array<string,mixed>> */
    public function summarizeByAction(int $tenantId): array
    {
        $tenantId = $this->assertPositiveInt($tenantId, 'tenant_id');

        $sql = 'SELECT action, COUNT(*) AS total
                FROM cloud_file_access_logs
                WHERE tenant_id = :tenant_id
                GROUP BY action
                ORDER BY total DESC, action ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function sanitizeLimit(int $limit): int
    {
        if ($limit < 1) {
            return 1;
        }

        if ($limit > 200) {
            return 200;
        }

        return $limit;
    }

    private function assertPositiveInt(int $value, string $field): int
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException($field . ' must be a positive integer.');
        }

        return $value;
    }
}
