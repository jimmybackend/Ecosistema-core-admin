<?php

declare(strict_types=1);

namespace App\Core\Permissions;

use PDO;

final class PermissionRepository
{
    /** @var array<string,bool> */
    private array $columnCache = [];

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function listRecent(int $limit = 100): array
    {
        $stmt = $this->pdo->prepare('SELECT p.id, p.module_id, p.code, p.name, p.description, p.created_at, m.name AS module_name, m.code AS module_code FROM core_permissions p LEFT JOIN core_modules m ON m.id = p.module_id ORDER BY p.id DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(fn (array $row): array => $this->hydratePermissionRow($row), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    public function listModules(): array
    {
        $stmt = $this->pdo->query("SELECT id, code, name, status FROM core_modules WHERE status = 'active' ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function moduleExists(int $id): bool { $stmt = $this->pdo->prepare('SELECT id FROM core_modules WHERE id=:id LIMIT 1'); $stmt->execute([':id' => $id]); return (bool) $stmt->fetchColumn(); }
    public function findPermission(int $id): ?array { $stmt = $this->pdo->prepare('SELECT id, module_id, code, name, description, created_at FROM core_permissions WHERE id=:id LIMIT 1'); $stmt->execute([':id'=>$id]); $r=$stmt->fetch(PDO::FETCH_ASSOC); return is_array($r)?$this->hydratePermissionRow($r):null; }
    public function create(array $d): bool { $stmt = $this->pdo->prepare('INSERT INTO core_permissions (module_id, code, name, description, created_at) VALUES (:module_id,:code,:name,:description,NOW())'); return $stmt->execute($d); }
    public function update(int $id,array $d): bool { $d[':id']=$id; $stmt=$this->pdo->prepare('UPDATE core_permissions SET module_id=:module_id, code=:code, name=:name, description=:description WHERE id=:id'); return $stmt->execute($d); }
    public function updateStatus(int $id,string $status): bool { return false; }
    public function findRole(int $id): ?array { $stmt=$this->pdo->prepare('SELECT id, tenant_id, slug, name, description, is_system, created_at, updated_at FROM core_roles WHERE id=:id LIMIT 1'); $stmt->execute([':id'=>$id]); $r=$stmt->fetch(PDO::FETCH_ASSOC); if(!is_array($r)){return null;} $r['code']=$r['slug']??''; $r['status']='active'; return $r; }
    public function listActivePermissionsByModule(): array { $stmt=$this->pdo->query('SELECT p.id, p.module_id, p.code, p.name, p.description, p.created_at, m.name AS module_name, m.code AS module_code FROM core_permissions p LEFT JOIN core_modules m ON m.id = p.module_id ORDER BY m.name ASC, p.id ASC'); return array_map(fn(array $row): array => $this->hydratePermissionRow($row), $stmt->fetchAll(PDO::FETCH_ASSOC)?:[]); }
    public function listRolePermissionIds(int $roleId, int $tenantId): array { $stmt=$this->pdo->prepare('SELECT permission_id FROM core_role_permissions WHERE role_id=:role_id AND tenant_id=:tenant_id');$stmt->execute([':role_id'=>$roleId, ':tenant_id'=>$tenantId]);return array_map(static fn($v):int=>(int)$v,$stmt->fetchAll(PDO::FETCH_COLUMN)?:[]); }
    public function countActivePermissionsByIds(array $ids): int { if($ids===[]){return 0;} $pl=implode(',',array_fill(0,count($ids),'?')); $stmt=$this->pdo->prepare("SELECT COUNT(*) FROM core_permissions WHERE id IN ($pl)"); $stmt->execute($ids); return (int)$stmt->fetchColumn(); }
    public function replaceRolePermissions(int $roleId,int $tenantId,array $ids): void { $this->pdo->beginTransaction(); try{ $this->pdo->prepare('DELETE FROM core_role_permissions WHERE role_id=:role_id AND tenant_id=:tenant_id')->execute([':role_id'=>$roleId, ':tenant_id'=>$tenantId]); if($ids!==[]){ $ins=$this->pdo->prepare('INSERT INTO core_role_permissions (tenant_id, role_id, permission_id, created_at) VALUES (:tenant_id, :role_id, :permission_id, NOW())'); foreach($ids as $pid){$ins->execute([':tenant_id'=>$tenantId, ':role_id'=>$roleId,':permission_id'=>$pid]);}} $this->pdo->commit(); }catch(\Throwable $e){ $this->pdo->rollBack(); throw $e; } }

    private function hydratePermissionRow(array $row): array
    {
        $row['status'] = 'active';
        $row['action'] = '';
        $row['resource'] = '';
        return $row;
    }
}
