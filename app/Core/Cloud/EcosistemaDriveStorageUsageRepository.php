<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use PDO;

final readonly class EcosistemaDriveStorageUsageRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<string,mixed> */
    public function summarizeTenantFiles(int $tenantId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                COUNT(*) AS total_files,
                COALESCE(SUM(size_bytes), 0) AS total_bytes,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_files,
                SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) AS archived_files,
                SUM(CASE WHEN status = 'missing' THEN 1 ELSE 0 END) AS missing_files,
                SUM(CASE WHEN status = 'deleted' OR deleted_at IS NOT NULL THEN 1 ELSE 0 END) AS deleted_files,
                SUM(CASE WHEN found_in_s3 = 1 THEN 1 ELSE 0 END) AS found_in_s3_count,
                SUM(CASE WHEN found_in_s3 = 0 OR found_in_s3 IS NULL THEN 1 ELSE 0 END) AS not_found_in_s3_count
            FROM cloud_files
            WHERE tenant_id = :tenant_id"
        );
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        return (array) ($stmt->fetch(PDO::FETCH_ASSOC) ?: []);
    }

    /** @return array<int,array<string,mixed>> */
    public function summarizeByBucket(int $tenantId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT f.bucket_id, COALESCE(b.name, 'N/A') AS bucket_name,
                    COALESCE(b.provider, 'N/A') AS provider,
                    COALESCE(b.status, 'N/A') AS status,
                    COUNT(*) AS file_count,
                    COALESCE(SUM(f.size_bytes), 0) AS total_bytes
             FROM cloud_files f
             LEFT JOIN cloud_buckets b ON b.id = f.bucket_id AND b.tenant_id = f.tenant_id
             WHERE f.tenant_id = :tenant_id
             GROUP BY f.bucket_id, b.name, b.provider, b.status
             ORDER BY total_bytes DESC, file_count DESC"
        );
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<int,array<string,mixed>> */
    public function summarizeByUser(int $tenantId, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT f.user_id, COALESCE(u.email, '') AS email, COALESCE(u.display_name, '') AS display_name,
                    COUNT(*) AS file_count, COALESCE(SUM(f.size_bytes), 0) AS total_bytes
             FROM cloud_files f
             LEFT JOIN core_users u ON u.id = f.user_id AND u.tenant_id = f.tenant_id
             WHERE f.tenant_id = :tenant_id
             GROUP BY f.user_id, u.email, u.display_name
             ORDER BY total_bytes DESC, file_count DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<int,array<string,mixed>> */
    public function summarizeByExtension(int $tenantId, int $limit = 50): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(NULLIF(TRIM(extension), ''), '(sin extensión)') AS extension,
                    COUNT(*) AS file_count, COALESCE(SUM(size_bytes), 0) AS total_bytes
             FROM cloud_files
             WHERE tenant_id = :tenant_id
             GROUP BY COALESCE(NULLIF(TRIM(extension), ''), '(sin extensión)')
             ORDER BY total_bytes DESC, file_count DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<int,array<string,mixed>> */
    public function summarizeDailyUsage(int $tenantId, int $days = 30): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT usage_date, total_bytes, file_count, mail_attachment_bytes, cloud_document_bytes, other_bytes
             FROM cloud_storage_usage_daily
             WHERE tenant_id = :tenant_id
               AND usage_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
             ORDER BY usage_date DESC"
        );
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
