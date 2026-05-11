<?php

declare(strict_types=1);

namespace App\Core\Roles;

use PDO;

final readonly class RoleRepository
{
    public function __construct(private PDO $pdo){}
    public function listRecent(int $limit = 100): array { $stmt=$this->pdo->prepare('SELECT r.id, r.tenant_id, r.code, r.name, r.description, r.scope, r.is_system, r.status, r.created_at, r.updated_at, t.name AS tenant_name, t.slug AS tenant_slug FROM core_roles r LEFT JOIN core_tenants t ON t.id = r.tenant_id ORDER BY r.id DESC LIMIT :limit'); $stmt->bindValue(':limit',$limit,PDO::PARAM_INT); $stmt->execute(); return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []; }
    public function findById(int $id): ?array { $stmt=$this->pdo->prepare('SELECT id, tenant_id, code, name, description, scope, is_system, status, created_at, updated_at FROM core_roles WHERE id = :id LIMIT 1'); $stmt->execute([':id'=>$id]); $role=$stmt->fetch(PDO::FETCH_ASSOC); return is_array($role)?$role:null; }
    public function listTenants(): array { $stmt=$this->pdo->query('SELECT id, name, slug, status FROM core_tenants ORDER BY id DESC LIMIT 200'); return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []; }
    public function tenantExists(int $tenantId): bool { $stmt=$this->pdo->prepare('SELECT id FROM core_tenants WHERE id = :id LIMIT 1'); $stmt->execute([':id'=>$tenantId]); return (bool)$stmt->fetchColumn(); }
    public function create(array $data): bool { $stmt=$this->pdo->prepare('INSERT INTO core_roles (tenant_id, code, name, description, scope, is_system, status) VALUES (:tenant_id, :code, :name, :description, :scope, :is_system, :status)'); return $stmt->execute($data); }
    public function update(int $id, array $data): bool { $data[':id']=$id; $stmt=$this->pdo->prepare('UPDATE core_roles SET tenant_id = :tenant_id, code = :code, name = :name, description = :description, scope = :scope, is_system = :is_system, status = :status, updated_at = NOW() WHERE id = :id'); return $stmt->execute($data); }
    public function updateNameDescriptionStatus(int $id, array $data): bool { $data[':id']=$id; $stmt=$this->pdo->prepare('UPDATE core_roles SET name = :name, description = :description, status = :status, updated_at = NOW() WHERE id = :id'); return $stmt->execute($data); }
    public function updateStatus(int $id, string $status): bool { $stmt=$this->pdo->prepare('UPDATE core_roles SET status = :status, updated_at = NOW() WHERE id = :id'); return $stmt->execute([':id'=>$id,':status'=>$status]); }
}
