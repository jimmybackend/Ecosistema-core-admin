<?php
declare(strict_types=1);
namespace App\Core\Permissions;
use PDOException;
final readonly class PermissionService{public const ALLOWED_STATUSES=['active','inactive','deleted'];public function __construct(private PermissionRepository $repository){}
public function listPermissions(): array{return $this->repository->listRecent(100);} public function listModules(): array{return $this->repository->listModules();} public function findPermission(int $id): ?array{return $this->repository->findPermission($id);} 
public function createPermission(array $input): string {$data=$this->normalize($input); if($data===null){return 'No se pudo guardar el permiso.';} try{return $this->repository->create($data)?'Permiso creado correctamente.':'No se pudo guardar el permiso.';}catch(PDOException $e){return $this->isDuplicate($e)?'Ya existe un permiso con ese código.':'No se pudo guardar el permiso.';}}
public function updatePermission(int $id,array $input): string {$perm=$this->repository->findPermission($id); if($perm===null){return 'Permiso no encontrado.';} $data=$this->normalize($input); if($data===null){return 'No se pudo guardar el permiso.';} try{return $this->repository->update($id,$data)?'Permiso actualizado correctamente.':'No se pudo guardar el permiso.';}catch(PDOException $e){return $this->isDuplicate($e)?'Ya existe un permiso con ese código.':'No se pudo guardar el permiso.';}}
public function changeStatus(int $id,string $status): string {$perm=$this->repository->findPermission($id); if($perm===null){return 'Permiso no encontrado.';} $st=trim($status); if(!in_array($st,self::ALLOWED_STATUSES,true)){return 'No se pudo guardar el permiso.';} return $this->repository->updateStatus($id,$st)?'Estado actualizado correctamente.':'No se pudo guardar el permiso.';}
private function normalize(array $i): ?array {$moduleId=(int)($i['module_id']??0);$code=trim((string)($i['code']??''));$name=trim((string)($i['name']??''));if($moduleId<=0||!$this->repository->moduleExists($moduleId)){return null;}if($code===''||!preg_match('/^[a-z0-9._-]+$/',$code)||$name===''){return null;} $description=trim((string)($i['description']??''));return [':module_id'=>$moduleId,':code'=>$code,':name'=>$name,':description'=>$description===''?null:$description];}
private function isDuplicate(PDOException $e): bool {return isset($e->errorInfo[0])&&(string)$e->errorInfo[0]==='23000';}
}
