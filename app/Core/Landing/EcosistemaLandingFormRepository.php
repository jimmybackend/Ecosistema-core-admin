<?php

declare(strict_types=1);

namespace App\Core\Landing;

use PDO;

final readonly class EcosistemaLandingFormRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listForms(int $tenantId, int $limit = 100): array
    {
        $safeLimit = $this->safeLimit($limit);
        $sql = 'SELECT f.id,f.landing_page_id,lp.title AS landing_page_title,f.campaign_id,c.name AS campaign_name,f.name,f.description,f.submit_button_text,f.success_message,f.redirect_url,f.creates_crm_lead,f.default_lead_source_id,f.default_funnel_stage_id,f.default_assigned_user_id,f.score_on_submit,f.is_active,f.created_at,f.updated_at,(SELECT COUNT(*) FROM landing_form_fields ff WHERE ff.tenant_id=f.tenant_id AND ff.form_id=f.id AND ff.is_active=1) AS fields_count FROM landing_forms f LEFT JOIN landing_pages lp ON lp.id=f.landing_page_id AND lp.tenant_id=f.tenant_id LEFT JOIN crm_marketing_campaigns c ON c.id=f.campaign_id WHERE f.tenant_id=:tenant_id ORDER BY f.updated_at DESC,f.id DESC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listFormsForPage(int $tenantId, int $pageId, int $limit = 100): array
    {
        if ($pageId <= 0) { return []; }
        $safeLimit = $this->safeLimit($limit);
        $sql = 'SELECT f.id,f.landing_page_id,lp.title AS landing_page_title,f.campaign_id,c.name AS campaign_name,f.name,f.description,f.submit_button_text,f.success_message,f.redirect_url,f.creates_crm_lead,f.default_lead_source_id,f.default_funnel_stage_id,f.default_assigned_user_id,f.score_on_submit,f.is_active,f.created_at,f.updated_at,(SELECT COUNT(*) FROM landing_form_fields ff WHERE ff.tenant_id=f.tenant_id AND ff.form_id=f.id AND ff.is_active=1) AS fields_count FROM landing_forms f LEFT JOIN landing_pages lp ON lp.id=f.landing_page_id AND lp.tenant_id=f.tenant_id LEFT JOIN crm_marketing_campaigns c ON c.id=f.campaign_id WHERE f.tenant_id=:tenant_id AND f.landing_page_id=:page_id ORDER BY f.updated_at DESC,f.id DESC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':page_id', $pageId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findForm(int $tenantId, int $formId): ?array
    {
        if ($formId <= 0) { return null; }
        $sql = 'SELECT f.id,f.landing_page_id,lp.title AS landing_page_title,f.campaign_id,c.name AS campaign_name,f.name,f.description,f.submit_button_text,f.success_message,f.redirect_url,f.creates_crm_lead,f.default_lead_source_id,f.default_funnel_stage_id,f.default_assigned_user_id,f.score_on_submit,f.is_active,f.created_at,f.updated_at,(SELECT COUNT(*) FROM landing_form_fields ff WHERE ff.tenant_id=f.tenant_id AND ff.form_id=f.id AND ff.is_active=1) AS fields_count FROM landing_forms f LEFT JOIN landing_pages lp ON lp.id=f.landing_page_id AND lp.tenant_id=f.tenant_id LEFT JOIN crm_marketing_campaigns c ON c.id=f.campaign_id WHERE f.tenant_id=:tenant_id AND f.id=:id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $formId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function listFieldsForForm(int $tenantId, int $formId): array
    {
        if ($formId <= 0) { return []; }
        $sql = 'SELECT id,form_id,field_key,label,field_type,placeholder,default_value,options_json,validation_json,crm_target_table,crm_target_field,is_required,is_active,sort_order,created_at,updated_at FROM landing_form_fields WHERE tenant_id=:tenant_id AND form_id=:form_id ORDER BY sort_order ASC,id ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':form_id', $formId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function summarizeForms(int $tenantId): array
    {
        $summary = ['total' => 0, 'active' => 0, 'inactive' => 0];
        $total = $this->pdo->prepare('SELECT COUNT(*) AS total,SUM(CASE WHEN is_active=1 THEN 1 ELSE 0 END) AS active FROM landing_forms WHERE tenant_id=:tenant_id');
        $total->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $total->execute();
        $row = $total->fetch(PDO::FETCH_ASSOC) ?: [];
        $summary['total'] = (int) ($row['total'] ?? 0);
        $summary['active'] = (int) ($row['active'] ?? 0);
        $summary['inactive'] = max(0, $summary['total'] - $summary['active']);

        return $summary;
    }

    private function safeLimit(int $limit): int
    {
        return max(1, min(200, $limit));
    }
}
