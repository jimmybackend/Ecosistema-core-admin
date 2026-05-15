<?php

declare(strict_types=1);

namespace App\Core\Workflow;

use PDO;

final readonly class EcosistemaWorkflowTemplateInstallRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function insertRule(int $tenantId, int $userId, array $rule): int
    {
        $sql = 'INSERT INTO workflow_rules (tenant_id, name, description, trigger_module, trigger_event, conditions_json, is_active, created_by_user_id, created_at, updated_at)
                VALUES (:tenant_id, :name, :description, :trigger_module, :trigger_event, :conditions_json, 0, :created_by_user_id, NOW(), NOW())';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenantId,
            ':name' => (string) ($rule['name'] ?? ''),
            ':description' => (string) ($rule['description'] ?? ''),
            ':trigger_module' => (string) ($rule['trigger_module'] ?? ''),
            ':trigger_event' => (string) ($rule['trigger_event'] ?? ''),
            ':conditions_json' => null,
            ':created_by_user_id' => $userId,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function insertAction(int $tenantId, int $ruleId, array $action): int
    {
        $sql = 'INSERT INTO workflow_actions (tenant_id, rule_id, sort_order, action_type, target_module, config_json, is_active, created_at)
                VALUES (:tenant_id, :rule_id, :sort_order, :action_type, :target_module, :config_json, 0, NOW())';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenantId,
            ':rule_id' => $ruleId,
            ':sort_order' => max(1, (int) ($action['sort_order'] ?? 1)),
            ':action_type' => (string) ($action['action_type'] ?? 'custom'),
            ':target_module' => (string) ($action['target_module'] ?? 'workflow'),
            ':config_json' => null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
