<?php

declare(strict_types=1);

namespace App\Core\Reports;

use PDO;

final readonly class EcosistemaReportExportDryRunRepository
{
    public function __construct(private PDO $pdo) {}

    public function listRows(int $tenantId, string $reportType, int $limit): array
    {
        [$sql, $orderBy] = match ($reportType) {
            'marketing_funnel' => ['SELECT id, tenant_id, campaign_id, landing_page_id, session_id, submitted_at FROM landing_form_submissions WHERE tenant_id = :tenant_id', 'submitted_at DESC'],
            'lead_performance' => ['SELECT id, tenant_id, source_module, lead_temperature, score, created_at, converted_at FROM crm_leads WHERE tenant_id = :tenant_id', 'created_at DESC'],
            default => ['SELECT id, tenant_id, dashboard_key, name, visibility, is_active, created_at FROM reports_dashboards WHERE tenant_id = :tenant_id', 'created_at DESC'],
        };

        $stmt = $this->pdo->prepare($sql . ' ORDER BY ' . $orderBy . ' LIMIT :limit');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
