<?php

declare(strict_types=1);

namespace App\Core\Roles;

use PDOException;

final readonly class RoleService
{
    public const ALLOWED_SCOPES=['global','tenant','module']; public const ALLOWED_STATUSES=['active','inactive','deleted'];
    public function __construct(private RoleRepository $repository){}
    public function listRoles(): array { return $this->repository->listRecent(100);} public function listTenants(): array { return $this->repository->listTenants(); } public function findRole(int $id): ?array { return $this->repository->findById($id); }
    public function createRole(array $input): string { $data=$this->normalizeCommon($input); if($data===null){return 'No se pudo guardar el rol.';} try{return $this->repository->create($data)?'Rol creado correctamente.':'No se pudo guardar el rol.';}catch(PDOException $e){return $this->isDuplicate($e)?'Ya existe un rol con ese código en este alcance.':'No se pudo guardar el rol.';}}
    public function updateRole(int $id,array $input): string { $role=$this->repository->findById($id); if($role===null){return 'Rol no encontrado.';} try{ if((int)($role['is_system']??0)===1){$data=$this->normalizeSystemRoleUpdate($input); if($data===null){return 'No se pudo guardar el rol.';} return $this->repository->updateNameDescriptionStatus($id,$data)?'Rol actualizado correctamente.':'No se pudo guardar el rol.';} $data=$this->normalizeCommon($input); if($data===null){return 'No se pudo guardar el rol.';} return $this->repository->update($id,$data)?'Rol actualizado correctamente.':'No se pudo guardar el rol.'; }catch(PDOException $e){return $this->isDuplicate($e)?'Ya existe un rol con ese código en este alcance.':'No se pudo guardar el rol.';}}
    public function changeStatus(int $id,string $status): string { $role=$this->repository->findById($id); if($role===null){return 'Rol no encontrado.';} $value=trim($status); if(!in_array($value,self::ALLOWED_STATUSES,true)){return 'No se pudo guardar el rol.';} if((int)($role['is_system']??0)===1 && $value==='deleted'){return 'No se puede eliminar lógicamente un rol de sistema desde esta pantalla.';} return $this->repository->updateStatus($id,$value)?'Estado actualizado correctamente.':'No se pudo guardar el rol.'; }
    private function normalizeCommon(array $input): ?array { $code=trim((string)($input['code']??'')); $name=trim((string)($input['name']??'')); $scope=trim((string)($input['scope']??'')); $status=trim((string)($input['status']??'')); $isSystem=(string)($input['is_system']??''); $tenantId=(int)($input['tenant_id']??0); if($code===''||$name===''||!preg_match('/^[a-z0-9_-]+$/',$code)){return null;} if(!in_array($scope,self::ALLOWED_SCOPES,true)||!in_array($status,self::ALLOWED_STATUSES,true)||!in_array($isSystem,['0','1'],true)){return null;} if($scope==='global'){$tenantId=0;} elseif($tenantId<=0 || !$this->repository->tenantExists($tenantId)){return null;} return [':tenant_id'=>$tenantId>0?$tenantId:null,':code'=>$code,':name'=>$name,':description'=>$this->nullableTrim($input['description']??null),':scope'=>$scope,':is_system'=>(int)$isSystem,':status'=>$status]; }
    private function normalizeSystemRoleUpdate(array $input): ?array { $name=trim((string)($input['name']??'')); $status=trim((string)($input['status']??'')); if($name===''||!in_array($status,self::ALLOWED_STATUSES,true)){return null;} return [':name'=>$name,':description'=>$this->nullableTrim($input['description']??null),':status'=>$status]; }
    private function nullableTrim(mixed $value): ?string { $trimmed=trim((string)$value); return $trimmed===''?null:$trimmed; }
    private function isDuplicate(PDOException $e): bool { return isset($e->errorInfo[0]) && (string)$e->errorInfo[0]==='23000'; }
}
