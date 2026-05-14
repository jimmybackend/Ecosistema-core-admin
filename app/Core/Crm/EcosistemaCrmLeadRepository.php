<?php

declare(strict_types=1);

namespace App\Core\Crm;

use PDO;

final readonly class EcosistemaCrmLeadRepository
{
    public function __construct(private PDO $pdo) {}

    public function listLeads(int $tenantId, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare('SELECT id,tenant_id,source_id,owner_user_id,company_name,contact_name,email,phone,interest,status,notes,created_at,updated_at FROM crm_leads WHERE tenant_id=:tenant_id ORDER BY updated_at DESC,id DESC LIMIT :limit');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(200, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findLead(int $tenantId, int $leadId): ?array
    {
        $sql = 'SELECT l.id,l.tenant_id,l.source_id,s.name AS source_name,l.owner_user_id,l.company_name,l.contact_name,l.email,l.phone,l.interest,l.status,l.notes,l.created_at,l.updated_at
                FROM crm_leads l
                LEFT JOIN crm_sources s ON s.id=l.source_id AND s.tenant_id=l.tenant_id
                WHERE l.tenant_id=:tenant_id AND l.id=:lead_id
                LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function listCampaignLinksForLead(int $tenantId, int $leadId): array
    {
        $sql = 'SELECT cl.id,cl.campaign_id,c.name AS campaign_name,c.code AS campaign_code,c.status AS campaign_status,cl.funnel_stage_id,fs.name AS funnel_stage_name,cl.assigned_user_id,cl.status,cl.temperature,cl.score,cl.first_touch_at,cl.last_touch_at,cl.next_followup_at,cl.notes,cl.created_at,cl.updated_at
                FROM crm_campaign_leads cl
                LEFT JOIN crm_marketing_campaigns c ON c.id=cl.campaign_id AND c.tenant_id=cl.tenant_id
                LEFT JOIN crm_lead_funnel_stages fs ON fs.id=cl.funnel_stage_id AND fs.tenant_id=cl.tenant_id
                WHERE cl.tenant_id=:tenant_id AND cl.lead_id=:lead_id
                ORDER BY cl.updated_at DESC,cl.id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listConversionsForLead(int $tenantId, int $leadId): array
    {
        $stmt = $this->pdo->prepare('SELECT id,converted_by_user_id,company_id,contact_id,deal_id,erp_customer_id,conversion_type,conversion_value,currency,notes,converted_at FROM crm_lead_conversions WHERE tenant_id=:tenant_id AND lead_id=:lead_id ORDER BY converted_at DESC,id DESC');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listAttributionForLead(int $tenantId, int $leadId): array
    {
        $stmt = $this->pdo->prepare('SELECT id,visit_id,submission_id,session_id,utm_source,utm_medium,utm_campaign,utm_term,utm_content,referrer_url,landing_url,attributed_at FROM browser_analytics_attribution WHERE tenant_id=:tenant_id AND lead_id=:lead_id ORDER BY attributed_at DESC,id DESC LIMIT 100');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listLandingSubmissionsForLead(int $tenantId, int $leadId): array
    {
        $sql = 'SELECT s.id,s.form_id,s.campaign_id,c.name AS campaign_name,s.visit_id,s.crm_lead_id,s.contact_name,s.email,s.phone,s.company_name,s.interest,s.message,s.status,s.submitted_at,
                       (SELECT COUNT(*) FROM landing_form_submission_values v WHERE v.tenant_id=s.tenant_id AND v.submission_id=s.id) AS values_count,
                       (SELECT COUNT(*) FROM url_clicks uc WHERE uc.tenant_id=s.tenant_id AND uc.visit_id=s.visit_id) AS url_clicks_count
                FROM landing_form_submissions s
                LEFT JOIN crm_marketing_campaigns c ON c.id=s.campaign_id AND c.tenant_id=s.tenant_id
                WHERE s.tenant_id=:tenant_id AND s.crm_lead_id=:lead_id
                ORDER BY s.submitted_at DESC,s.id DESC LIMIT 100';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function summarizeLeads(int $tenantId): array
    {
        $summary = ['total' => 0, 'by_status' => []];

        $total = $this->pdo->prepare('SELECT COUNT(*) FROM crm_leads WHERE tenant_id=:tenant_id');
        $total->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $total->execute();
        $summary['total'] = (int) $total->fetchColumn();

        $group = $this->pdo->prepare('SELECT status,COUNT(*) AS total FROM crm_leads WHERE tenant_id=:tenant_id GROUP BY status ORDER BY status ASC');
        $group->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $group->execute();

        foreach ($group->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $summary['by_status'][] = [
                'status' => (string) ($row['status'] ?? ''),
                'total' => (int) ($row['total'] ?? 0),
            ];
        }

        return $summary;
    }
}
