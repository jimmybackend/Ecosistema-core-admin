<?php

declare(strict_types=1);

namespace App\Core\Cloud;

use PDO;

final readonly class EcosistemaDriveSummaryService
{
    public function __construct(
        private PDO $pdo,
        private EcosistemaDriveRootRepository $rootRepository,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function getSummary(int $tenantId, int $userId): array
    {
        $warnings = [];
        $root = $this->rootRepository->findForUser($tenantId, $userId);

        if ($root === null) {
            $warnings[] = 'No existe raíz Drive configurada para el usuario actual.';
        }

        $quotaBytes = $root['quota_bytes'] ?? null;
        $usedBytes = $root['used_bytes'] ?? null;
        if ($quotaBytes === null || $usedBytes === null) {
            $warnings[] = 'No hay información completa de cuota/uso en cloud_user_roots.';
        }

        return [
            'root_summary' => $root === null ? null : [
                'id' => isset($root['id']) ? (int) $root['id'] : null,
                'display_name' => isset($root['display_name']) ? (string) $root['display_name'] : null,
                'status' => isset($root['status']) ? (string) $root['status'] : null,
                'bucket_id' => isset($root['bucket_id']) ? (int) $root['bucket_id'] : null,
            ],
            'file_count' => $this->countFiles($tenantId, $userId),
            'folder_count' => $this->countFolders($tenantId, $userId),
            'bucket_count' => $this->countBuckets($tenantId),
            'quota_bytes' => $quotaBytes !== null ? (int) $quotaBytes : null,
            'used_bytes' => $usedBytes !== null ? (int) $usedBytes : null,
            'read_only' => true,
            'mode' => 'contract/dry-run',
            'warnings' => $warnings,
        ];
    }

    private function countFiles(int $tenantId, int $userId): int
    {
        $sql = 'SELECT COUNT(*) FROM cloud_files WHERE tenant_id = :tenant_id AND user_id = :user_id';
        if ($this->hasColumn('cloud_files', 'status')) {
            $sql .= " AND status <> 'deleted'";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    private function countFolders(int $tenantId, int $userId): int
    {
        $sql = 'SELECT COUNT(*) FROM cloud_folders WHERE tenant_id = :tenant_id AND user_id = :user_id';
        if ($this->hasColumn('cloud_folders', 'is_deleted')) {
            $sql .= ' AND is_deleted = 0';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    private function countBuckets(int $tenantId): int
    {
        $sql = 'SELECT COUNT(*) FROM cloud_buckets WHERE tenant_id = :tenant_id';
        if ($this->hasColumn('cloud_buckets', 'status')) {
            $sql .= " AND (status IS NULL OR status <> 'deleted')";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    private function hasColumn(string $table, string $column): bool
    {
        $stmt = $this->pdo->prepare(sprintf('SHOW COLUMNS FROM %s LIKE :column', $table));
        $stmt->bindValue(':column', $column);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
}
