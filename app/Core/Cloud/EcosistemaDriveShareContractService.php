<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class EcosistemaDriveShareContractService
{
    public function __construct(
        private EcosistemaDriveFileService $fileService,
        private EcosistemaDriveShareContract $shareContract,
    ) {
    }

    /**
     * @return array<string,mixed>|null
     */
    public function describeForFile(int $tenantId, int $userId, int $fileId): ?array
    {
        if ($fileId <= 0) {
            return null;
        }

        $file = $this->fileService->getFileDetail($tenantId, $userId, $fileId);
        if ($file === null) {
            return null;
        }

        return [
            'file' => [
                'id' => (int)($file['id'] ?? 0),
                'original_name' => (string)($file['original_name'] ?? ''),
                'mime_type' => (string)($file['mime_type'] ?? ''),
                'size_bytes' => (int)($file['size_bytes'] ?? 0),
                'status' => (string)($file['status'] ?? ''),
            ],
            'contract' => $this->shareContract->describe(),
        ];
    }
}
