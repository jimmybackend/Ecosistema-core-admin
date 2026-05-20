<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use PDO;
use Throwable;

final readonly class UserCloudRootProvisioner
{
    public function __construct(private PDO $pdo, private CloudStorageService $storage, private array $config)
    {
    }

    public function provisionForUser(int $tenantId, int $userId): array
    {
        $basePrefix = 'users';
        $rootPrefix = CloudPath::normalizeRootPrefix($userId);

        $bucketId = $this->resolveOrCreateBucket($tenantId, $basePrefix);
        if ($bucketId <= 0) {
            return ['ok' => false, 'message' => 'No se pudo preparar bucket Cloud.'];
        }

        $rootId = $this->resolveOrCreateRoot($tenantId, $userId, $bucketId, $rootPrefix);
        if ($rootId <= 0) {
            return ['ok' => false, 'message' => 'No se pudo preparar raíz de usuario en Cloud.'];
        }

        $definitions = $this->folderDefinitions($rootPrefix);
        $idsByPrefix = [];
        foreach ($definitions as $folder) {
            $this->storage->createPrefix((string) $folder['prefix']);
            $id = $this->resolveOrCreateFolder($tenantId, $userId, $bucketId, $rootId, $folder, $idsByPrefix);
            if ($id > 0) { $idsByPrefix[(string) $folder['prefix']] = $id; }
        }

        return ['ok' => true, 'bucket_id' => $bucketId, 'root_id' => $rootId, 'root_prefix' => $rootPrefix];
    }

    private function resolveOrCreateBucket(int $tenantId, string $basePrefix): int
    {
        $bucket = trim((string) (($this->config['cloud']['s3']['bucket'] ?? '')));
        $region = trim((string) (($this->config['cloud']['s3']['region'] ?? 'us-east-1')));
        if ($bucket === '') { return 0; }

        $stmt = $this->pdo->prepare('SELECT id FROM cloud_buckets WHERE tenant_id = :tenant_id AND bucket_name = :bucket_name LIMIT 1');
        $stmt->execute([':tenant_id' => $tenantId, ':bucket_name' => $bucket]);
        $id = (int) ($stmt->fetchColumn() ?: 0);
        if ($id > 0) { return $id; }

        $insert = $this->pdo->prepare('INSERT INTO cloud_buckets (tenant_id, bucket_name, provider, region, base_prefix, is_default, status, config_json, created_at, updated_at) VALUES (:tenant_id, :bucket_name, :provider, :region, :base_prefix, :is_default, :status, :config_json, NOW(), NOW())');
        $ok = $insert->execute([':tenant_id' => $tenantId, ':bucket_name' => $bucket, ':provider' => 'aws_s3', ':region' => $region, ':base_prefix' => $basePrefix, ':is_default' => 1, ':status' => 'active', ':config_json' => json_encode(['managed_by' => 'UserCloudRootProvisioner'], JSON_UNESCAPED_UNICODE)]);

        return $ok ? (int) $this->pdo->lastInsertId() : 0;
    }

    private function resolveOrCreateRoot(int $tenantId, int $userId, int $bucketId, string $rootPrefix): int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM cloud_user_roots WHERE tenant_id = :tenant_id AND user_id = :user_id AND bucket_id = :bucket_id AND root_prefix = :root_prefix LIMIT 1');
        $stmt->execute([':tenant_id' => $tenantId, ':user_id' => $userId, ':bucket_id' => $bucketId, ':root_prefix' => $rootPrefix]);
        $id = (int) ($stmt->fetchColumn() ?: 0);
        if ($id > 0) { return $id; }

        $insert = $this->pdo->prepare('INSERT INTO cloud_user_roots (tenant_id, user_id, bucket_id, root_prefix, display_name, status, created_at, updated_at) VALUES (:tenant_id, :user_id, :bucket_id, :root_prefix, :display_name, :status, NOW(), NOW())');
        $ok = $insert->execute([':tenant_id' => $tenantId, ':user_id' => $userId, ':bucket_id' => $bucketId, ':root_prefix' => $rootPrefix, ':display_name' => 'Usuario ' . $userId, ':status' => 'active']);

        return $ok ? (int) $this->pdo->lastInsertId() : 0;
    }

    private function resolveOrCreateFolder(int $tenantId, int $userId, int $bucketId, int $rootId, array $folder, array $idsByPrefix): int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM cloud_folders WHERE tenant_id = :tenant_id AND user_id = :user_id AND bucket_id = :bucket_id AND root_id = :root_id AND prefix = :prefix AND is_deleted = 0 LIMIT 1');
        $stmt->execute([':tenant_id' => $tenantId, ':user_id' => $userId, ':bucket_id' => $bucketId, ':root_id' => $rootId, ':prefix' => $folder['prefix']]);
        $id = (int) ($stmt->fetchColumn() ?: 0);
        if ($id > 0) { return $id; }

        $parentId = null;
        $parentPrefix = (string) ($folder['parent_prefix'] ?? '');
        if ($parentPrefix !== '' && isset($idsByPrefix[$parentPrefix])) { $parentId = (int) $idsByPrefix[$parentPrefix]; }

        $insert = $this->pdo->prepare('INSERT INTO cloud_folders (tenant_id, user_id, bucket_id, root_id, parent_folder_id, name, prefix, folder_type, access_type, found_in_s3, is_system, is_deleted, created_at, updated_at) VALUES (:tenant_id,:user_id,:bucket_id,:root_id,:parent_folder_id,:name,:prefix,:folder_type,:access_type,:found_in_s3,:is_system,:is_deleted,NOW(),NOW())');
        $ok = $insert->execute([':tenant_id' => $tenantId, ':user_id' => $userId, ':bucket_id' => $bucketId, ':root_id' => $rootId, ':parent_folder_id' => $parentId, ':name' => $folder['name'], ':prefix' => $folder['prefix'], ':folder_type' => $folder['folder_type'], ':access_type' => 'normal', ':found_in_s3' => 1, ':is_system' => 1, ':is_deleted' => 0]);

        return $ok ? (int) $this->pdo->lastInsertId() : 0;
    }

    private function folderDefinitions(string $rootPrefix): array
    {
        return [
            ['name' => 'uploads', 'prefix' => CloudPath::joinS3Prefix($rootPrefix, 'uploads'), 'folder_type' => 'custom', 'parent_prefix' => null],
            ['name' => 'mail', 'prefix' => CloudPath::joinS3Prefix($rootPrefix, 'mail'), 'folder_type' => 'mail', 'parent_prefix' => null],
            ['name' => 'inbound', 'prefix' => CloudPath::joinS3Prefix($rootPrefix, 'mail', 'inbound'), 'folder_type' => 'mail', 'parent_prefix' => CloudPath::joinS3Prefix($rootPrefix, 'mail')],
            ['name' => 'attachments', 'prefix' => CloudPath::joinS3Prefix($rootPrefix, 'mail', 'inbound', 'attachments'), 'folder_type' => 'mail', 'parent_prefix' => CloudPath::joinS3Prefix($rootPrefix, 'mail', 'inbound')],
            ['name' => 'outbound', 'prefix' => CloudPath::joinS3Prefix($rootPrefix, 'mail', 'outbound'), 'folder_type' => 'mail', 'parent_prefix' => CloudPath::joinS3Prefix($rootPrefix, 'mail')],
            ['name' => 'attachments', 'prefix' => CloudPath::joinS3Prefix($rootPrefix, 'mail', 'outbound', 'attachments'), 'folder_type' => 'mail', 'parent_prefix' => CloudPath::joinS3Prefix($rootPrefix, 'mail', 'outbound')],
            ['name' => 'products', 'prefix' => CloudPath::joinS3Prefix($rootPrefix, 'products'), 'folder_type' => 'custom', 'parent_prefix' => null],
            ['name' => 'campaigns', 'prefix' => CloudPath::joinS3Prefix($rootPrefix, 'campaigns'), 'folder_type' => 'custom', 'parent_prefix' => null],
            ['name' => 'generated', 'prefix' => CloudPath::joinS3Prefix($rootPrefix, 'generated'), 'folder_type' => 'custom', 'parent_prefix' => null],
        ];
    }

}
