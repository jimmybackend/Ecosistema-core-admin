<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final class EcosistemaDriveAccessPolicy
{
    public function canViewDriveMetadata(array $sessionContext): bool
    {
        $tenantId = isset($sessionContext['tenant_id']) ? (int) $sessionContext['tenant_id'] : 0;
        $userId = isset($sessionContext['user_id']) ? (int) $sessionContext['user_id'] : 0;

        if ($tenantId <= 0 || $userId <= 0) {
            return false;
        }

        $permissions = $sessionContext['permissions'] ?? [];
        if (!is_array($permissions)) {
            return false;
        }

        return in_array('cloud.view', array_map('strval', $permissions), true);
    }

    public function canViewFileMetadata(array $file, int $tenantId, int $userId): bool
    {
        return $this->matchesTenant($file, $tenantId) && $this->matchesOptionalUser($file, $userId);
    }

    public function canViewFolderMetadata(array $folder, int $tenantId, int $userId): bool
    {
        return $this->matchesTenant($folder, $tenantId) && $this->matchesOptionalUser($folder, $userId);
    }

    public function canViewRootMetadata(array $root, int $tenantId, int $userId): bool
    {
        return $this->matchesTenant($root, $tenantId) && $this->matchesOptionalUser($root, $userId);
    }

    public function canViewBucketMetadata(array $bucket, int $tenantId): bool
    {
        return $this->matchesTenant($bucket, $tenantId);
    }

    /** @return array<string,mixed> */
    public function describeReadOnlyPolicy(): array
    {
        return [
            'mode' => 'read-only/contract/dry-run',
            'required_permission' => 'cloud.view',
            'future_admin_permission' => 'cloud.manage',
            'tenant_boundary' => 'tenant_id obligatorio para todos los recursos Drive.',
            'user_boundary' => 'user_id obligatorio en recursos del usuario (files/folders/root).',
            'access_type_rule' => 'access_type no habilita acceso público en este PR.',
            'blocked_operations' => [
                'uploads' => true,
                'downloads' => true,
                'signed_urls' => true,
                'aws_s3_real' => true,
                'edition_delete' => true,
            ],
        ];
    }

    private function matchesTenant(array $resource, int $tenantId): bool
    {
        return isset($resource['tenant_id']) && (int) $resource['tenant_id'] === $tenantId;
    }

    private function matchesOptionalUser(array $resource, int $userId): bool
    {
        if (!array_key_exists('user_id', $resource)) {
            return true;
        }

        return (int) $resource['user_id'] === $userId;
    }
}
