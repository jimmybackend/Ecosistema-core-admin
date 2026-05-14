<?php

declare(strict_types=1);

namespace App\Core\Attribution;

final readonly class EcosistemaUrlLandingAttributionService
{
    public function __construct(private EcosistemaUrlLandingAttributionRepository $repository, private array $config = []) {}

    public function dryRun(int $tenantId, int $clickId): array
    {
        $result = ['mode'=>'dry-run','enabled'=>(bool)($this->config['enabled']??false),'write_enabled'=>(bool)($this->config['write_enabled']??false),'db_write'=>false,'tenant_from_session'=>true,'click_id'=>$clickId,'eligible'=>false,'blocked_reason'=>null,'match_summary'=>['visits'=>0,'sessions'=>0],'click'=>null,'visit_candidates'=>[],'session_candidates'=>[],'warnings'=>[]];
        if (!$result['enabled']) { $result['blocked_reason'] = 'feature_disabled'; return $result; }
        if ($tenantId <= 0 || $clickId <= 0) { $result['blocked_reason'] = 'invalid_request'; return $result; }

        $click = $this->repository->findClickById($tenantId, $clickId);
        if ($click === null) { $result['blocked_reason'] = 'click_not_found'; return $result; }
        if ((int)($click['short_link_id']??0) <= 0 || (int)($click['landing_page_id']??0) <= 0 || (int)($click['campaign_id']??0) <= 0 || trim((string)($click['visitor_uuid']??'')) === '') {
            $result['blocked_reason'] = 'click_missing_required_fields';
            return $result;
        }

        $visits = $this->repository->findVisitCandidates($tenantId, $click);
        $sessions = $this->repository->findSessionCandidates($tenantId, (string)$click['visitor_uuid']);
        $result['click'] = $this->safeClick($click);
        $result['visit_candidates'] = array_map(fn(array $r): array => $this->safeVisit($r), $visits);
        $result['session_candidates'] = array_map(fn(array $r): array => $this->safeSession($r), $sessions);
        $result['match_summary'] = ['visits'=>count($visits),'sessions'=>count($sessions)];
        $result['eligible'] = $result['match_summary']['visits'] > 0 || $result['match_summary']['sessions'] > 0;
        if (!$result['eligible']) { $result['warnings'][] = 'no_potential_relations'; }
        return $result;
    }

    private function safeClick(array $row): array { return ['id'=>(int)$row['id'],'short_link_id'=>(int)$row['short_link_id'],'landing_page_id'=>(int)$row['landing_page_id'],'campaign_id'=>(int)$row['campaign_id'],'visitor_uuid_present'=>trim((string)($row['visitor_uuid']??''))!=='','clicked_at'=>(string)($row['clicked_at']??''),'ip_preview'=>$this->preview((string)($row['ip_address']??''),8),'user_agent_preview'=>$this->preview((string)($row['user_agent']??''),36),'referer_preview'=>$this->preview((string)($row['referer']??''),48),'clicked_url_preview'=>$this->preview((string)($row['clicked_url']??''),48)]; }
    private function safeVisit(array $row): array { return ['id'=>(int)$row['id'],'landing_page_id'=>(int)$row['landing_page_id'],'campaign_id'=>(int)$row['campaign_id'],'short_link_id'=>(int)$row['short_link_id'],'session_uuid_present'=>trim((string)($row['session_uuid']??''))!=='','visited_at'=>(string)($row['visited_at']??''),'ip_preview'=>$this->preview((string)($row['ip_address']??''),8),'user_agent_preview'=>$this->preview((string)($row['user_agent']??''),36),'referer_preview'=>$this->preview((string)($row['referer']??''),48),'full_url_preview'=>$this->preview((string)($row['full_url']??''),48)]; }
    private function safeSession(array $row): array { return ['id'=>(int)$row['id'],'browser_session_uuid_present'=>trim((string)($row['browser_session_uuid']??''))!=='','started_at'=>(string)($row['started_at']??''),'last_activity_at'=>(string)($row['last_activity_at']??''),'entry_url_preview'=>$this->preview((string)($row['entry_url']??''),48),'referrer_preview'=>$this->preview((string)($row['referrer_url']??''),48),'ip_preview'=>$this->preview((string)($row['ip_address']??''),8),'user_agent_preview'=>$this->preview((string)($row['user_agent']??''),36)]; }
    private function preview(string $value, int $max): string { $trim=trim($value); if($trim===''){return '';} return mb_substr($trim,0,$max).'…'; }
}
