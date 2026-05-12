<?php

declare(strict_types=1);

namespace App\Core\Cloud;

final readonly class EcosistemaDriveRootService
{
    public function __construct(
        private EcosistemaDriveRootRepository $repository,
        private EcosistemaDriveAccessPolicy $policy,
    ) {
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getUserRootSummary(int $tenantId, int $userId): ?array
    {
        $root = $this->repository->findForUser($tenantId, $userId);
        if ($root === null || !$this->policy->canViewRootMetadata($root, $tenantId, $userId)) {
            return null;
        }

        return [
            'id' => isset($root['id']) ? (int)$root['id'] : 0,
            'bucket_id' => isset($root['bucket_id']) ? (int)$root['bucket_id'] : null,
            'display_name' => (string)($root['display_name'] ?? ''),
            'quota_bytes' => isset($root['quota_bytes']) ? (int)$root['quota_bytes'] : 0,
            'used_bytes' => isset($root['used_bytes']) ? (int)$root['used_bytes'] : 0,
            'file_count' => isset($root['file_count']) ? (int)$root['file_count'] : 0,
            'status' => (string)($root['status'] ?? ''),
            'created_at' => (string)($root['created_at'] ?? ''),
            'updated_at' => (string)($root['updated_at'] ?? ''),
        ];
    }
}
