<?php

declare(strict_types=1);

namespace App\Core\Roles;

use PDO;

final readonly class RoleRepository
{
    public function __construct(private PDO $pdo){}
    public function listRecent(int $limit = 100): array { $stmt=$this->pdo->prepare('SELECT r.id, r.tenant_id, r.slug, r.name, r.description, r.is_system, r.created_at, r.updated_at, t.name AS tenant_name, t.slug AS tenant_slug FROM core_roles r LEFT JOIN core_tenants t ON t.id = r.tenant_id ORDER BY r.id DESC LIMIT :limit'); $stmt->bindValue(':limit',$limit,PDO::PARAM_INT); $stmt->execute(); return array_map(fn(array $r): array => $this->mapRole($r), $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []); }
    public function findById(int $id): ?array { $stmt=$this->pdo->prepare('SELECT id, tenant_id, slug, name, description, is_system, created_at, updated_at FROM core_roles WHERE id = :id LIMIT 1'); $stmt->execute([':id'=>$id]); $role=$stmt->fetch(PDO::FETCH_ASSOC); return is_array($role)?$this->mapRole($role):null; }
    public function listTenants(): array { $stmt=$this->pdo->query('SELECT id, name, slug, status FROM core_tenants ORDER BY id DESC LIMIT 200'); return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: []; }
    public function tenantExists(int $tenantId): bool { $stmt=$this->pdo->prepare('SELECT id FROM core_tenants WHERE id = :id LIMIT 1'); $stmt->execute([':id'=>$tenantId]); return (bool)$stmt->fetchColumn(); }
    public function create(array $data): bool { $stmt=$this->pdo->prepare('INSERT INTO core_roles (tenant_id, slug, name, description, is_system, created_at, updated_at) VALUES (:tenant_id, :slug, :name, :description, :is_system, NOW(), NOW())'); return $stmt->execute($data); }
    public function update(int $id, array $data): bool { $data[':id']=$id; $stmt=$this->pdo->prepare('UPDATE core_roles SET tenant_id = :tenant_id, slug = :slug, name = :name, description = :description, is_system = :is_system, updated_at = NOW() WHERE id = :id'); return $stmt->execute($data); }
    public function updateNameDescriptionStatus(int $id, array $data): bool { $data[':id']=$id; $stmt=$this->pdo->prepare('UPDATE core_roles SET name = :name, description = :description, updated_at = NOW() WHERE id = :id'); return $stmt->execute($data); }
    public function updateStatus(int $id, string $status): bool { return true; }

    private function mapRole(array $role): array { $role['code'] = $role['slug'] ?? ''; $role['status'] = 'active'; return $role; }
}
