<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class EcosistemaDriveFolderService
{
    public function __construct(
        private EcosistemaDriveFolderRepository $repository,
        private EcosistemaDriveFileRepository $fileRepository,
        private EcosistemaDriveAccessPolicy $policy,
    )
    {
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function listFolders(int $tenantId, int $userId, int $limit = 100): array
    {
        $folders = array_values(array_filter(
            $this->repository->listMetadataByUser($tenantId, $userId, $limit),
            fn (array $folder): bool => $this->policy->canViewFolderMetadata($folder, $tenantId, $userId),
        ));

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
        if ($folder === null || !$this->policy->canViewFolderMetadata($folder, $tenantId, $userId)) {
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

    /**
     * @return array<string,mixed>
     */
    public function getFolderBrowser(int $tenantId, int $userId, ?int $folderId): array
    {
        $limit = 100;
        $currentFolder = null;
        $parentFolder = null;

        if ($folderId !== null) {
            $currentFolder = $this->getFolderDetail($tenantId, $userId, $folderId);
            if ($currentFolder === null) {
                throw new \RuntimeException('Folder not found.');
            }

            $parentId = $currentFolder['parent_folder_id'] ?? null;
            if (is_int($parentId) && $parentId > 0) {
                $parentFolder = $this->getFolderDetail($tenantId, $userId, $parentId);
            }
        }

        $children = $this->repository->listChildren($tenantId, $userId, $folderId, $limit);
        $files = $this->fileRepository->listByFolder($tenantId, $userId, $folderId, $limit);

        return [
            'current_folder' => $currentFolder,
            'parent_folder' => $parentFolder,
            'child_folders' => array_map(static fn (array $folder): array => [
                'id' => isset($folder['id']) ? (int)$folder['id'] : 0,
                'name' => (string)($folder['name'] ?? ''),
                'folder_type' => (string)($folder['folder_type'] ?? ''),
                'access_type' => (string)($folder['access_type'] ?? ''),
                'found_in_s3' => !empty($folder['found_in_s3']),
                'is_system' => !empty($folder['is_system']),
                'created_at' => (string)($folder['created_at'] ?? ''),
                'updated_at' => (string)($folder['updated_at'] ?? ''),
            ], $children),
            'files' => array_map(static fn (array $file): array => [
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
                'uploaded_at' => (string)($file['uploaded_at'] ?? ''),
                'updated_at' => (string)($file['updated_at'] ?? ''),
            ], $files),
            'breadcrumbs' => $currentFolder !== null ? [['id' => $currentFolder['id'], 'name' => $currentFolder['name']]] : [],
            'limits' => ['max_items' => $limit],
            'read_only' => true,
        ];
    }
}
