<?php

declare(strict_types=1);

namespace App\Core\Tenants;

use PDO;

final readonly class TenantRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array<int,array<string,mixed>> */
    public function listRecent(int $limit = 50): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, slug, legal_name, domain, plan_code, status, timezone, locale, created_at, updated_at
             FROM core_tenants
             ORDER BY id DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<string,mixed>|null */
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, slug, legal_name, domain, plan_code, status, timezone, locale, created_at, updated_at
             FROM core_tenants
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $tenant = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($tenant) ? $tenant : null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO core_tenants (name, slug, legal_name, domain, plan_code, status, timezone, locale)
             VALUES (:name, :slug, :legal_name, :domain, :plan_code, :status, :timezone, :locale)'
        );

        return $stmt->execute([
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':legal_name' => $data['legal_name'],
            ':domain' => $data['domain'],
            ':plan_code' => $data['plan_code'],
            ':status' => $data['status'],
            ':timezone' => $data['timezone'],
            ':locale' => $data['locale'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE core_tenants
             SET name = :name,
                 slug = :slug,
                 legal_name = :legal_name,
                 domain = :domain,
                 plan_code = :plan_code,
                 status = :status,
                 timezone = :timezone,
                 locale = :locale,
                 updated_at = NOW()
             WHERE id = :id'
        );

        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':slug' => $data['slug'],
            ':legal_name' => $data['legal_name'],
            ':domain' => $data['domain'],
            ':plan_code' => $data['plan_code'],
            ':status' => $data['status'],
            ':timezone' => $data['timezone'],
            ':locale' => $data['locale'],
        ]);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE core_tenants
             SET status = :status,
                 updated_at = NOW()
             WHERE id = :id'
        );

        return $stmt->execute([':id' => $id, ':status' => $status]);
    }
}
