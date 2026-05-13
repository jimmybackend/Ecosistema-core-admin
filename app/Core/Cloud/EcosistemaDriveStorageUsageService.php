<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class EcosistemaDriveStorageUsageService
{
    public function __construct(private EcosistemaDriveStorageUsageRepository $repository)
    {
    }

    /** @return array<string,mixed> */
    public function buildUsage(int $tenantId): array
    {
        $summary = $this->repository->summarizeTenantFiles($tenantId);
        $byBucket = $this->repository->summarizeByBucket($tenantId);
        $byUser = $this->repository->summarizeByUser($tenantId, 100);
        $byExtension = $this->repository->summarizeByExtension($tenantId, 50);
        $dailyUsage = $this->repository->summarizeDailyUsage($tenantId, 30);

        $decorate = fn (array $row): array => $row + ['total_human' => $this->humanizeBytes((int) ($row['total_bytes'] ?? 0))];

        return [
            'total_files' => (int) ($summary['total_files'] ?? 0),
            'total_bytes' => (int) ($summary['total_bytes'] ?? 0),
            'total_human' => $this->humanizeBytes((int) ($summary['total_bytes'] ?? 0)),
            'active_files' => (int) ($summary['active_files'] ?? 0),
            'archived_files' => (int) ($summary['archived_files'] ?? 0),
            'missing_files' => (int) ($summary['missing_files'] ?? 0),
            'deleted_files' => (int) ($summary['deleted_files'] ?? 0),
            'found_in_s3_count' => (int) ($summary['found_in_s3_count'] ?? 0),
            'not_found_in_s3_count' => (int) ($summary['not_found_in_s3_count'] ?? 0),
            'by_bucket' => array_map($decorate, $byBucket),
            'by_user' => array_map($decorate, $byUser),
            'by_extension' => array_map($decorate, $byExtension),
            'daily_usage' => array_map(function (array $row): array {
                return $row + [
                    'total_human' => $this->humanizeBytes((int) ($row['total_bytes'] ?? 0)),
                    'mail_attachment_human' => $this->humanizeBytes((int) ($row['mail_attachment_bytes'] ?? 0)),
                    'cloud_document_human' => $this->humanizeBytes((int) ($row['cloud_document_bytes'] ?? 0)),
                    'other_human' => $this->humanizeBytes((int) ($row['other_bytes'] ?? 0)),
                ];
            }, $dailyUsage),
            'mode' => 'read-only',
            'db_write' => false,
            'aws_connection' => false,
            'storage_scan' => false,
            's3_scan' => false,
        ];
    }

    private function humanizeBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $value = max(0, (float) $bytes);
        $unit = 0;
        while ($value >= 1024 && $unit < count($units) - 1) {
            $value /= 1024;
            $unit++;
        }

        return number_format($value, $unit === 0 ? 0 : 2, '.', ',') . ' ' . $units[$unit];
    }
}
