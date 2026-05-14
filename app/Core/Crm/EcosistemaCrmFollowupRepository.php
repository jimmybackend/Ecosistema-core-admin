<?php

declare(strict_types=1);

namespace App\Core\Crm;

use PDO;

final readonly class EcosistemaCrmFollowupRepository
{
    public function __construct(private PDO $pdo) {}

    public function listFollowups(int $tenantId, int $limit = 100): array
    {
        $safeLimit = max(1, min(200, $limit));

        $tasks = $this->pdo->prepare('SELECT id,lead_id,assigned_user_id,title,description,due_at,priority,status,created_at,updated_at FROM crm_tasks WHERE tenant_id=:tenant_id ORDER BY updated_at DESC,id DESC LIMIT :limit');
        $tasks->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $tasks->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $tasks->execute();

        $followups = $this->pdo->prepare('SELECT id,contact_id,company_id,deal_id,assigned_user_id,followup_type,status,scheduled_at,completed_at,result_notes,agenda_event_id,created_at,updated_at FROM crm_customer_followups WHERE tenant_id=:tenant_id ORDER BY updated_at DESC,id DESC LIMIT :limit');
        $followups->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $followups->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $followups->execute();

        $events = $this->pdo->prepare('SELECT id,owner_user_id,created_by_user_id,title,description,location,event_type,status,priority,starts_at,ends_at,source_module,source_table,source_id,created_at,updated_at FROM agenda_events WHERE tenant_id=:tenant_id AND source_table=:source_table ORDER BY updated_at DESC,id DESC LIMIT :limit');
        $events->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $events->bindValue(':source_table', 'crm_leads');
        $events->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $events->execute();

        return ['tasks' => $tasks->fetchAll(PDO::FETCH_ASSOC) ?: [], 'followups' => $followups->fetchAll(PDO::FETCH_ASSOC) ?: [], 'events' => $events->fetchAll(PDO::FETCH_ASSOC) ?: []];
    }

    public function listFollowupsForLead(int $tenantId, int $leadId, int $limit = 100): array
    {
        $safeLimit = max(1, min(200, $limit));

        $tasks = $this->pdo->prepare('SELECT id,lead_id,assigned_user_id,title,description,due_at,priority,status,created_at,updated_at FROM crm_tasks WHERE tenant_id=:tenant_id AND lead_id=:lead_id ORDER BY updated_at DESC,id DESC LIMIT :limit');
        $tasks->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $tasks->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $tasks->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $tasks->execute();

        $followups = $this->pdo->prepare('SELECT f.id,f.contact_id,f.company_id,f.deal_id,f.assigned_user_id,f.followup_type,f.status,f.scheduled_at,f.completed_at,f.result_notes,f.agenda_event_id,f.created_at,f.updated_at FROM crm_customer_followups f INNER JOIN crm_tasks t ON t.tenant_id=f.tenant_id AND t.contact_id=f.contact_id WHERE f.tenant_id=:tenant_id AND t.lead_id=:lead_id ORDER BY f.updated_at DESC,f.id DESC LIMIT :limit');
        $followups->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $followups->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $followups->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $followups->execute();

        $events = $this->pdo->prepare('SELECT id,owner_user_id,created_by_user_id,title,description,location,event_type,status,priority,starts_at,ends_at,source_module,source_table,source_id,created_at,updated_at FROM agenda_events WHERE tenant_id=:tenant_id AND source_table=:source_table AND source_id=:lead_id ORDER BY updated_at DESC,id DESC LIMIT :limit');
        $events->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $events->bindValue(':source_table', 'crm_leads');
        $events->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $events->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $events->execute();

        return ['tasks' => $tasks->fetchAll(PDO::FETCH_ASSOC) ?: [], 'followups' => $followups->fetchAll(PDO::FETCH_ASSOC) ?: [], 'events' => $events->fetchAll(PDO::FETCH_ASSOC) ?: []];
    }
}
