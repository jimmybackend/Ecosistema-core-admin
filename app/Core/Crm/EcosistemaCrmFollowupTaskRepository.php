<?php

declare(strict_types=1);

namespace App\Core\Crm;

use PDO;

final readonly class EcosistemaCrmFollowupTaskRepository
{
    public function __construct(private PDO $pdo) {}

    public function findLead(int $tenantId, int $leadId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id,tenant_id,owner_user_id,status FROM crm_leads WHERE tenant_id=:tenant_id AND id=:lead_id LIMIT 1');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':lead_id', $leadId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function userExists(int $tenantId, int $userId): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM core_users WHERE tenant_id=:tenant_id AND id=:user_id LIMIT 1');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }

    public function createTask(int $tenantId, array $payload): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO crm_tasks (tenant_id,assigned_user_id,created_by_user_id,lead_id,title,description,due_at,priority,status,created_at,updated_at) VALUES (:tenant_id,:assigned_user_id,:created_by_user_id,:lead_id,:title,:description,:due_at,:priority,:status,NOW(),NOW())');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':assigned_user_id', (int) $payload['assigned_user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':created_by_user_id', (int) $payload['created_by_user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':lead_id', (int) $payload['lead_id'], PDO::PARAM_INT);
        $stmt->bindValue(':title', (string) $payload['title'], PDO::PARAM_STR);
        $stmt->bindValue(':description', (string) $payload['description'], PDO::PARAM_STR);
        $stmt->bindValue(':due_at', (string) $payload['due_at'], PDO::PARAM_STR);
        $stmt->bindValue(':priority', (string) $payload['priority'], PDO::PARAM_STR);
        $stmt->bindValue(':status', 'pending', PDO::PARAM_STR);
        $stmt->execute();

        return (int) $this->pdo->lastInsertId();
    }
}
