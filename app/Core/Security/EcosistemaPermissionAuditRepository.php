<?php
declare(strict_types=1);

namespace App\Core\Security;

use PDO;

final readonly class EcosistemaPermissionAuditRepository
{
    public function __construct(private PDO $pdo){}

    public function listModulesWithPermissionsSummary(int $tenantId): array
    {
        $sql = 'SELECT m.id, m.code, m.name, m.description, m.status,
                       COUNT(DISTINCT p.id) AS total_permissions,
                       COUNT(DISTINCT CASE WHEN rp.role_id IS NOT NULL THEN p.id END) AS used_permissions,
                       COUNT(DISTINCT rp.role_id) AS roles_with_permissions
                FROM core_modules m
                LEFT JOIN core_permissions p ON p.module_id = m.id
                LEFT JOIN core_role_permissions rp ON rp.permission_id = p.id AND rp.tenant_id = :tenant_id
                GROUP BY m.id, m.code, m.name, m.description, m.status
                ORDER BY m.name ASC';
        $s = $this->pdo->prepare($sql);
        $s->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $s->execute();
        return $s->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findModuleByCode(string $code): ?array
    {
        $s = $this->pdo->prepare('SELECT id, code, name, description, status FROM core_modules WHERE code = :code LIMIT 1');
        $s->execute([':code' => $code]);
        $row = $s->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function listModulePermissionsByRole(int $tenantId, int $moduleId): array
    {
        $sql = 'SELECT p.id AS permission_id, p.code AS permission_code, p.name AS permission_name, p.description AS permission_description,
                       r.id AS role_id, r.name AS role_name, r.slug AS role_slug
                FROM core_permissions p
                LEFT JOIN core_role_permissions rp ON rp.permission_id = p.id AND rp.tenant_id = :tenant_id
                LEFT JOIN core_roles r ON r.id = rp.role_id AND r.tenant_id = :tenant_id
                WHERE p.module_id = :module_id
                ORDER BY p.code ASC, r.name ASC';
        $s = $this->pdo->prepare($sql);
        $s->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $s->bindValue(':module_id', $moduleId, PDO::PARAM_INT);
        $s->execute();
        return $s->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listTenantRoles(int $tenantId): array
    {
        $s = $this->pdo->prepare('SELECT id, name, slug, is_system FROM core_roles WHERE tenant_id = :tenant_id ORDER BY name ASC');
        $s->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $s->execute();
        return $s->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
