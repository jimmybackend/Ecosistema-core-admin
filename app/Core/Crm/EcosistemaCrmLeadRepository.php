<?php

declare(strict_types=1);

namespace App\Core\Crm;

use PDO;

final readonly class EcosistemaCrmLeadRepository
{
    public function __construct(private PDO $pdo) {}

    public function listLeads(int $tenantId, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare('SELECT id,tenant_id,source_id,owner_user_id,company_name,contact_name,email,phone,interest,status,notes,created_at,updated_at FROM crm_leads WHERE tenant_id=:tenant_id ORDER BY updated_at DESC,id DESC LIMIT :limit');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', max(1, min(200, $limit)), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function summarizeLeads(int $tenantId): array
    {
        $summary = ['total' => 0, 'by_status' => []];

        $total = $this->pdo->prepare('SELECT COUNT(*) FROM crm_leads WHERE tenant_id=:tenant_id');
        $total->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $total->execute();
        $summary['total'] = (int) $total->fetchColumn();

        $group = $this->pdo->prepare('SELECT status,COUNT(*) AS total FROM crm_leads WHERE tenant_id=:tenant_id GROUP BY status ORDER BY status ASC');
        $group->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $group->execute();

        foreach ($group->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $summary['by_status'][] = [
                'status' => (string) ($row['status'] ?? ''),
                'total' => (int) ($row['total'] ?? 0),
            ];
        }

        return $summary;
    }
}
