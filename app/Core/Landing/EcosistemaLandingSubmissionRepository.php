<?php

declare(strict_types=1);

namespace App\Core\Landing;

use PDO;

final readonly class EcosistemaLandingSubmissionRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listRecentSubmissions(int $tenantId, int $limit = 100): array
    {
        return $this->listByWhere($tenantId, '', [], $limit);
    }

    public function listSubmissionsForForm(int $tenantId, int $formId, int $limit = 100): array
    {
        if ($formId <= 0) { return []; }
        return $this->listByWhere($tenantId, 's.form_id=:form_id', [':form_id' => $formId], $limit);
    }

    public function listSubmissionsForPage(int $tenantId, int $pageId, int $limit = 100): array
    {
        if ($pageId <= 0) { return []; }
        return $this->listByWhere($tenantId, 's.landing_page_id=:page_id', [':page_id' => $pageId], $limit);
    }

    public function findSubmission(int $tenantId, int $submissionId): ?array
    {
        if ($submissionId <= 0) { return null; }
        $rows = $this->listByWhere($tenantId, 's.id=:submission_id', [':submission_id' => $submissionId], 1);
        return $rows[0] ?? null;
    }

    public function listSubmissionValues(int $tenantId, int $submissionId): array
    {
        if ($submissionId <= 0) { return []; }
        $sql = 'SELECT id,submission_id,field_id,field_key,field_label,value_text,value_json,file_path,s3_key,created_at FROM landing_form_submission_values WHERE tenant_id=:tenant_id AND submission_id=:submission_id ORDER BY id ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':submission_id', $submissionId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function summarizeSubmissions(int $tenantId): array
    {
        $summary = ['total' => 0, 'by_status' => [], 'spam_score' => ['scored' => 0, 'high' => 0]];
        $total = $this->pdo->prepare('SELECT COUNT(*) AS total,SUM(CASE WHEN spam_score IS NOT NULL THEN 1 ELSE 0 END) AS scored,SUM(CASE WHEN spam_score >= 80 THEN 1 ELSE 0 END) AS high FROM landing_form_submissions WHERE tenant_id=:tenant_id');
        $total->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $total->execute();
        $row = $total->fetch(PDO::FETCH_ASSOC) ?: [];
        $summary['total'] = (int) ($row['total'] ?? 0);
        $summary['spam_score']['scored'] = (int) ($row['scored'] ?? 0);
        $summary['spam_score']['high'] = (int) ($row['high'] ?? 0);

        $status = $this->pdo->prepare('SELECT COALESCE(status,\'\') AS bucket,COUNT(*) AS total FROM landing_form_submissions WHERE tenant_id=:tenant_id GROUP BY status ORDER BY total DESC,bucket ASC LIMIT 20');
        $status->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $status->execute();
        foreach ($status->fetchAll(PDO::FETCH_ASSOC) ?: [] as $item) {
            $summary['by_status'][] = ['label' => (string) ($item['bucket'] ?? ''), 'total' => (int) ($item['total'] ?? 0)];
        }

        return $summary;
    }

    private function listByWhere(int $tenantId, string $extraWhere, array $bindings, int $limit): array
    {
        $safeLimit = max(1, min(200, $limit));
        $where = 's.tenant_id=:tenant_id' . ($extraWhere !== '' ? ' AND ' . $extraWhere : '');
        $sql = 'SELECT s.id,s.form_id,f.name AS form_name,s.landing_page_id,lp.title AS landing_page_title,s.campaign_id,c.name AS campaign_name,s.visit_id,s.crm_lead_id,s.submitted_by_user_id,s.contact_name,s.email,s.phone,s.company_name,s.interest,s.message,s.raw_data_json,s.ip_address,s.user_agent,s.country,s.region,s.city,s.latitude,s.longitude,s.status,s.spam_score,s.submitted_at,s.processed_at FROM landing_form_submissions s LEFT JOIN landing_forms f ON f.id=s.form_id AND f.tenant_id=s.tenant_id LEFT JOIN landing_pages lp ON lp.id=s.landing_page_id AND lp.tenant_id=s.tenant_id LEFT JOIN crm_marketing_campaigns c ON c.id=s.campaign_id WHERE ' . $where . ' ORDER BY s.submitted_at DESC,s.id DESC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        foreach ($bindings as $k => $v) { $stmt->bindValue($k, $v, PDO::PARAM_INT); }
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
