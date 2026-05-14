<?php

declare(strict_types=1);

namespace App\Core\Crm;

use PDO;

final readonly class EcosistemaCrmLeadWriteRepository
{
    public function __construct(private PDO $pdo) {}

    public function findSubmissionForWrite(int $tenantId, int $submissionId): ?array
    {
        $sql = 'SELECT id,tenant_id,campaign_id,crm_lead_id,contact_name,email,phone,company_name,interest,message,status FROM landing_form_submissions WHERE tenant_id=:tenant_id AND id=:submission_id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':submission_id', $submissionId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function createLeadFromSubmission(int $tenantId, array $data): int
    {
        $sql = 'INSERT INTO crm_leads (tenant_id,source_id,owner_user_id,company_name,contact_name,email,phone,interest,status,notes,created_at,updated_at)
                VALUES (:tenant_id,:source_id,:owner_user_id,:company_name,:contact_name,:email,:phone,:interest,:status,:notes,NOW(),NOW())';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':source_id', $data['source_id'], $data['source_id'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':owner_user_id', $data['owner_user_id'], $data['owner_user_id'] === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':company_name', $data['company_name']);
        $stmt->bindValue(':contact_name', $data['contact_name']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':phone', $data['phone']);
        $stmt->bindValue(':interest', $data['interest']);
        $stmt->bindValue(':status', $data['status']);
        $stmt->bindValue(':notes', $data['notes']);
        $stmt->execute();

        return (int) $this->pdo->lastInsertId();
    }

    public function linkLeadToCampaign(int $tenantId, int $campaignId, int $leadId, int $userId): ?int
    {
        $exists = $this->pdo->prepare('SELECT id FROM crm_campaign_leads WHERE tenant_id=:tenant_id AND campaign_id=:campaign_id AND lead_id=:lead_id LIMIT 1');
        $exists->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $exists->bindValue(':campaign_id', $campaignId, PDO::PARAM_INT);
        $exists->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $exists->execute();
        $existing = $exists->fetchColumn();
        if ($existing !== false) {
            return (int) $existing;
        }

        $sql = 'INSERT INTO crm_campaign_leads (tenant_id,campaign_id,lead_id,funnel_stage_id,assigned_user_id,status,temperature,score,first_touch_at,last_touch_at,next_followup_at,notes,created_at,updated_at)
                VALUES (:tenant_id,:campaign_id,:lead_id,NULL,:assigned_user_id,:status,NULL,NULL,NOW(),NOW(),NULL,:notes,NOW(),NOW())';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':campaign_id', $campaignId, PDO::PARAM_INT);
        $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $stmt->bindValue(':assigned_user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':status', 'new');
        $stmt->bindValue(':notes', 'auto-link from landing submission');
        $stmt->execute();

        return (int) $this->pdo->lastInsertId();
    }

    public function markSubmissionProcessed(int $tenantId, int $submissionId, int $leadId): bool
    {
        $stmt = $this->pdo->prepare('UPDATE landing_form_submissions SET crm_lead_id=:crm_lead_id,processed_at=NOW(),updated_at=NOW() WHERE tenant_id=:tenant_id AND id=:submission_id');
        $stmt->bindValue(':crm_lead_id', $leadId, PDO::PARAM_INT);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':submission_id', $submissionId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function countDuplicateLeads(int $tenantId, string $email, string $phoneDigits): int
    {
        if ($email === '' && $phoneDigits === '') { return 0; }
        $clauses = [];
        if ($email !== '') { $clauses[] = 'LOWER(email)=LOWER(:email)'; }
        if ($phoneDigits !== '') { $clauses[] = 'REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone, " ", ""), "-", ""), "(", ""), ")", ""), "+", "")=:phone_digits'; }
        $sql = 'SELECT COUNT(*) FROM crm_leads WHERE tenant_id=:tenant_id AND (' . implode(' OR ', $clauses) . ')';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        if ($email !== '') { $stmt->bindValue(':email', $email); }
        if ($phoneDigits !== '') { $stmt->bindValue(':phone_digits', $phoneDigits); }
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function beginTransaction(): void { if (!$this->pdo->inTransaction()) { $this->pdo->beginTransaction(); } }
    public function commit(): void { if ($this->pdo->inTransaction()) { $this->pdo->commit(); } }
    public function rollBack(): void { if ($this->pdo->inTransaction()) { $this->pdo->rollBack(); } }
}
