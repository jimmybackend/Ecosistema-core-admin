<?php

declare(strict_types=1);

namespace App\Core\Crm;

final readonly class EcosistemaCrmFollowupService
{
    public function __construct(private EcosistemaCrmFollowupRepository $repository, private EcosistemaCrmAdapter $adapter) {}

    public function listFollowups(int $tenantId): array
    {
        $rows = $this->repository->listFollowups($tenantId);
        return $this->toResponse($rows);
    }

    public function listFollowupsForLead(int $tenantId, int $leadId): array
    {
        $rows = $this->repository->listFollowupsForLead($tenantId, $leadId);
        return $this->toResponse($rows);
    }

    private function toResponse(array $rows): array
    {
        return [
            'tasks' => array_map(fn(array $row): array => $this->toTaskDto($row), (array)($rows['tasks'] ?? [])),
            'followups' => array_map(fn(array $row): array => $this->toFollowupDto($row), (array)($rows['followups'] ?? [])),
            'events' => array_map(fn(array $row): array => $this->toEventDto($row), (array)($rows['events'] ?? [])),
            'mode' => 'read-only',
            'db_write' => false,
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    private function toTaskDto(array $row): array { return ['id'=>(int)($row['id']??0),'lead_id'=>isset($row['lead_id'])?(int)$row['lead_id']:null,'assigned_user_id'=>isset($row['assigned_user_id'])?(int)$row['assigned_user_id']:null,'title_preview'=>$this->preview((string)($row['title']??''),80),'description_present'=>trim((string)($row['description']??''))!=='','description_preview'=>$this->preview((string)($row['description']??''),80),'due_at'=>$row['due_at']??null,'priority'=>(string)($row['priority']??''),'status'=>(string)($row['status']??''),'created_at'=>$row['created_at']??null,'updated_at'=>$row['updated_at']??null]; }
    private function toFollowupDto(array $row): array { return ['id'=>(int)($row['id']??0),'contact_id'=>isset($row['contact_id'])?(int)$row['contact_id']:null,'company_id'=>isset($row['company_id'])?(int)$row['company_id']:null,'deal_id'=>isset($row['deal_id'])?(int)$row['deal_id']:null,'assigned_user_id'=>isset($row['assigned_user_id'])?(int)$row['assigned_user_id']:null,'followup_type'=>(string)($row['followup_type']??''),'status'=>(string)($row['status']??''),'scheduled_at'=>$row['scheduled_at']??null,'completed_at'=>$row['completed_at']??null,'result_notes_present'=>trim((string)($row['result_notes']??''))!=='','result_notes_preview'=>$this->preview((string)($row['result_notes']??''),80),'agenda_event_id'=>isset($row['agenda_event_id'])?(int)$row['agenda_event_id']:null,'created_at'=>$row['created_at']??null,'updated_at'=>$row['updated_at']??null]; }
    private function toEventDto(array $row): array { return ['id'=>(int)($row['id']??0),'owner_user_id'=>isset($row['owner_user_id'])?(int)$row['owner_user_id']:null,'created_by_user_id'=>isset($row['created_by_user_id'])?(int)$row['created_by_user_id']:null,'title_preview'=>$this->preview((string)($row['title']??''),80),'description_present'=>trim((string)($row['description']??''))!=='','description_preview'=>$this->preview((string)($row['description']??''),80),'location_preview'=>$this->preview((string)($row['location']??''),60),'event_type'=>(string)($row['event_type']??''),'status'=>(string)($row['status']??''),'priority'=>(string)($row['priority']??''),'starts_at'=>$row['starts_at']??null,'ends_at'=>$row['ends_at']??null,'source_module'=>(string)($row['source_module']??''),'source_table'=>(string)($row['source_table']??''),'source_id'=>isset($row['source_id'])?(int)$row['source_id']:null,'created_at'=>$row['created_at']??null,'updated_at'=>$row['updated_at']??null]; }
    private function preview(string $value, int $max): ?string { $trim = trim($value); if ($trim === '') { return null; } $head = mb_substr($trim, 0, $max); return $head === $trim ? $head : $head . '…'; }
}
