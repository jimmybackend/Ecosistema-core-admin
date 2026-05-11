<?php

declare(strict_types=1);

namespace App\Core\Modules;

use PDO;

final readonly class ModuleRepository
{
    public function __construct(private PDO $pdo) {}
    public function list(int $limit = 100): array { $stmt=$this->pdo->prepare('SELECT id, code, name, description, table_prefix, is_billable, is_core, status, created_at, updated_at FROM core_modules ORDER BY id ASC LIMIT :limit'); $stmt->bindValue(':limit',$limit,PDO::PARAM_INT); $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []; }
    public function findById(int $id): ?array { $stmt=$this->pdo->prepare('SELECT id, code, name, description, table_prefix, is_billable, is_core, status, created_at, updated_at FROM core_modules WHERE id = :id LIMIT 1'); $stmt->execute([':id'=>$id]); $row=$stmt->fetch(PDO::FETCH_ASSOC); return is_array($row)?$row:null; }
    public function create(array $data): bool { $stmt=$this->pdo->prepare('INSERT INTO core_modules (code, name, description, table_prefix, is_billable, is_core, status) VALUES (:code, :name, :description, :table_prefix, :is_billable, :is_core, :status)'); return $stmt->execute([':code'=>$data['code'],':name'=>$data['name'],':description'=>$data['description'],':table_prefix'=>$data['table_prefix'],':is_billable'=>$data['is_billable'],':is_core'=>$data['is_core'],':status'=>$data['status']]); }
    public function update(int $id, array $data): bool { $stmt=$this->pdo->prepare('UPDATE core_modules SET code = :code, name = :name, description = :description, table_prefix = :table_prefix, is_billable = :is_billable, is_core = :is_core, status = :status, updated_at = NOW() WHERE id = :id'); return $stmt->execute([':id'=>$id,':code'=>$data['code'],':name'=>$data['name'],':description'=>$data['description'],':table_prefix'=>$data['table_prefix'],':is_billable'=>$data['is_billable'],':is_core'=>$data['is_core'],':status'=>$data['status']]); }
    public function updateStatus(int $id, string $status): bool { $stmt=$this->pdo->prepare('UPDATE core_modules SET status = :status, updated_at = NOW() WHERE id = :id'); return $stmt->execute([':id'=>$id,':status'=>$status]); }
}
