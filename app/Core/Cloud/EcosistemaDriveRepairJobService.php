<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class EcosistemaDriveRepairJobService
{
    public function __construct(private EcosistemaDriveRepairJobRepository $repository)
    {
    }

    /** @return array{summary: array<int,array<string,mixed>>, jobs: array<int,array<string,mixed>>} */
    public function listRecentForTenant(int $tenantId, int $limit = 100): array
    {
        return [
            'summary' => $this->mapSummary($this->repository->summarizeJobs($tenantId)),
            'jobs' => $this->mapJobs($this->repository->listRecentJobs($tenantId, $limit)),
        ];
    }

    /** @return array{job: array<string,mixed>|null, logs: array<int,array<string,mixed>>} */
    public function getJobDetail(int $tenantId, int $jobId, int $logLimit = 200): array
    {
        $job = $this->repository->findJob($tenantId, $jobId);

        return [
            'job' => $job !== null ? $this->mapJob($job) : null,
            'logs' => $this->mapLogs($this->repository->listJobLogs($tenantId, $jobId, $logLimit)),
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function mapJobs(array $rows): array
    {
        return array_map(fn (array $row): array => $this->mapJob($row), $rows);
    }

    /** @return array<string,mixed> */
    private function mapJob(array $row): array
    {
        return [
            'id' => (int)($row['id'] ?? 0),
            'tenant_id' => (int)($row['tenant_id'] ?? 0),
            'bucket_id' => (int)($row['bucket_id'] ?? 0),
            'bucket_name' => (string)($row['bucket_name'] ?? ''),
            'status' => (string)($row['status'] ?? ''),
            'total_s3' => (int)($row['total_s3'] ?? 0),
            'total_db' => (int)($row['total_db'] ?? 0),
            'total_actions' => (int)($row['total_actions'] ?? 0),
            'prefix_present' => trim((string)($row['prefix'] ?? '')) !== '',
            'prefix_exposed' => false,
            'last_message_preview' => $this->preview((string)($row['last_message'] ?? ''), 120),
            'started_at' => (string)($row['started_at'] ?? ''),
            'finished_at' => (string)($row['finished_at'] ?? ''),
            'created_at' => (string)($row['created_at'] ?? ''),
            'updated_at' => (string)($row['updated_at'] ?? ''),
            'logs_count' => (int)($row['logs_count'] ?? 0),
            'mode' => 'read-only',
            'repair_enabled' => false,
            'repair_executed' => false,
            'aws_connection' => false,
            'db_write' => false,
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function mapLogs(array $rows): array
    {
        return array_map(function (array $row): array {
            return [
                'id' => (int)($row['id'] ?? 0),
                'repair_job_id' => (int)($row['repair_job_id'] ?? 0),
                'file_id' => (int)($row['file_id'] ?? 0),
                'action' => (string)($row['action'] ?? ''),
                'old_s3_key_present' => trim((string)($row['old_s3_key'] ?? '')) !== '',
                'old_s3_key_exposed' => false,
                'new_s3_key_present' => trim((string)($row['new_s3_key'] ?? '')) !== '',
                'new_s3_key_exposed' => false,
                'detail_preview' => $this->preview((string)($row['detail'] ?? ''), 120),
                'created_at' => (string)($row['created_at'] ?? ''),
            ];
        }, $rows);
    }

    /** @return array<int,array<string,mixed>> */
    private function mapSummary(array $rows): array
    {
        return array_map(static fn (array $row): array => [
            'status' => (string)($row['status'] ?? ''),
            'total' => (int)($row['total'] ?? 0),
        ], $rows);
    }

    private function preview(string $value, int $max): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? '');
        if ($value === '') {
            return '';
        }

        return mb_strlen($value) > $max ? mb_substr($value, 0, $max) . '…' : $value;
    }
}
