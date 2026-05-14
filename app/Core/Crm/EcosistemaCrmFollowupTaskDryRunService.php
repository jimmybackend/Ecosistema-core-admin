<?php

declare(strict_types=1);

namespace App\Core\Crm;

final readonly class EcosistemaCrmFollowupTaskDryRunService
{
    public function __construct(private EcosistemaCrmFollowupTaskDryRunRepository $repository, private array $config = []) {}

    public function evaluate(int $tenantId, int $leadId, int $createdByUserId, array $input): array
    {
        $enabled = (bool) ($this->config['followup_task_dry_run'] ?? false);
        $result = ['mode'=>'dry-run','enabled'=>$enabled,'db_write'=>false,'would_create_task'=>false,'tenant_from_session'=>true,'blocked_reasons'=>[],'warnings'=>[],'lead'=>null,'task_preview'=>null];

        if (!$enabled) { $result['blocked_reasons'][] = 'feature_disabled'; return $result; }
        if ($tenantId <= 0 || $leadId <= 0 || $createdByUserId <= 0) { $result['blocked_reasons'][] = 'invalid_context'; return $result; }

        $lead = $this->repository->findLead($tenantId, $leadId);
        if ($lead === null) { $result['blocked_reasons'][] = 'lead_not_found'; return $result; }

        $assignedUserId = (int) ($input['assigned_user_id'] ?? 0);
        $title = trim((string) ($input['title'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $dueAt = trim((string) ($input['due_at'] ?? ''));
        $priority = strtolower(trim((string) ($input['priority'] ?? '')));

        if ($assignedUserId <= 0) { $result['blocked_reasons'][] = 'invalid_assigned_user_id'; }
        elseif (!$this->repository->userExists($tenantId, $assignedUserId)) { $result['blocked_reasons'][] = 'assigned_user_not_found'; }
        if ($title === '') { $result['blocked_reasons'][] = 'title_required'; }
        if (!$this->isValidDatetime($dueAt)) { $result['blocked_reasons'][] = 'invalid_due_at'; }
        if (!in_array($priority, ['low','medium','high'], true)) { $result['blocked_reasons'][] = 'invalid_priority'; }

        $result['lead'] = ['id'=>(int)$lead['id'],'status'=>(string)($lead['status'] ?? ''),'company_name_preview'=>$this->preview((string)($lead['company_name'] ?? ''), 50),'contact_name_preview'=>$this->mask((string)($lead['contact_name'] ?? ''))];
        $result['task_preview'] = ['lead_id'=>$leadId,'assigned_user_id'=>$assignedUserId > 0 ? $assignedUserId : null,'created_by_user_id'=>$createdByUserId,'title_preview'=>$this->preview($title, 80),'description_present'=>$description !== '','description_preview'=>$this->preview($description, 120),'due_at'=>$dueAt !== '' ? $dueAt : null,'priority'=>$priority !== '' ? $priority : null,'status'=>'pending'];
        $result['would_create_task'] = $result['blocked_reasons'] === [];
        if ($description !== '' && mb_strlen($description) > 500) { $result['warnings'][] = 'description_truncated_preview'; }

        return $result;
    }

    private function isValidDatetime(string $value): bool
    {
        if ($value === '') { return false; }
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value);
        return $dt !== false;
    }
    private function preview(string $value, int $max): ?string { $v = trim($value); if ($v === '') return null; $h = mb_substr($v,0,$max); return $h === $v ? $h : ($h.'…'); }
    private function mask(string $value): ?string { $v = trim($value); if ($v==='') return null; return mb_substr($v,0,2).'***'; }
}
