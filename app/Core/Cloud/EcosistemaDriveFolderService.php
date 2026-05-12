<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class EcosistemaDriveFolderService
{
    public function __construct(private EcosistemaDriveFolderRepository $repository)
    {
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function listFolders(int $tenantId, int $userId, int $limit = 100): array
    {
        $folders = $this->repository->listMetadataByUser($tenantId, $userId, $limit);

        return array_map(static fn (array $folder): array => [
            'id' => isset($folder['id']) ? (int)$folder['id'] : 0,
            'name' => (string)($folder['name'] ?? ''),
            'folder_type' => (string)($folder['folder_type'] ?? ''),
            'access_type' => (string)($folder['access_type'] ?? ''),
            'found_in_s3' => !empty($folder['found_in_s3']),
            'is_system' => !empty($folder['is_system']),
            'parent_folder_id' => isset($folder['parent_folder_id']) ? (int)$folder['parent_folder_id'] : null,
            'root_id' => isset($folder['root_id']) ? (int)$folder['root_id'] : null,
            'bucket_id' => isset($folder['bucket_id']) ? (int)$folder['bucket_id'] : null,
            'created_at' => (string)($folder['created_at'] ?? ''),
            'updated_at' => (string)($folder['updated_at'] ?? ''),
            'deleted_at' => isset($folder['deleted_at']) && $folder['deleted_at'] !== '' ? (string)$folder['deleted_at'] : null,
        ], $folders);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getFolderDetail(int $tenantId, int $userId, int $id): ?array
    {
        $folder = $this->repository->findVisibleById($tenantId, $userId, $id);
        if ($folder === null) {
            return null;
        }

        return [
            'id' => isset($folder['id']) ? (int)$folder['id'] : 0,
            'name' => (string)($folder['name'] ?? ''),
            'folder_type' => (string)($folder['folder_type'] ?? ''),
            'access_type' => (string)($folder['access_type'] ?? ''),
            'found_in_s3' => !empty($folder['found_in_s3']),
            'is_system' => !empty($folder['is_system']),
            'parent_folder_id' => isset($folder['parent_folder_id']) ? (int)$folder['parent_folder_id'] : null,
            'root_id' => isset($folder['root_id']) ? (int)$folder['root_id'] : null,
            'bucket_id' => isset($folder['bucket_id']) ? (int)$folder['bucket_id'] : null,
            'created_at' => (string)($folder['created_at'] ?? ''),
            'updated_at' => (string)($folder['updated_at'] ?? ''),
            'deleted_at' => isset($folder['deleted_at']) && $folder['deleted_at'] !== '' ? (string)$folder['deleted_at'] : null,
        ];
    }
}
