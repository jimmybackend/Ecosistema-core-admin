<?php

declare(strict_types=1);

namespace App\Core\Workflow;

final readonly class EcosistemaWorkflowExecutionService
{
    public function __construct(private EcosistemaWorkflowRuleRepository $rules, private EcosistemaWorkflowDryRunService $dryRun, private EcosistemaWorkflowExecutionRepository $writer, private array $config)
    {
    }

    public function executeRule(int $tenantId, int $ruleId, array $context): array
    {
        $sim = $this->dryRun->simulateRule($tenantId, $ruleId, $context);
        return $this->executeFromSimulation($tenantId, $ruleId, $context, $sim);
    }

    public function executeEvent(int $tenantId, string $triggerModule, string $triggerEvent, array $context): array
    {
        $sim = $this->dryRun->simulateEvent($tenantId, $triggerModule, $triggerEvent, $context);
        $ruleId = (int) ($sim['rule_id'] ?? 0);
        return $this->executeFromSimulation($tenantId, $ruleId, $context, $sim);
    }

    private function executeFromSimulation(int $tenantId, int $ruleId, array $context, array $sim): array
    {
        $enabled = (bool) ($this->config['enabled'] ?? false);
        $executionEnabled = (bool) ($this->config['execution_enabled'] ?? false);
        if (!$enabled || !$executionEnabled || $ruleId <= 0 || ($sim['matched'] ?? false) !== true) {
            return $this->blockedResult($ruleId, ['execution_disabled']);
        }

        $runId = $this->writer->createRun($tenantId, $ruleId, isset($context['triggered_by_user_id']) ? (int) $context['triggered_by_user_id'] : null, $context, $context);
        $this->writer->updateRunStatus($tenantId, $runId, 'running');
        $this->writer->insertRunLog($tenantId, $runId, null, 'info', 'workflow execution started');

        $executed = 0; $blocked = 0; $warnings = []; $safeLogs = [];
        foreach ((array) ($sim['actions'] ?? []) as $action) {
            $actionId = (int) ($action['action_id'] ?? 0);
            $type = (string) ($action['action_type'] ?? '');
            $flag = $this->actionFlag($type);
            if ($flag === '' || (($this->config[$flag] ?? false) !== true) || in_array($type, ['custom','webhook','update_record','create_task','create_ticket','create_agenda_event','send_email'], true)) {
                $blocked++;
                $warnings[] = 'action_blocked_' . $type;
                $this->writer->insertRunLog($tenantId, $runId, $actionId, 'warning', 'action blocked', ['action_type' => $type]);
                $safeLogs[] = ['level' => 'warning', 'message' => 'action blocked: ' . $type];
                continue;
            }

            $executed++;
            $this->writer->insertRunLog($tenantId, $runId, $actionId, 'info', 'action delegated safely', ['action_type' => $type]);
            $safeLogs[] = ['level' => 'info', 'message' => 'action delegated: ' . $type];
        }

        $status = $warnings === [] ? 'success' : 'canceled';
        $this->writer->updateRunStatus($tenantId, $runId, $status, ['actions_executed' => $executed, 'actions_blocked' => $blocked], $warnings === [] ? null : 'blocked_actions_present');

        return [
            'mode' => 'controlled', 'execution_enabled' => true, 'run_id' => $runId, 'rule_id' => $ruleId, 'status' => $status,
            'actions_total' => count((array) ($sim['actions'] ?? [])), 'actions_executed' => $executed, 'actions_blocked' => $blocked,
            'db_write' => true, 'external_calls' => false, 'email_send' => false, 'webhook_call' => false,
            'warnings' => $warnings, 'safe_logs' => $safeLogs,
        ];
    }

    private function blockedResult(int $ruleId, array $warnings): array
    {
        return ['mode' => 'controlled', 'execution_enabled' => false, 'run_id' => 0, 'rule_id' => $ruleId, 'status' => 'canceled', 'actions_total' => 0, 'actions_executed' => 0, 'actions_blocked' => 0, 'db_write' => false, 'external_calls' => false, 'email_send' => false, 'webhook_call' => false, 'warnings' => $warnings, 'safe_logs' => []];
    }

    private function actionFlag(string $actionType): string
    {
        return match ($actionType) {
            'create_notification' => 'action_create_notification',
            'send_email' => 'action_send_email',
            'webhook' => 'action_webhook',
            'update_record' => 'action_update_record',
            'create_task' => 'action_create_task',
            'create_ticket' => 'action_create_ticket',
            'create_agenda_event' => 'action_create_agenda_event',
            'custom' => 'action_custom',
            default => '',
        };
    }
}
