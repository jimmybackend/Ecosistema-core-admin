<?php

declare(strict_types=1);

namespace App\Core\Crm;

final readonly class EcosistemaCrmLeadService
{
    public function __construct(private EcosistemaCrmLeadRepository $repository, private EcosistemaCrmAdapter $adapter) {}

    public function listLeads(int $tenantId, int $limit = 100): array
    {
        return [
            'summary' => $this->repository->summarizeLeads($tenantId),
            'leads' => array_map(fn(array $row): array => $this->toSafeDto($row), $this->repository->listLeads($tenantId, $limit)),
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    public function getLeadDetail(int $tenantId, int $leadId): ?array
    {
        $lead = $this->repository->findLead($tenantId, $leadId);
        if ($lead === null) {
            return null;
        }

        return [
            'lead' => $this->toSafeDetailDto($lead),
            'campaign_links' => array_map(fn(array $r): array => $this->toCampaignLinkDto($r), $this->repository->listCampaignLinksForLead($tenantId, $leadId)),
            'conversions' => array_map(fn(array $r): array => $this->toConversionDto($r), $this->repository->listConversionsForLead($tenantId, $leadId)),
            'analytics_attribution' => array_map(fn(array $r): array => $this->toAttributionDto($r), $this->repository->listAttributionForLead($tenantId, $leadId)),
            'landing_submissions_summary' => array_map(fn(array $r): array => $this->toLandingSubmissionDto($r), $this->repository->listLandingSubmissionsForLead($tenantId, $leadId)),
            'pii_exposed' => false,
            'mode' => 'read-only',
            'db_write' => false,
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    private function toSafeDto(array $row): array
    { /* unchanged */
        $contactName = trim((string) ($row['contact_name'] ?? ''));
        $email = trim((string) ($row['email'] ?? ''));
        $phone = trim((string) ($row['phone'] ?? ''));
        $notes = trim((string) ($row['notes'] ?? ''));
        return ['id' => (int) ($row['id'] ?? 0),'source_id' => isset($row['source_id']) ? (int) $row['source_id'] : null,'owner_user_id' => isset($row['owner_user_id']) ? (int) $row['owner_user_id'] : null,'company_name_preview' => $this->preview((string) ($row['company_name'] ?? ''), 60),'contact_name_present' => $contactName !== '','contact_name_preview' => $this->maskText($contactName, 2),'email_present' => $email !== '','email_preview' => $this->maskEmail($email),'phone_present' => $phone !== '','phone_preview' => $this->maskPhone($phone),'interest_preview' => $this->preview((string) ($row['interest'] ?? ''), 80),'status' => (string) ($row['status'] ?? ''),'notes_present' => $notes !== '','notes_preview' => $this->preview($notes, 80),'created_at' => $row['created_at'] ?? null,'updated_at' => $row['updated_at'] ?? null,'mode' => 'read-only','db_write' => false];
    }

    private function toSafeDetailDto(array $row): array
    {
        return $this->toSafeDto($row) + ['source_name' => $this->preview((string)($row['source_name'] ?? ''), 80)];
    }
    private function toCampaignLinkDto(array $row): array
    {
        return ['id'=>(int)($row['id']??0),'campaign_id'=>isset($row['campaign_id'])?(int)$row['campaign_id']:null,'campaign_name'=>$this->preview((string)($row['campaign_name']??''),80),'campaign_code'=>$this->preview((string)($row['campaign_code']??''),40),'campaign_status'=>(string)($row['campaign_status']??''),'funnel_stage_id'=>isset($row['funnel_stage_id'])?(int)$row['funnel_stage_id']:null,'funnel_stage_name'=>$this->preview((string)($row['funnel_stage_name']??''),80),'assigned_user_id'=>isset($row['assigned_user_id'])?(int)$row['assigned_user_id']:null,'status'=>(string)($row['status']??''),'temperature'=>(string)($row['temperature']??''),'score'=>isset($row['score'])?(float)$row['score']:null,'first_touch_at'=>$row['first_touch_at']??null,'last_touch_at'=>$row['last_touch_at']??null,'next_followup_at'=>$row['next_followup_at']??null,'notes_present'=>trim((string)($row['notes']??''))!=='' ,'notes_preview'=>$this->preview((string)($row['notes']??''),60),'created_at'=>$row['created_at']??null,'updated_at'=>$row['updated_at']??null];
    }
    private function toConversionDto(array $row): array
    {
        return ['id'=>(int)($row['id']??0),'converted_by_user_id'=>isset($row['converted_by_user_id'])?(int)$row['converted_by_user_id']:null,'company_id'=>isset($row['company_id'])?(int)$row['company_id']:null,'contact_id'=>isset($row['contact_id'])?(int)$row['contact_id']:null,'deal_id'=>isset($row['deal_id'])?(int)$row['deal_id']:null,'erp_customer_id'=>$this->preview((string)($row['erp_customer_id']??''),24),'conversion_type'=>(string)($row['conversion_type']??''),'conversion_value'=>isset($row['conversion_value'])?(float)$row['conversion_value']:null,'currency'=>(string)($row['currency']??''),'notes_present'=>trim((string)($row['notes']??''))!=='' ,'notes_preview'=>$this->preview((string)($row['notes']??''),60),'converted_at'=>$row['converted_at']??null];
    }
    private function toAttributionDto(array $row): array
    {
        return ['id'=>(int)($row['id']??0),'visit_id'=>isset($row['visit_id'])?(int)$row['visit_id']:null,'submission_id'=>isset($row['submission_id'])?(int)$row['submission_id']:null,'session_id'=>$this->preview((string)($row['session_id']??''),24),'utm_source'=>$this->preview((string)($row['utm_source']??''),40),'utm_medium'=>$this->preview((string)($row['utm_medium']??''),40),'utm_campaign'=>$this->preview((string)($row['utm_campaign']??''),60),'utm_term'=>$this->preview((string)($row['utm_term']??''),60),'utm_content'=>$this->preview((string)($row['utm_content']??''),60),'referrer_url_present'=>trim((string)($row['referrer_url']??''))!=='','referrer_url_preview'=>$this->sanitizeUrlPreview((string)($row['referrer_url']??'')),'landing_url_present'=>trim((string)($row['landing_url']??''))!=='','landing_url_preview'=>$this->sanitizeUrlPreview((string)($row['landing_url']??'')),'attributed_at'=>$row['attributed_at']??null];
    }
    private function toLandingSubmissionDto(array $row): array
    {
        return ['id'=>(int)($row['id']??0),'form_id'=>isset($row['form_id'])?(int)$row['form_id']:null,'campaign_id'=>isset($row['campaign_id'])?(int)$row['campaign_id']:null,'campaign_name'=>$this->preview((string)($row['campaign_name']??''),80),'visit_id'=>isset($row['visit_id'])?(int)$row['visit_id']:null,'crm_lead_id'=>isset($row['crm_lead_id'])?(int)$row['crm_lead_id']:null,'contact_name_present'=>trim((string)($row['contact_name']??''))!=='','contact_name_preview'=>$this->maskText(trim((string)($row['contact_name']??'')),2),'email_present'=>trim((string)($row['email']??''))!=='','email_preview'=>$this->maskEmail(trim((string)($row['email']??''))),'phone_present'=>trim((string)($row['phone']??''))!=='','phone_preview'=>$this->maskPhone(trim((string)($row['phone']??''))),'company_name_preview'=>$this->preview((string)($row['company_name']??''),60),'interest_preview'=>$this->preview((string)($row['interest']??''),80),'message_present'=>trim((string)($row['message']??''))!=='','message_preview'=>$this->preview((string)($row['message']??''),80),'status'=>(string)($row['status']??''),'submitted_at'=>$row['submitted_at']??null,'values_count'=>isset($row['values_count'])?(int)$row['values_count']:0,'url_clicks_count'=>isset($row['url_clicks_count'])?(int)$row['url_clicks_count']:0];
    }

    private function preview(string $value, int $max): ?string { $trim = trim($value); if ($trim === '') { return null; } $head = mb_substr($trim, 0, $max); return $head === $trim ? $head : $head . '…'; }
    private function sanitizeUrlPreview(string $url): ?string { $url = trim($url); if ($url === '') { return null; } $parts = parse_url($url); if (!is_array($parts)) { return $this->preview($url, 80); } $host = (string)($parts['host'] ?? ''); $path = (string)($parts['path'] ?? ''); $out = $host . $path; return $this->preview($out, 80); }
    private function maskText(string $value, int $visible): ?string { if ($value === '') { return null; } return mb_substr($value, 0, $visible) . '***'; }
    private function maskEmail(string $email): ?string { if ($email === '') { return null; } $atPos = strpos($email, '@'); if ($atPos === false) { return $this->maskText($email, 2); } $local = substr($email, 0, $atPos); $domain = substr($email, $atPos + 1); $domainPreview = explode('.', $domain)[0] ?? ''; return $this->maskText($local, 1) . '@' . $this->maskText($domainPreview, 1); }
    private function maskPhone(string $phone): ?string { if ($phone === '') { return null; } $digits = preg_replace('/\D+/', '', $phone) ?? ''; if ($digits === '') { return '***'; } return str_repeat('*', max(0, strlen($digits) - 2)) . substr($digits, -2); }
}
