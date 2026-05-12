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

        return array_map(static function (array $row): array {
            $bucket = [
                'id' => isset($row['id']) ? (int) $row['id'] : 0,
                'name' => (string) ($row['name'] ?? ''),
                'provider' => (string) ($row['provider'] ?? ''),
                'region' => (string) ($row['region'] ?? ''),
                'is_default' => !empty($row['is_default']),
                'created_at' => (string) ($row['created_at'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
            ];

            if (array_key_exists('status', $row)) {
                $bucket['status'] = (string) ($row['status'] ?? '');
            }

            return $bucket;
        }, $rows);
    }
}
