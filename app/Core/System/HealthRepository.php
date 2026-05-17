<?php
declare(strict_types=1);

namespace App\Core\System;

use PDO;

final readonly class HealthRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listDefinitionsWithLastResult(int $tenantId, int $limit = 100): array
    {
        $sql = "SELECT d.id, d.check_code, d.name, d.description, d.module_code, d.check_type, d.severity, d.expected_signal, d.is_active, d.created_at, d.updated_at, r.status AS last_result_status, r.finished_at AS last_checked_at, r.message AS last_result_message, r.measured_value AS last_measured_value FROM system_health_check_definitions d LEFT JOIN system_health_check_runs r ON r.id = (SELECT r2.id FROM system_health_check_runs r2 WHERE r2.check_definition_id = d.id AND (r2.tenant_id = :tenant_id OR r2.tenant_id IS NULL) ORDER BY r2.created_at DESC, r2.id DESC LIMIT 1) WHERE d.is_active = 1 ORDER BY d.id DESC LIMIT :limit";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findDefinitionById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, check_code, name, description, module_code, check_type, severity, expected_signal, is_active, check_sql, remediation_hint, created_at, updated_at FROM system_health_check_definitions WHERE id = :id AND is_active = 1 LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }
}
