<?php

declare(strict_types=1);

namespace App\Core\Reports;

use PDO;

final readonly class EcosistemaReportExportRepository
{
    public function __construct(private PDO $pdo) {}

    public function findDashboardById(int $tenantId, int $dashboardId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, tenant_id, dashboard_key, name, visibility, is_active, created_at FROM reports_dashboards WHERE tenant_id = :tenant_id AND id = :id LIMIT 1');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $dashboardId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function createExportRequest(int $tenantId, int $userId, string $reportType, int $sourceId, string $format, string $metadataJson): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO reports_exports (tenant_id, report_type, source_id, format, status, system_job_id, file_id, requested_by_user_id, requested_at, completed_at, metadata_json) VALUES (:tenant_id, :report_type, :source_id, :format, :status, NULL, NULL, :requested_by_user_id, NOW(), NULL, :metadata_json)');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':report_type', $reportType);
        $stmt->bindValue(':source_id', $sourceId, PDO::PARAM_INT);
        $stmt->bindValue(':format', $format);
        $stmt->bindValue(':status', 'queued');
        $stmt->bindValue(':requested_by_user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':metadata_json', $metadataJson);
        $stmt->execute();

        return (int) $this->pdo->lastInsertId();
    }
}
