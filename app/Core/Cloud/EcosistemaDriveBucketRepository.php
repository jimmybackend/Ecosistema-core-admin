<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use PDO;

final readonly class EcosistemaDriveBucketRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function listVisible(int $tenantId, int $limit = 100): array
    {
        $limit = max(1, min($limit, 100));

        $fields = ['id', 'name', 'provider', 'region', 'is_default', 'created_at', 'updated_at'];
        if ($this->hasColumn('status')) {
            $fields[] = 'status';
        }

        $sql = sprintf(
            'SELECT %s FROM cloud_buckets WHERE tenant_id = :tenant_id',
            implode(', ', $fields)
        );

        if (in_array('status', $fields, true)) {
            $sql .= " AND (status IS NULL OR status <> 'deleted')";
        }

        $sql .= ' ORDER BY id DESC LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function hasColumn(string $column): bool
    {
        $stmt = $this->pdo->prepare('SHOW COLUMNS FROM cloud_buckets LIKE :column');
        $stmt->bindValue(':column', $column);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
}
