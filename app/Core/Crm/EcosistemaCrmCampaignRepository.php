<?php

declare(strict_types=1);

namespace App\Core\Crm;

use PDO;

final readonly class EcosistemaCrmCampaignRepository
{
    public function __construct(private PDO $pdo) {}

    public function listCampaigns(int $tenantId, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare('SELECT id,tenant_id,channel_id,owner_user_id,name,code,description,campaign_type,objective,status,budget,currency,starts_at,ends_at,landing_url,source_module,source_table,source_id,created_at,updated_at FROM crm_marketing_campaigns WHERE tenant_id=:tenant_id ORDER BY updated_at DESC,id DESC LIMIT :limit');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(200, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findCampaign(int $tenantId, int $campaignId): ?array
    {
        if ($campaignId <= 0) {
            return null;
        }

        $sql = 'SELECT c.id,c.tenant_id,c.channel_id,c.owner_user_id,c.name,c.code,c.description,c.campaign_type,c.objective,c.status,c.budget,c.currency,c.starts_at,c.ends_at,c.landing_url,c.source_module,c.source_table,c.source_id,c.created_at,c.updated_at,COALESCE(v.total_visits,0) AS total_visits,COALESCE(clk.total_clicks,0) AS total_clicks,COALESCE(sub.total_submissions,0) AS total_submissions FROM crm_marketing_campaigns c LEFT JOIN (SELECT tenant_id,campaign_id,COUNT(*) AS total_visits FROM landing_visits GROUP BY tenant_id,campaign_id) v ON v.tenant_id=c.tenant_id AND v.campaign_id=c.id LEFT JOIN (SELECT tenant_id,campaign_id,COUNT(*) AS total_clicks FROM url_clicks GROUP BY tenant_id,campaign_id) clk ON clk.tenant_id=c.tenant_id AND clk.campaign_id=c.id LEFT JOIN (SELECT tenant_id,campaign_id,COUNT(*) AS total_submissions FROM landing_form_submissions GROUP BY tenant_id,campaign_id) sub ON sub.tenant_id=c.tenant_id AND sub.campaign_id=c.id WHERE c.tenant_id=:tenant_id AND c.id=:campaign_id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':campaign_id', $campaignId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function summarizeCampaigns(int $tenantId): array
    {
        $summary = ['total' => 0, 'by_status' => []];
        $total = $this->pdo->prepare('SELECT COUNT(*) FROM crm_marketing_campaigns WHERE tenant_id=:tenant_id');
        $total->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $total->execute();
        $summary['total'] = (int) $total->fetchColumn();

        $group = $this->pdo->prepare('SELECT status,COUNT(*) AS total FROM crm_marketing_campaigns WHERE tenant_id=:tenant_id GROUP BY status ORDER BY status ASC');
        $group->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $group->execute();
        foreach ($group->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $summary['by_status'][] = ['status' => (string) ($row['status'] ?? ''), 'total' => (int) ($row['total'] ?? 0)];
        }

        return $summary;
    }
}
