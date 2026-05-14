<?php

declare(strict_types=1);

namespace App\Core\Workflow;

final readonly class EcosistemaWorkflowDryRunService
{
    public function __construct(private EcosistemaWorkflowRuleRepository $repository, private EcosistemaWorkflowAdapter $adapter, private array $config)
    {
    }

    public function simulateRule(int $tenantId, int $ruleId, array $context): array
    {
        if ($ruleId <= 0) {
            return $this->blockedResult(['invalid_rule_id']);
        }

        $rule = $this->repository->findRule($tenantId, $ruleId);
        if (!is_array($rule)) {
            return $this->blockedResult(['rule_not_found']);
        }

        return $this->buildResult($rule, $context, true);
    }

    public function simulateEvent(int $tenantId, string $triggerModule, string $triggerEvent, array $context): array
    {
        if (!$this->isSafeToken($triggerModule) || !$this->isSafeToken($triggerEvent)) {
            return $this->blockedResult(['invalid_trigger']);
        }

        $match = null;
        foreach ($this->repository->listRules($tenantId, 200) as $row) {
            if ((string) ($row['trigger_module'] ?? '') === $triggerModule && (string) ($row['trigger_event'] ?? '') === $triggerEvent) {
                $match = $row;
                break;
            }
        }

        if (!is_array($match)) {
            $result = $this->baseResult();
            $result['trigger_module'] = $triggerModule;
            $result['trigger_event'] = $triggerEvent;
            $result['source_module'] = (string) ($context['source_module'] ?? '');
            $result['source_table_present'] = trim((string) ($context['source_table'] ?? '')) !== '';
            $result['source_id_present'] = isset($context['source_id']) && (string) $context['source_id'] !== '';
            $result['blocked_reasons'][] = 'rule_not_found';
            return $result;
        }

        return $this->buildResult($match, $context, true);
    }

    private function buildResult(array $rule, array $context, bool $matched): array
    {
        $result = $this->baseResult();
        $result['rule_id'] = (int) ($rule['id'] ?? 0);
        $result['rule_name'] = (string) ($rule['name'] ?? '');
        $result['trigger_module'] = (string) ($rule['trigger_module'] ?? '');
        $result['trigger_event'] = (string) ($rule['trigger_event'] ?? '');
        $result['source_module'] = (string) ($context['source_module'] ?? '');
        $result['source_table_present'] = trim((string) ($context['source_table'] ?? '')) !== '';
        $result['source_id_present'] = isset($context['source_id']) && (string) $context['source_id'] !== '';
        $result['matched'] = $matched;
        $result['conditions_json_present'] = trim((string) ($rule['conditions_json'] ?? '')) !== '';
        $result['conditions_evaluation_status'] = $result['conditions_json_present'] ? 'basic_check' : 'not_executed';

        $actions = $this->repository->listActionsForRule((int) ($rule['tenant_id'] ?? 0), (int) ($rule['id'] ?? 0));
        $result['actions'] = array_map(fn (array $action): array => $this->actionDto($action), $actions);

        foreach ($result['actions'] as $action) {
            if (($action['would_execute'] ?? false) !== true && is_string($action['blocked_reason']) && $action['blocked_reason'] !== '') {
                $result['blocked_reasons'][] = $action['blocked_reason'];
            }
        }

        return $result;
    }

    private function actionDto(array $row): array
    {
        $type = (string) ($row['action_type'] ?? '');
        $blockedReason = '';
        $wouldExecute = true;
        if ($type === 'custom') {
            $wouldExecute = false;
            $blockedReason = 'blocked_by_default';
        }

        return [
            'action_id' => (int) ($row['id'] ?? 0),
            'sort_order' => (int) ($row['sort_order'] ?? 0),
            'action_type' => $type,
            'target_module' => (string) ($row['target_module'] ?? ''),
            'config_json_present' => trim((string) ($row['config_json'] ?? '')) !== '',
            'config_json_exposed' => false,
            'would_execute' => $wouldExecute,
            'executed' => false,
            'blocked_reason' => $blockedReason,
        ];
    }

    private function baseResult(): array
    {
        $workflowEnabled = (bool) (($this->config['enabled'] ?? false));
        $dryRunEnabled = (bool) (($this->config['dry_run_enabled'] ?? false));
        $blockedReasons = [];
        if ($workflowEnabled !== true) {
            $blockedReasons[] = 'workflow_disabled';
        }
        if ($dryRunEnabled !== true) {
            $blockedReasons[] = 'dry_run_disabled';
        }

        return [
            'mode' => 'dry-run',
            'workflow_enabled' => $workflowEnabled,
            'dry_run_enabled' => $dryRunEnabled,
            'execution_enabled' => false,
            'rule_id' => 0,
            'rule_name' => '',
            'trigger_module' => '',
            'trigger_event' => '',
            'source_module' => '',
            'source_table_present' => false,
            'source_id_present' => false,
            'matched' => false,
            'conditions_json_present' => false,
            'conditions_json_exposed' => false,
            'conditions_evaluation_status' => 'not_executed',
            'actions' => [],
            'would_create_run' => false,
            'would_write_logs' => false,
            'would_execute_actions' => false,
            'db_write' => false,
            'external_calls' => false,
            'email_send' => false,
            'webhook_call' => false,
            'warnings' => [],
            'blocked_reasons' => $blockedReasons,
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    private function blockedResult(array $reasons): array
    {
        $result = $this->baseResult();
        $result['blocked_reasons'] = array_values(array_unique(array_merge($result['blocked_reasons'], $reasons)));
        return $result;
    }

    private function isSafeToken(string $value): bool
    {
        return $value !== '' && preg_match('/^[a-zA-Z0-9_\-.]{1,100}$/', $value) === 1;
    }
}
