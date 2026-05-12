<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class EcosistemaDriveFileService
{
    public function __construct(private EcosistemaDriveFileRepository $repository)
    {
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function listFiles(int $tenantId, int $userId, int $limit = 100): array
    {
        return $this->repository->listMetadataByUser($tenantId, $userId, $limit);
    }
}
