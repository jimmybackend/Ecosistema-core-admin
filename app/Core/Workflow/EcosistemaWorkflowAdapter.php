<?php

declare(strict_types=1);

namespace App\Core\Workflow;

final class EcosistemaWorkflowAdapter
{
    public function capabilities(): array
    {
        return [
            'rules_read' => true,
            'rule_detail_read' => true,
            'actions_read' => true,
            'runs_read' => true,
            'run_logs_read' => true,
            'dry_run' => true,
            'execution_write' => false,
            'action_execution' => false,
            'db_writes' => false,
            'retry_enabled' => false,
            'external_calls' => false,
            'mode' => 'dry-run',
        ];
    }
}
