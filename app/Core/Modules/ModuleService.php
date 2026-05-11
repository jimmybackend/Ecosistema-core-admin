<?php

declare(strict_types=1);

namespace App\Core\Modules;

final readonly class ModuleService
{
    public const ALLOWED_STATUSES = ['active', 'inactive', 'deprecated'];
    public function __construct(private ModuleRepository $repository) {}
    public function listModules(): array { return $this->repository->list(100); }
    public function findModule(int $id): ?array { return $this->repository->findById($id); }
    public function createModule(array $input): string { $data=$this->normalizeAndValidate($input); if($data===null){return 'No se pudo guardar el módulo.';} $this->repository->create($data); return 'Módulo creado correctamente.'; }
    public function updateModule(int $id, array $input): string { $current=$this->repository->findById($id); if($current===null){return 'Módulo no encontrado.';} $data=$this->normalizeAndValidate($input); if($data===null){return 'No se pudo guardar el módulo.';} if((int)($current['is_core']??0)===1 && (($current['code']??'')!==$data['code'] || ($current['table_prefix']??null)!==$data['table_prefix'] || (int)$data['is_core']!==1)){ return 'No se pueden modificar campos críticos de un módulo core desde esta pantalla.'; } $this->repository->update($id,$data); return 'Módulo actualizado correctamente.'; }
    public function changeStatus(int $id, string $status): string { $current=$this->repository->findById($id); if($current===null){return 'Módulo no encontrado.';} $value=trim($status); if(!in_array($value,self::ALLOWED_STATUSES,true)){return 'No se pudo guardar el módulo.';} if((int)($current['is_core']??0)===1 && $value==='deprecated'){ return 'No se puede marcar como deprecated un módulo core desde esta pantalla.'; } $this->repository->updateStatus($id,$value); return 'Estado actualizado correctamente.'; }
    private function normalizeAndValidate(array $input): ?array { $code=trim((string)($input['code']??'')); $name=trim((string)($input['name']??'')); $status=trim((string)($input['status']??'')); if($code===''||$name===''||!in_array($status,self::ALLOWED_STATUSES,true)||!preg_match('/^[a-z0-9_-]+$/',$code)){return null;} $tablePrefix=$this->nullableTrim($input['table_prefix']??null); if($tablePrefix!==null && !preg_match('/^[a-z0-9_]+$/',$tablePrefix)){return null;} $isBillable=(string)($input['is_billable']??''); $isCore=(string)($input['is_core']??''); if(!in_array($isBillable,['0','1'],true)||!in_array($isCore,['0','1'],true)){return null;} return ['code'=>$code,'name'=>$name,'description'=>$this->nullableTrim($input['description']??null),'table_prefix'=>$tablePrefix,'is_billable'=>(int)$isBillable,'is_core'=>(int)$isCore,'status'=>$status]; }
    private function nullableTrim(mixed $value): ?string { $trimmed=trim((string)$value); return $trimmed===''?null:$trimmed; }
}
