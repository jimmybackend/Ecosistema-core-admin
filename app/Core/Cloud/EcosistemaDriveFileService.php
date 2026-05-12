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

    /**
     * @return array<string,mixed>|null
     */
    public function getFileDetail(int $tenantId, int $userId, int $id): ?array
    {
        $file = $this->repository->findVisibleById($tenantId, $userId, $id);
        if ($file === null) {
            return null;
        }

        return [
            'id' => isset($file['id']) ? (int)$file['id'] : 0,
            'original_name' => (string)($file['original_name'] ?? ''),
            'mime_type' => (string)($file['mime_type'] ?? ''),
            'extension' => (string)($file['extension'] ?? ''),
            'size_bytes' => isset($file['size_bytes']) ? (int)$file['size_bytes'] : 0,
            'status' => (string)($file['status'] ?? ''),
            'virus_scan_status' => (string)($file['virus_scan_status'] ?? ''),
            'access_type' => (string)($file['access_type'] ?? ''),
            'encrypted' => !empty($file['encrypted']),
            'found_in_s3' => !empty($file['found_in_s3']),
            'origin_module' => (string)($file['origin_module'] ?? ''),
            'origin_table' => (string)($file['origin_table'] ?? ''),
            'origin_id' => isset($file['origin_id']) ? (int)$file['origin_id'] : null,
            'uploaded_by_user_id' => isset($file['uploaded_by_user_id']) ? (int)$file['uploaded_by_user_id'] : null,
            'uploaded_at' => (string)($file['uploaded_at'] ?? ''),
            'updated_at' => (string)($file['updated_at'] ?? ''),
            'deleted_at' => isset($file['deleted_at']) && $file['deleted_at'] !== '' ? (string)$file['deleted_at'] : null,
        ];
    }
}
