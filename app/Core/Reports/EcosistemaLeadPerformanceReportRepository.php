<?php

declare(strict_types=1);

namespace App\Core\Reports;

use PDO;

final class EcosistemaLeadPerformanceReportRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function bySource(int $tenantId, string $fromDate, string $toDate): array
    {
        $stmt = $this->pdo->prepare('SELECT COALESCE(source_module,\'unknown\') AS source_module, COUNT(*) AS leads, SUM(CASE WHEN converted_at IS NOT NULL THEN 1 ELSE 0 END) AS conversions FROM crm_leads WHERE tenant_id=:tenant_id AND DATE(created_at) BETWEEN :from_date AND :to_date GROUP BY COALESCE(source_module,\'unknown\') ORDER BY leads DESC, source_module ASC LIMIT 100');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':from_date', $fromDate, PDO::PARAM_STR);
        $stmt->bindValue(':to_date', $toDate, PDO::PARAM_STR);
        $stmt->execute();
        return (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function byCampaign(int $tenantId, string $fromDate, string $toDate): array
    {
        $stmt = $this->pdo->prepare('SELECT campaign_id, COUNT(*) AS leads, SUM(CASE WHEN converted_at IS NOT NULL THEN 1 ELSE 0 END) AS conversions FROM crm_leads WHERE tenant_id=:tenant_id AND DATE(created_at) BETWEEN :from_date AND :to_date GROUP BY campaign_id ORDER BY leads DESC, campaign_id DESC LIMIT 100');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':from_date', $fromDate, PDO::PARAM_STR);
        $stmt->bindValue(':to_date', $toDate, PDO::PARAM_STR);
        $stmt->execute();
        return (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function byStatus(int $tenantId, string $fromDate, string $toDate): array
    {
        $stmt = $this->pdo->prepare('SELECT COALESCE(status,\'unknown\') AS status, COUNT(*) AS leads, SUM(CASE WHEN converted_at IS NOT NULL THEN 1 ELSE 0 END) AS conversions FROM crm_leads WHERE tenant_id=:tenant_id AND DATE(created_at) BETWEEN :from_date AND :to_date GROUP BY COALESCE(status,\'unknown\') ORDER BY leads DESC, status ASC LIMIT 100');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':from_date', $fromDate, PDO::PARAM_STR);
        $stmt->bindValue(':to_date', $toDate, PDO::PARAM_STR);
        $stmt->execute();
        return (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function scoreTemperatureSummary(int $tenantId, string $fromDate, string $toDate): array
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS leads, AVG(COALESCE(score,0)) AS avg_score, SUM(CASE WHEN temperature=\'hot\' THEN 1 ELSE 0 END) AS hot_leads, SUM(CASE WHEN temperature=\'warm\' THEN 1 ELSE 0 END) AS warm_leads, SUM(CASE WHEN temperature=\'cold\' THEN 1 ELSE 0 END) AS cold_leads FROM crm_leads WHERE tenant_id=:tenant_id AND DATE(created_at) BETWEEN :from_date AND :to_date');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':from_date', $fromDate, PDO::PARAM_STR);
        $stmt->bindValue(':to_date', $toDate, PDO::PARAM_STR);
        $stmt->execute();

        return (array) ($stmt->fetch(PDO::FETCH_ASSOC) ?: []);
    }
}
