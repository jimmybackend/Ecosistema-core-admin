<?php
declare(strict_types=1);

namespace App\Core\Security;

final readonly class EcosistemaPermissionAuditService
{
    public function __construct(private EcosistemaPermissionAuditRepository $repository){}

    public function buildDashboard(int $tenantId): array
    {
        if ($tenantId <= 0) { return ['modules' => []]; }
        $modules = [];
        foreach ($this->repository->listModulesWithPermissionsSummary($tenantId) as $row) {
            $total = (int) ($row['total_permissions'] ?? 0);
            $used = (int) ($row['used_permissions'] ?? 0);
            $modules[] = [
                'code' => (string) ($row['code'] ?? ''),
                'name' => (string) ($row['name'] ?? ''),
                'description' => (string) ($row['description'] ?? ''),
                'status' => (string) ($row['status'] ?? ''),
                'total_permissions' => $total,
                'used_permissions' => $used,
                'missing_permissions' => max(0, $total - $used),
                'roles_with_permissions' => (int) ($row['roles_with_permissions'] ?? 0),
            ];
        }
        return ['modules' => $modules];
    }

    public function buildModuleDetail(int $tenantId, string $moduleCode): ?array
    {
        if ($tenantId <= 0 || $moduleCode === '') { return null; }
        $module = $this->repository->findModuleByCode($moduleCode);
        if (!is_array($module)) { return null; }
        $roles = $this->repository->listTenantRoles($tenantId);
        $rows = $this->repository->listModulePermissionsByRole($tenantId, (int) ($module['id'] ?? 0));
        $permissions = [];
        foreach ($rows as $row) {
            $permissionId = (int) ($row['permission_id'] ?? 0);
            if ($permissionId <= 0) { continue; }
            if (!isset($permissions[$permissionId])) {
                $permissions[$permissionId] = [
                    'id' => $permissionId,
                    'code' => (string) ($row['permission_code'] ?? ''),
                    'name' => (string) ($row['permission_name'] ?? ''),
                    'description' => (string) ($row['permission_description'] ?? ''),
                    'roles' => [],
                ];
            }
            if ((int) ($row['role_id'] ?? 0) > 0) {
                $permissions[$permissionId]['roles'][] = [
                    'id' => (int) $row['role_id'],
                    'name' => (string) ($row['role_name'] ?? ''),
                    'slug' => (string) ($row['role_slug'] ?? ''),
                ];
            }
        }
        $items = array_values($permissions);
        usort($items, static fn(array $a, array $b): int => strcmp((string) $a['code'], (string) $b['code']));
        $missing = 0; foreach ($items as $i) { if (count((array) ($i['roles'] ?? [])) === 0) { $missing++; } }
        return ['module' => $module, 'roles' => $roles, 'permissions' => $items, 'summary' => ['total_permissions' => count($items), 'missing_permissions' => $missing]];
    }
}
