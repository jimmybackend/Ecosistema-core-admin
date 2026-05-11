<?php

declare(strict_types=1);

namespace App\Core\Auth;

final class AuthorizationService
{
    public function __construct(private readonly AuthorizationRepository $repository)
    {
    }

    public function can(int $userId, int $tenantId, string $permissionCode): bool
    {
        if ($userId <= 0 || $tenantId <= 0 || $permissionCode === '') {
            return false;
        }

        return $this->repository->userHasPermission($userId, $tenantId, $permissionCode);
    }
}
