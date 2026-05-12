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
        $columns = $this->resolveVisibleColumns();
        $sql = sprintf(
            'SELECT %s FROM cloud_buckets WHERE tenant_id = :tenant_id',
            implode(', ', $columns)
        );

        if ($this->hasColumn('status')) {
            $sql .= " AND (status IS NULL OR status <> 'deleted')";
        }

        $sql .= ' ORDER BY id DESC LIMIT :limit';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<int,string>
     */
    private function resolveVisibleColumns(): array
    {
        $allowedColumns = ['id', 'name', 'provider', 'region', 'status', 'is_default', 'created_at', 'updated_at'];
        $columns = [];

        foreach ($allowedColumns as $column) {
            if ($this->hasColumn($column)) {
                $columns[] = $column;
            }
        }

        return $columns !== [] ? $columns : ['id'];
    }

    private function hasColumn(string $column): bool
    {
        $stmt = $this->pdo->prepare('SHOW COLUMNS FROM cloud_buckets LIKE :column');
        $stmt->bindValue(':column', $column);
        $stmt->execute();

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
