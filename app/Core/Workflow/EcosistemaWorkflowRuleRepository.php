<?php

declare(strict_types=1);

namespace App\Core\Workflow;

use PDO;

final readonly class EcosistemaWorkflowRuleRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listRules(int $tenantId, int $limit = 100): array
    {
        $safeLimit = max(1, min(200, $limit));
        $sql = 'SELECT r.id, r.tenant_id, r.name, r.description, r.trigger_module, r.trigger_event, r.conditions_json, r.is_active, r.created_by_user_id, r.created_at, r.updated_at, u.display_name AS created_by_name, u.email AS created_by_email, (SELECT COUNT(*) FROM workflow_actions a WHERE a.tenant_id = r.tenant_id AND a.rule_id = r.id) AS actions_count FROM workflow_rules r LEFT JOIN core_users u ON u.id = r.created_by_user_id AND u.tenant_id = r.tenant_id WHERE r.tenant_id = :tenant_id ORDER BY r.updated_at DESC, r.id DESC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findRule(int $tenantId, int $ruleId): ?array
    {
        if ($ruleId <= 0) {
            return null;
        }

        $sql = 'SELECT r.id, r.tenant_id, r.name, r.description, r.trigger_module, r.trigger_event, r.conditions_json, r.is_active, r.created_by_user_id, r.created_at, r.updated_at, u.display_name AS created_by_name, u.email AS created_by_email, (SELECT COUNT(*) FROM workflow_actions a WHERE a.tenant_id = r.tenant_id AND a.rule_id = r.id) AS actions_count FROM workflow_rules r LEFT JOIN core_users u ON u.id = r.created_by_user_id AND u.tenant_id = r.tenant_id WHERE r.tenant_id = :tenant_id AND r.id = :id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $ruleId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function listActionsForRule(int $tenantId, int $ruleId): array
    {
        if ($ruleId <= 0) {
            return [];
        }

        $sql = 'SELECT id, rule_id, sort_order, action_type, target_module, config_json, is_active, created_at FROM workflow_actions WHERE tenant_id = :tenant_id AND rule_id = :rule_id ORDER BY sort_order ASC, id ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':rule_id', $ruleId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function summarizeRules(int $tenantId): array
    {
        $sql = 'SELECT trigger_module, is_active, COUNT(*) AS total_rules FROM workflow_rules WHERE tenant_id = :tenant_id GROUP BY trigger_module, is_active ORDER BY trigger_module ASC, is_active DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
