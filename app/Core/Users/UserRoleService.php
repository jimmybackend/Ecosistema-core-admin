<?php

declare(strict_types=1);

namespace App\Core\Users;

final readonly class UserRoleService
{
    public function __construct(private UserRoleRepository $repository)
    {
    }

    /** @return array<string,mixed>|null */
    public function findUser(int $userId): ?array
    {
        if ($userId <= 0) {
            return null;
        }

        return $this->repository->findUser($userId);
    }

    /** @return array<int,array<string,mixed>> */
    public function listRolesForTenant(int $tenantId): array
    {
        return $tenantId > 0 ? $this->repository->listRolesForTenant($tenantId) : [];
    }

    /** @return array<int,int> */
    public function listAssignedRoleIds(int $tenantId, int $userId): array
    {
        if ($tenantId <= 0 || $userId <= 0) {
            return [];
        }

        return $this->repository->listAssignedRoleIds($tenantId, $userId);
    }

    public function replaceUserRoles(int $tenantId, int $userId, mixed $roleIdsInput, ?int $assignedByUserId): string
    {
        if ($tenantId <= 0 || $userId <= 0) {
            return 'Usuario no encontrado.';
        }

        if (!is_array($roleIdsInput)) {
            return 'Formato de roles inválido.';
        }

        $normalized = [];
        foreach ($roleIdsInput as $roleId) {
            $value = (int) $roleId;
            if ($value > 0) {
                $normalized[$value] = $value;
            }
        }

        $requestedRoleIds = array_values($normalized);
        $validRoleIds = $this->repository->filterValidRoleIdsForTenant($tenantId, $requestedRoleIds);
        $validRoleIds = array_values(array_unique(array_map(static fn (int $id): int => $id, $validRoleIds)));

        $this->repository->replaceUserRoles($tenantId, $userId, $validRoleIds, $assignedByUserId);

        return 'Roles del usuario actualizados correctamente.';
    }
}
