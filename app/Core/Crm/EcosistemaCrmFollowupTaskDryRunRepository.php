<?php

declare(strict_types=1);

namespace App\Core\Crm;

use PDO;

final readonly class EcosistemaCrmFollowupTaskDryRunRepository
{
    public function __construct(private PDO $pdo) {}

    public function findLead(int $tenantId, int $leadId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id,tenant_id,owner_user_id,company_name,contact_name,status FROM crm_leads WHERE tenant_id=:tenant_id AND id=:lead_id LIMIT 1');
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
}
