<?php

declare(strict_types=1);

namespace App\Core\Workflow;

final readonly class EcosistemaWorkflowRuleService
{
    public function __construct(private EcosistemaWorkflowRuleRepository $repository, private EcosistemaWorkflowAdapter $adapter)
    {
    }

    public function listRules(int $tenantId, int $limit = 100): array
    {
        return [
            'summary' => $this->normalizeSummary($this->repository->summarizeRules($tenantId)),
            'items' => array_map(fn (array $row): array => $this->toRuleDto($row), $this->repository->listRules($tenantId, $limit)),
            'mode' => 'read-only',
            'db_write' => false,
            'execution_enabled' => false,
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    public function findRuleDetail(int $tenantId, int $ruleId): ?array
    {
        $rule = $this->repository->findRule($tenantId, $ruleId);
        if (!is_array($rule)) {
            return null;
        }

        return [
            'rule' => $this->toRuleDto($rule),
            'actions' => array_map(fn (array $row): array => $this->toActionDto($row), $this->repository->listActionsForRule($tenantId, $ruleId)),
            'mode' => 'read-only',
            'db_write' => false,
            'execution_enabled' => false,
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    private function normalizeSummary(array $rows): array
    {
        return array_map(static fn (array $row): array => [
            'trigger_module' => (string) ($row['trigger_module'] ?? ''),
            'is_active' => (int) ($row['is_active'] ?? 0) === 1,
            'total_rules' => (int) ($row['total_rules'] ?? 0),
        ], $rows);
    }

    private function toRuleDto(array $row): array
    {
        $description = trim((string) ($row['description'] ?? ''));
        $conditionsJson = trim((string) ($row['conditions_json'] ?? ''));

        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'description_preview' => mb_substr($description, 0, 160),
            'trigger_module' => (string) ($row['trigger_module'] ?? ''),
            'trigger_event' => (string) ($row['trigger_event'] ?? ''),
            'conditions_json_present' => $conditionsJson !== '',
            'conditions_json_exposed' => false,
            'is_active' => (int) ($row['is_active'] ?? 0) === 1,
            'created_by_user_id' => isset($row['created_by_user_id']) ? (int) $row['created_by_user_id'] : null,
            'created_by_label' => $this->createdByLabel($row),
            'actions_count' => (int) ($row['actions_count'] ?? 0),
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
            'mode' => 'read-only',
            'db_write' => false,
            'execution_enabled' => false,
        ];
    }

    private function toActionDto(array $row): array
    {
        $configJson = trim((string) ($row['config_json'] ?? ''));
        $actionType = (string) ($row['action_type'] ?? '');

        return [
            'id' => (int) ($row['id'] ?? 0),
            'rule_id' => (int) ($row['rule_id'] ?? 0),
            'sort_order' => (int) ($row['sort_order'] ?? 0),
            'action_type' => $actionType,
            'action_type_label' => $this->actionTypeLabel($actionType),
            'target_module' => (string) ($row['target_module'] ?? ''),
            'config_json_present' => $configJson !== '',
            'config_json_exposed' => false,
            'is_active' => (int) ($row['is_active'] ?? 0) === 1,
            'created_at' => $row['created_at'] ?? null,
            'execution_enabled' => false,
        ];
    }

    private function createdByLabel(array $row): string
    {
        $name = trim((string) ($row['created_by_name'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $email = trim((string) ($row['created_by_email'] ?? ''));
        return $email !== '' ? $email : 'N/A';
    }

    private function actionTypeLabel(string $actionType): string
    {
        $labels = [
            'create_notification' => 'Create notification',
            'create_agenda_event' => 'Create agenda event',
            'create_ticket' => 'Create ticket',
            'send_email' => 'Send email',
            'webhook' => 'Webhook',
            'update_record' => 'Update record',
            'create_task' => 'Create task',
            'custom' => 'Custom',
        ];

        return $labels[$actionType] ?? 'Other';
    }
}
