<?php
declare(strict_types=1);
namespace App\Core\Permissions;
final readonly class RolePermissionService{public function __construct(private PermissionRepository $repository){}
public function getRolePermissionsScreen(int $roleId): array {$role=$this->repository->findRole($roleId); if($role===null){return ['role'=>null,'permissions'=>[],'assigned'=>[]];} $tenantId=(int)($role['tenant_id']??0); if($tenantId<=0){return ['role'=>$role,'permissions'=>[],'assigned'=>[]];} return ['role'=>$role,'permissions'=>$this->repository->listActivePermissionsByModule(),'assigned'=>$this->repository->listRolePermissionIds($roleId,$tenantId)];}
public function replaceRolePermissions(int $roleId,array $permissionIds): string {$role=$this->repository->findRole($roleId); if($role===null){return 'Rol no encontrado.';} $clean=[]; foreach($permissionIds as $pid){$v=(int)$pid; if($v>0){$clean[]=$v;}} $clean=array_values(array_unique($clean)); if($clean!==[] && $this->repository->countActivePermissionsByIds($clean)!==count($clean)){return 'No se pudo guardar el permiso.';} $tenantId=(int)($role['tenant_id']??0); if($tenantId<=0){return 'No se pudo guardar el permiso.';} $this->repository->replaceRolePermissions($roleId,$tenantId,$clean); return 'Permisos del rol actualizados correctamente.';}
}
