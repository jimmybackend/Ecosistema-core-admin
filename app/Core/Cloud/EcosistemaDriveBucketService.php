<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class EcosistemaDriveBucketService
{
    public function __construct(private EcosistemaDriveBucketRepository $repository)
    {
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function listBucketSummaries(int $tenantId): array
    {
        $rows = $this->repository->listVisible($tenantId, 100);

        return array_map(static fn (array $row): array => [
            'id' => isset($row['id']) ? (int) $row['id'] : null,
            'name' => array_key_exists('name', $row) ? (string) ($row['name'] ?? '') : null,
            'provider' => array_key_exists('provider', $row) ? (string) ($row['provider'] ?? '') : null,
            'region' => array_key_exists('region', $row) ? (string) ($row['region'] ?? '') : null,
            'status' => array_key_exists('status', $row) ? (string) ($row['status'] ?? '') : null,
            'is_default' => array_key_exists('is_default', $row) ? (bool) $row['is_default'] : null,
            'created_at' => array_key_exists('created_at', $row) ? (string) ($row['created_at'] ?? '') : null,
            'updated_at' => array_key_exists('updated_at', $row) ? (string) ($row['updated_at'] ?? '') : null,
        ], $rows);
    }
}
