<?php

declare(strict_types=1);

namespace App\Core\Workflow;

final class EcosistemaWorkflowAdapter
{
    public function __construct(private array $config = [])
    {
    }

    public function capabilities(): array
    {
        $execution = (bool) (($this->config['enabled'] ?? false) && ($this->config['execution_enabled'] ?? false));
        return [
            'rules_read' => true,
            'rule_detail_read' => true,
            'actions_read' => true,
            'runs_read' => true,
            'run_logs_read' => true,
            'dry_run' => true,
            'execution_write' => $execution,
            'action_execution' => $execution,
            'db_writes' => $execution,
            'retry_enabled' => false,
            'external_calls' => false,
            'webhook_calls' => (bool) ($execution && ($this->config['action_webhook'] ?? false)),
            'email_send' => (bool) ($execution && ($this->config['action_send_email'] ?? false)),
            'custom_actions' => false,
            'mode' => $execution ? 'controlled' : 'dry-run',
        ];
    }
}
