<?php

declare(strict_types=1);

namespace App\Core\System;

use PDO;

final readonly class AuditLogger
{
    public function __construct(private PDO $pdo)
    {
    }

    public function log(array $data): void
    {
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO core_audit (tenant_id, user_id, entity_type, entity_id, action, before_data, after_data, ip_address, user_agent)
                 VALUES (:tenant_id, :user_id, :entity_type, :entity_id, :action, :before_data, :after_data, :ip_address, :user_agent)'
            );
            $stmt->execute([
                ':tenant_id' => $data['tenant_id'] ?? null,
                ':user_id' => $data['user_id'] ?? null,
                ':entity_type' => $data['entity_type'] ?? null,
                ':entity_id' => $data['entity_id'] ?? null,
                ':action' => $data['action'] ?? null,
                ':before_data' => $this->encode($data['old_values'] ?? null),
                ':after_data' => $this->encode($data['new_values'] ?? null),
                ':ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
        } catch (\Throwable) {
            // no-op: auditoría no debe romper flujo principal
        }
    }

    private function encode(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return is_string($json) ? $json : null;
    }
}
