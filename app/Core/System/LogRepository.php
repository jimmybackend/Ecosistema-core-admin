<?php
declare(strict_types=1);

namespace App\Core\System;

use PDO;

final readonly class LogRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listRecent(int $tenantId, int $limit = 100): array
    {
        $stmt = $this->pdo->prepare('SELECT id, tenant_id, user_id, level, module_code, channel, message, context_json, ip_address, user_agent, created_at FROM system_logs WHERE tenant_id = :tenant_id ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function insert(array $data): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO system_logs (tenant_id, user_id, level, module_code, channel, message, context_json, ip_address, user_agent) VALUES (:tenant_id, :user_id, :level, :module_code, :channel, :message, :context_json, :ip_address, :user_agent)');
        return $stmt->execute($data);
    }
}
