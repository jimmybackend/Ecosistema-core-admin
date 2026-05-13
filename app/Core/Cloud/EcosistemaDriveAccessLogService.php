<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class EcosistemaDriveAccessLogService
{
    public function __construct(private EcosistemaDriveAccessLogRepository $repository)
    {
    }

    /** @return array{summary: array<int,array<string,mixed>>, logs: array<int,array<string,mixed>>} */
    public function listRecentForTenant(int $tenantId, int $limit = 100): array
    {
        return [
            'summary' => $this->mapSummary($this->repository->summarizeByAction($tenantId)),
            'logs' => $this->mapLogs($this->repository->listRecentForTenant($tenantId, $limit)),
        ];
    }

    /** @return array{summary: array<int,array<string,mixed>>, logs: array<int,array<string,mixed>>} */
    public function listForFile(int $tenantId, int $fileId, int $limit = 100): array
    {
        return [
            'summary' => $this->mapSummary($this->repository->summarizeByAction($tenantId)),
            'logs' => $this->mapLogs($this->repository->listForFile($tenantId, $fileId, $limit)),
        ];
    }

    /** @return array<int,array<string,mixed>> */
    private function mapLogs(array $rows): array
    {
        return array_map(function (array $row): array {
            $action = (string)($row['action'] ?? '');
            $ip = trim((string)($row['ip_address'] ?? ''));
            $ua = trim((string)($row['user_agent'] ?? ''));
            $country = trim((string)($row['country'] ?? ''));
            $region = trim((string)($row['region'] ?? ''));
            $city = trim((string)($row['city'] ?? ''));

            return [
                'id' => (int)($row['id'] ?? 0),
                'file_id' => (int)($row['file_id'] ?? 0),
                'file_original_name' => (string)($row['file_original_name'] ?? ''),
                'user_id' => (int)($row['user_id'] ?? 0),
                'user_email' => (string)($row['user_email'] ?? ''),
                'action' => $action,
                'action_label' => strtoupper($action),
                'ip_address_preview' => $ip !== '' ? 'present' : 'not-present',
                'ip_address_present' => $ip !== '',
                'user_agent_preview' => $ua === '' ? 'n/a' : mb_substr($ua, 0, 64) . (mb_strlen($ua) > 64 ? '…' : ''),
                'geo_present' => $country !== '' || $region !== '' || $city !== '',
                'country' => $country,
                'region' => $region,
                'city' => $city,
                'metadata_present' => trim((string)($row['metadata_json'] ?? '')) !== '',
                'metadata_exposed' => false,
                'created_at' => (string)($row['created_at'] ?? ''),
                'mode' => 'read-only',
                'db_write' => false,
                'aws_connection' => false,
            ];
        }, $rows);
    }

    /** @return array<int,array<string,mixed>> */
    private function mapSummary(array $rows): array
    {
        return array_map(static fn (array $row): array => [
            'action' => (string)($row['action'] ?? ''),
            'total' => (int)($row['total'] ?? 0),
        ], $rows);
    }
}
