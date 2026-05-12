<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class EcosistemaDriveRootService
{
    public function __construct(private EcosistemaDriveRootRepository $repository)
    {
    }

    /** @return array<int,array<string,mixed>> */
    public function listRootSummaries(int $tenantId, int $userId): array
    {
        $rows = $this->repository->listVisible($tenantId, $userId, 20);
        return array_map(static fn(array $r): array => [
            'id' => isset($r['id']) ? (int)$r['id'] : 0,
            'bucket_id' => isset($r['bucket_id']) ? (int)$r['bucket_id'] : null,
            'display_name' => (string)($r['display_name'] ?? ''),
            'quota_bytes' => isset($r['quota_bytes']) ? (int)$r['quota_bytes'] : 0,
            'used_bytes' => isset($r['used_bytes']) ? (int)$r['used_bytes'] : 0,
            'file_count' => isset($r['file_count']) ? (int)$r['file_count'] : 0,
            'status' => (string)($r['status'] ?? ''),
            'created_at' => (string)($r['created_at'] ?? ''),
            'updated_at' => (string)($r['updated_at'] ?? ''),
        ], $rows);
    }
}
