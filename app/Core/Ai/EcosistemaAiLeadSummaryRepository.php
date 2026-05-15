<?php

declare(strict_types=1);

namespace App\Core\Ai;

use PDO;

final readonly class EcosistemaAiLeadSummaryRepository
{
    public function __construct(private PDO $pdo) {}

    public function findLeadContext(int $tenantId, int $leadId): ?array
    {
        $sql = 'SELECT id,tenant_id,source_id,owner_user_id,company_name,contact_name,email,phone,interest,status,notes,created_at,updated_at FROM crm_leads WHERE tenant_id=:tenant_id AND id=:lead_id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function listCampaignTouches(int $tenantId, int $leadId): array
    {
        $sql = 'SELECT cl.id,cl.campaign_id,c.name AS campaign_name,c.code AS campaign_code,cl.status,cl.temperature,cl.score,cl.first_touch_at,cl.last_touch_at
                FROM crm_campaign_leads cl
                LEFT JOIN crm_marketing_campaigns c ON c.id=cl.campaign_id AND c.tenant_id=cl.tenant_id
                WHERE cl.tenant_id=:tenant_id AND cl.lead_id=:lead_id
                ORDER BY cl.updated_at DESC,cl.id DESC LIMIT 20';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
