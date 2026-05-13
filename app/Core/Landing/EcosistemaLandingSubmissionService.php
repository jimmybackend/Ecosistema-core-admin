<?php

declare(strict_types=1);

namespace App\Core\Landing;

final readonly class EcosistemaLandingSubmissionService
{
    public function __construct(private EcosistemaLandingSubmissionRepository $repository, private EcosistemaLandingAdapter $adapter)
    {
    }

    public function listRecentSubmissions(int $tenantId, int $limit = 100): array
    {
        return ['summary' => $this->repository->summarizeSubmissions($tenantId), 'submissions' => array_map(fn(array $r): array => $this->toSubmissionDto($r), $this->repository->listRecentSubmissions($tenantId, $limit)), 'capabilities' => $this->adapter->capabilities()];
    }
    public function listSubmissionsForForm(int $tenantId, int $formId, int $limit = 100): array
    {
        return ['summary' => $this->repository->summarizeSubmissions($tenantId), 'submissions' => array_map(fn(array $r): array => $this->toSubmissionDto($r), $this->repository->listSubmissionsForForm($tenantId, $formId, $limit)), 'capabilities' => $this->adapter->capabilities()];
    }
    public function listSubmissionsForPage(int $tenantId, int $pageId, int $limit = 100): array
    {
        return ['summary' => $this->repository->summarizeSubmissions($tenantId), 'submissions' => array_map(fn(array $r): array => $this->toSubmissionDto($r), $this->repository->listSubmissionsForPage($tenantId, $pageId, $limit)), 'capabilities' => $this->adapter->capabilities()];
    }
    public function getSubmissionDetail(int $tenantId, int $submissionId): ?array
    {
        $submission = $this->repository->findSubmission($tenantId, $submissionId);
        if ($submission === null) { return null; }
        return ['submission' => $this->toSubmissionDto($submission), 'values' => array_map(fn(array $r): array => $this->toValueDto($r), $this->repository->listSubmissionValues($tenantId, $submissionId)), 'capabilities' => $this->adapter->capabilities()];
    }

    private function toSubmissionDto(array $r): array
    {
        $geoPresent = trim((string) ($r['country'] ?? '')) !== '' || trim((string) ($r['region'] ?? '')) !== '' || trim((string) ($r['city'] ?? '')) !== '';
        return ['id'=>(int)($r['id']??0),'form_id'=>isset($r['form_id'])?(int)$r['form_id']:null,'form_name'=>(string)($r['form_name']??''),'landing_page_id'=>isset($r['landing_page_id'])?(int)$r['landing_page_id']:null,'landing_page_title'=>(string)($r['landing_page_title']??''),'campaign_id'=>isset($r['campaign_id'])?(int)$r['campaign_id']:null,'campaign_name'=>(string)($r['campaign_name']??''),'visit_id'=>isset($r['visit_id'])?(int)$r['visit_id']:null,'crm_lead_id'=>isset($r['crm_lead_id'])?(int)$r['crm_lead_id']:null,'submitted_by_user_id'=>isset($r['submitted_by_user_id'])?(int)$r['submitted_by_user_id']:null,'contact_name_present'=>$this->has($r['contact_name']??null),'contact_name_preview'=>$this->preview((string)($r['contact_name']??''),20),'email_present'=>$this->has($r['email']??null),'email_preview'=>$this->maskEmail((string)($r['email']??'')),'phone_present'=>$this->has($r['phone']??null),'phone_preview'=>$this->maskPhone((string)($r['phone']??'')),'company_name_preview'=>$this->preview((string)($r['company_name']??''),20),'interest_preview'=>$this->preview((string)($r['interest']??''),20),'message_present'=>$this->has($r['message']??null),'message_preview'=>$this->preview((string)($r['message']??''),60),'raw_data_json_present'=>$this->has($r['raw_data_json']??null),'raw_data_json_exposed'=>false,'ip_address_present'=>$this->has($r['ip_address']??null),'ip_address_preview'=>$this->maskIp((string)($r['ip_address']??'')),'ip_address_exposed'=>false,'user_agent_present'=>$this->has($r['user_agent']??null),'user_agent_preview'=>$this->preview((string)($r['user_agent']??''),24),'geo_present'=>$geoPresent,'country'=>(string)($r['country']??''),'region'=>(string)($r['region']??''),'city'=>(string)($r['city']??''),'coordinates_present'=>$this->has($r['latitude']??null)||$this->has($r['longitude']??null),'coordinates_exposed'=>false,'status'=>(string)($r['status']??''),'spam_score'=>isset($r['spam_score'])?(float)$r['spam_score']:null,'submitted_at'=>$r['submitted_at']??null,'processed_at'=>$r['processed_at']??null,'mode'=>'read-only','db_write'=>false,'crm_lead_write'=>false,'file_download'=>false];
    }
    private function toValueDto(array $r): array
    {
        return ['id'=>(int)($r['id']??0),'submission_id'=>(int)($r['submission_id']??0),'field_id'=>isset($r['field_id'])?(int)$r['field_id']:null,'field_key'=>(string)($r['field_key']??''),'field_label'=>(string)($r['field_label']??''),'value_text_present'=>$this->has($r['value_text']??null),'value_text_preview'=>$this->preview((string)($r['value_text']??''),64),'value_text_exposed'=>false,'value_json_present'=>$this->has($r['value_json']??null),'value_json_exposed'=>false,'file_path_present'=>$this->has($r['file_path']??null),'file_path_exposed'=>false,'s3_key_present'=>$this->has($r['s3_key']??null),'s3_key_exposed'=>false,'created_at'=>$r['created_at']??null];
    }
    private function has(mixed $v): bool { return trim((string)$v) !== ''; }
    private function preview(string $v, int $max): ?string { $t=trim($v); if($t===''){return null;} $h=mb_substr($t,0,$max); return $h===$t?$h:$h.'…'; }
    private function maskEmail(string $email): ?string { $e=trim($email); if($e===''||!str_contains($e,'@')) return $this->preview($e,4); [$l,$d]=explode('@',$e,2); return mb_substr($l,0,1).'***@'.$d; }
    private function maskPhone(string $phone): ?string { $d=preg_replace('/\D+/','', $phone) ?? ''; if($d==='') return null; return '***'.substr($d,-4); }
    private function maskIp(string $ip): ?string { $ip=trim($ip); if($ip==='') return null; if(str_contains($ip, ':')) { return preg_replace('/:[0-9a-f]{1,4}$/i', ':****', $ip) ?: 'hidden'; } $parts=explode('.',$ip); if(count($parts)!==4) return 'hidden'; return $parts[0].'.'.$parts[1].'.*.*'; }
}
