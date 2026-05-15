<?php

declare(strict_types=1);

namespace App\Core\Ai;

use PDO;

final readonly class EcosistemaAiCampaignInsightDryRunRepository
{
    public function __construct(private PDO $pdo) {}

    public function findCampaign(int $tenantId, int $campaignId): ?array
    {
        $sql = 'SELECT id,tenant_id,name,code,status,starts_at,ends_at,budget,updated_at FROM crm_marketing_campaigns WHERE tenant_id=:tenant_id AND id=:campaign_id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':campaign_id', $campaignId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function countCampaignLeads(int $tenantId, int $campaignId): int
    {
        $sql = 'SELECT COUNT(*) FROM crm_campaign_leads WHERE tenant_id=:tenant_id AND campaign_id=:campaign_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':campaign_id', $campaignId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function countCampaignEvents(int $tenantId, int $campaignId): int
    {
        $sql = 'SELECT COUNT(*) FROM service_event_logs WHERE tenant_id=:tenant_id AND resource_type=:resource_type AND resource_id=:resource_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':resource_type', 'crm_campaign', PDO::PARAM_STR);
        $stmt->bindValue(':resource_id', $campaignId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) ($stmt->fetchColumn() ?: 0);
    }

    public function listRecentRollups(int $tenantId, int $campaignId, int $limit = 7): array
    {
        $sql = 'SELECT rollup_date,sessions,pageviews,clicks,submissions,conversions FROM browser_analytics_daily_rollups WHERE tenant_id=:tenant_id AND campaign_id=:campaign_id ORDER BY rollup_date DESC LIMIT :limit_rows';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':campaign_id', $campaignId, PDO::PARAM_INT);
        $stmt->bindValue(':limit_rows', max(1, $limit), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
