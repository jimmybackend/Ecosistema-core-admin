<?php
declare(strict_types=1);

namespace App\Core\Campaigns;

use PDO;

final class EcosistemaCampaignCockpitRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function listCampaigns(int $tenantId, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare('SELECT id,name,code,status,campaign_type,starts_at,ends_at,updated_at FROM crm_marketing_campaigns WHERE tenant_id=:tenant_id ORDER BY updated_at DESC,id DESC LIMIT :limit');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(500, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        return (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findCampaign(int $tenantId, int $campaignId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id,name,code,description,campaign_type,objective,status,budget,currency,starts_at,ends_at,landing_url,source_module,source_table,source_id,created_at,updated_at FROM crm_marketing_campaigns WHERE tenant_id=:tenant_id AND id=:id LIMIT 1');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $campaignId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function funnelCounts(int $tenantId, int $campaignId): array
    {
        return [
            'short_links' => $this->count('url_short_links', $tenantId, $campaignId),
            'clicks' => $this->count('url_clicks', $tenantId, $campaignId),
            'visits' => $this->count('landing_visits', $tenantId, $campaignId),
            'submissions' => $this->count('landing_form_submissions', $tenantId, $campaignId),
            'leads' => $this->count('crm_campaign_leads', $tenantId, $campaignId),
            'attributions' => $this->count('browser_analytics_attribution', $tenantId, $campaignId),
            'workflow_runs' => $this->countWorkflowRuns($tenantId, $campaignId),
        ];
    }

    public function leadsByStatus(int $tenantId, int $campaignId): array
    {
        $stmt = $this->pdo->prepare('SELECT status,COUNT(*) AS total FROM crm_campaign_leads WHERE tenant_id=:tenant_id AND campaign_id=:campaign_id GROUP BY status ORDER BY total DESC,status ASC');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':campaign_id', $campaignId, PDO::PARAM_INT);
        $stmt->execute();

        return (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function workflowsByStatus(int $tenantId, int $campaignId): array
    {
        $stmt = $this->pdo->prepare("SELECT status,COUNT(*) AS total FROM workflow_runs WHERE tenant_id=:tenant_id AND source_table='crm_marketing_campaigns' AND source_id=:campaign_id GROUP BY status ORDER BY total DESC,status ASC");
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':campaign_id', $campaignId, PDO::PARAM_INT);
        $stmt->execute();

        return (array) $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function count(string $table, int $tenantId, int $campaignId): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE tenant_id=:tenant_id AND campaign_id=:campaign_id");
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':campaign_id', $campaignId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    private function countWorkflowRuns(int $tenantId, int $campaignId): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM workflow_runs WHERE tenant_id=:tenant_id AND source_table='crm_marketing_campaigns' AND source_id=:campaign_id");
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':campaign_id', $campaignId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}
