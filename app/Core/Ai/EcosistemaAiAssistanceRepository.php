<?php

declare(strict_types=1);

namespace App\Core\Ai;

use PDO;

final readonly class EcosistemaAiAssistanceRepository
{
    public function __construct(private PDO $pdo) {}

    public function findLeadContext(int $tenantId, int $leadId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id,tenant_id,company_name,contact_name,email,phone,interest,status,notes FROM crm_leads WHERE tenant_id=:tenant_id AND id=:lead_id LIMIT 1');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function insertProposal(int $tenantId, int $userId, array $proposal): ?int
    {
        $sql = 'INSERT INTO os_ai_proposals (boot_id,tenant_id,user_id,created_unix,proposal_type,summary,rationale,risk_level,benefit_level,requires_human_confirmation,status,created_at,updated_at)
                VALUES (:boot_id,:tenant_id,:user_id,:created_unix,:proposal_type,:summary,:rationale,:risk_level,:benefit_level,:requires_human_confirmation,:status,NOW(),NOW())';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':boot_id', (int) ($proposal['boot_id'] ?? time()), PDO::PARAM_INT);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':created_unix', time(), PDO::PARAM_INT);
        $stmt->bindValue(':proposal_type', (string) ($proposal['proposal_type'] ?? 'assist_summary'));
        $stmt->bindValue(':summary', (string) ($proposal['summary'] ?? ''));
        $stmt->bindValue(':rationale', (string) ($proposal['rationale'] ?? ''));
        $stmt->bindValue(':risk_level', (string) ($proposal['risk_level'] ?? 'unknown'));
        $stmt->bindValue(':benefit_level', (string) ($proposal['benefit_level'] ?? 'unknown'));
        $stmt->bindValue(':requires_human_confirmation', (int) ($proposal['requires_human_confirmation'] ?? 1), PDO::PARAM_INT);
        $stmt->bindValue(':status', (string) ($proposal['status'] ?? 'pending_review'));
        if (!$stmt->execute()) {
            return null;
        }
        return (int) $this->pdo->lastInsertId();
    }
}
