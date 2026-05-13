<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use PDO;

final readonly class EcosistemaDriveRepairJobRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<int,array<string,mixed>> */
    public function listRecentJobs(int $tenantId, int $limit = 100): array
    {
        $tenantId = $this->assertPositiveInt($tenantId, 'tenant_id');
        $limit = $this->sanitizeLimit($limit);

        $sql = 'SELECT j.id, j.tenant_id, j.bucket_id, b.name AS bucket_name, j.status, j.total_s3, j.total_db, j.total_actions, j.prefix, j.last_message, j.started_at, j.finished_at, j.created_at, j.updated_at,
                       (SELECT COUNT(*) FROM cloud_repair_logs l WHERE l.tenant_id = j.tenant_id AND l.repair_job_id = j.id) AS logs_count
                FROM cloud_repair_jobs j
                LEFT JOIN cloud_buckets b ON b.id = j.bucket_id AND b.tenant_id = j.tenant_id
                WHERE j.tenant_id = :tenant_id
                ORDER BY j.created_at DESC, j.id DESC
                LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<string,mixed>|null */
    public function findJob(int $tenantId, int $jobId): ?array
    {
        $tenantId = $this->assertPositiveInt($tenantId, 'tenant_id');
        $jobId = $this->assertPositiveInt($jobId, 'job_id');

        $sql = 'SELECT j.id, j.tenant_id, j.bucket_id, b.name AS bucket_name, j.status, j.total_s3, j.total_db, j.total_actions, j.prefix, j.last_message, j.started_at, j.finished_at, j.created_at, j.updated_at,
                       (SELECT COUNT(*) FROM cloud_repair_logs l WHERE l.tenant_id = j.tenant_id AND l.repair_job_id = j.id) AS logs_count
                FROM cloud_repair_jobs j
                LEFT JOIN cloud_buckets b ON b.id = j.bucket_id AND b.tenant_id = j.tenant_id
                WHERE j.tenant_id = :tenant_id AND j.id = :job_id
                LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':job_id', $jobId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    /** @return array<int,array<string,mixed>> */
    public function listJobLogs(int $tenantId, int $jobId, int $limit = 200): array
    {
        $tenantId = $this->assertPositiveInt($tenantId, 'tenant_id');
        $jobId = $this->assertPositiveInt($jobId, 'job_id');
        $limit = $this->sanitizeLimit($limit);

        $sql = 'SELECT id, tenant_id, repair_job_id, file_id, action, old_s3_key, new_s3_key, detail, created_at
                FROM cloud_repair_logs
                WHERE tenant_id = :tenant_id AND repair_job_id = :job_id
                ORDER BY created_at DESC, id DESC
                LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':job_id', $jobId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<int,array<string,mixed>> */
    public function summarizeJobs(int $tenantId): array
    {
        $tenantId = $this->assertPositiveInt($tenantId, 'tenant_id');

        $sql = 'SELECT status, COUNT(*) AS total
                FROM cloud_repair_jobs
                WHERE tenant_id = :tenant_id
                GROUP BY status
                ORDER BY total DESC, status ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function sanitizeLimit(int $limit): int
    {
        return max(1, min(200, $limit));
    }

    private function assertPositiveInt(int $value, string $field): int
    {
        if ($value <= 0) {
            throw new \InvalidArgumentException($field . ' must be a positive integer.');
        }

        return $value;
    }
}
