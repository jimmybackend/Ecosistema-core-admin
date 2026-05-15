<?php

declare(strict_types=1);

namespace App\Core\Crm;

use PDO;

final readonly class EcosistemaCrmLeadStatusRepository
{
    public function __construct(private PDO $pdo) {}

    public function findLead(int $tenantId, int $leadId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id,tenant_id,status FROM crm_leads WHERE tenant_id=:tenant_id AND id=:lead_id LIMIT 1');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function updateLeadStatus(int $tenantId, int $leadId, string $status): bool
    {
        $stmt = $this->pdo->prepare('UPDATE crm_leads SET status=:status,updated_at=NOW() WHERE tenant_id=:tenant_id AND id=:lead_id');
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function findCampaignLead(int $tenantId, int $leadId, int $campaignLeadId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id,tenant_id,lead_id,status,temperature,score FROM crm_campaign_leads WHERE tenant_id=:tenant_id AND lead_id=:lead_id AND id=:campaign_lead_id LIMIT 1');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $stmt->bindValue(':campaign_lead_id', $campaignLeadId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function updateCampaignLeadState(int $tenantId, int $campaignLeadId, string $status, ?string $temperature, ?float $score): bool
    {
        $stmt = $this->pdo->prepare('UPDATE crm_campaign_leads SET status=:status,temperature=:temperature,score=:score,updated_at=NOW() WHERE tenant_id=:tenant_id AND id=:campaign_lead_id');
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':temperature', $temperature, $temperature === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':score', $score, $score === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':campaign_lead_id', $campaignLeadId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
