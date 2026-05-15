<?php

declare(strict_types=1);

namespace App\Core\Campaigns;

use PDO;

final readonly class EcosistemaCampaignCreationRepository
{
    public function __construct(private PDO $pdo) {}

    public function createCampaign(int $tenantId, array $data): int
    {
        $sql = 'INSERT INTO crm_marketing_campaigns (
                    tenant_id,channel_id,owner_user_id,name,code,description,campaign_type,objective,status,budget,currency,starts_at,ends_at,landing_url,source_module,source_table,source_id,created_at,updated_at
                ) VALUES (
                    :tenant_id,:channel_id,:owner_user_id,:name,:code,:description,:campaign_type,:objective,:status,:budget,:currency,:starts_at,:ends_at,:landing_url,:source_module,:source_table,:source_id,NOW(),NOW()
                )';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':channel_id', null, PDO::PARAM_NULL);
        $stmt->bindValue(':owner_user_id', $data['owner_user_id'], PDO::PARAM_INT);
        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':code', $data['code']);
        $stmt->bindValue(':description', $data['description']);
        $stmt->bindValue(':campaign_type', $data['campaign_type']);
        $stmt->bindValue(':objective', $data['objective']);
        $stmt->bindValue(':status', 'draft');
        $stmt->bindValue(':budget', $data['budget'], $data['budget'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':currency', $data['currency'], $data['currency'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':starts_at', $data['starts_at'], $data['starts_at'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':ends_at', $data['ends_at'], $data['ends_at'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':landing_url', null, PDO::PARAM_NULL);
        $stmt->bindValue(':source_module', 'campaigns');
        $stmt->bindValue(':source_table', null, PDO::PARAM_NULL);
        $stmt->bindValue(':source_id', null, PDO::PARAM_NULL);
        $stmt->execute();

        return (int) $this->pdo->lastInsertId();
    }

    public function beginTransaction(): void { if (!$this->pdo->inTransaction()) { $this->pdo->beginTransaction(); } }
    public function commit(): void { if ($this->pdo->inTransaction()) { $this->pdo->commit(); } }
    public function rollBack(): void { if ($this->pdo->inTransaction()) { $this->pdo->rollBack(); } }
}
