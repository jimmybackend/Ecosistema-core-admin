<?php

declare(strict_types=1);

namespace App\Core\Attribution;

use PDO;

final readonly class EcosistemaCampaignAttributionRepository
{
    public function __construct(private PDO $pdo) {}

    public function listCampaigns(int $tenantId, int $limit = 100): array
    {
        $safeLimit = max(1, min(200, $limit));
        $sql = 'SELECT id,tenant_id,name,code,status,campaign_type,objective,starts_at,ends_at,created_at,updated_at
                FROM crm_marketing_campaigns
                WHERE tenant_id=:tenant_id
                ORDER BY updated_at DESC,id DESC
                LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findCampaign(int $tenantId, int $campaignId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id,tenant_id,name,code,status,campaign_type,objective,description,starts_at,ends_at,created_at,updated_at FROM crm_marketing_campaigns WHERE tenant_id=:tenant_id AND id=:id LIMIT 1');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $campaignId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function fetchFunnelCounts(int $tenantId, int $campaignId): array
    {
        return [
            'clicks' => $this->countByTable('url_clicks', $tenantId, $campaignId),
            'visits' => $this->countByTable('landing_visits', $tenantId, $campaignId),
            'submissions' => $this->countByTable('landing_form_submissions', $tenantId, $campaignId),
            'leads' => $this->countByTable('crm_campaign_leads', $tenantId, $campaignId),
            'conversions' => $this->countByTable('crm_lead_conversions', $tenantId, $campaignId),
        ];
    }

    private function countByTable(string $table, int $tenantId, int $campaignId): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE tenant_id=:tenant_id AND campaign_id=:campaign_id");
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':campaign_id', $campaignId, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
