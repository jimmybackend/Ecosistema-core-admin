<?php

declare(strict_types=1);

namespace App\Core\Workflow;

final readonly class EcosistemaWorkflowTemplateInstallService
{
    public function __construct(private EcosistemaWorkflowTemplateInstallRepository $repository, private array $config)
    {
    }

    public function install(int $tenantId, int $userId, string $key): array
    {
        if ($tenantId <= 0 || $userId <= 0 || trim($key) === '') {
            return $this->blockedResult($key, ['invalid_context']);
        }

        $template = EcosistemaWorkflowTemplateCatalog::all()[$key] ?? null;
        if (!is_array($template)) {
            return $this->blockedResult($key, ['template_not_found']);
        }

        if (!$this->isWriteEnabled()) {
            return $this->blockedResult($key, ['feature_disabled']);
        }

        $ruleId = $this->repository->insertRule($tenantId, $userId, $template);
        $actionsCreated = 0;
        $order = 1;
        foreach ((array) ($template['actions'] ?? []) as $actionType) {
            $type = trim((string) $actionType);
            if ($type === '') { continue; }
            $this->repository->insertAction($tenantId, $ruleId, [
                'sort_order' => $order,
                'action_type' => $this->normalizeActionType($type),
                'target_module' => $this->targetModuleByAction($type),
            ]);
            $actionsCreated++;
            $order++;
        }

        return [
            'mode' => 'template-install',
            'db_write' => true,
            'installed' => true,
            'rule_id' => $ruleId,
            'actions_created' => $actionsCreated,
            'rule_active' => false,
            'tenant_from_session' => true,
            'template' => ['key' => (string) ($template['key'] ?? ''), 'name' => (string) ($template['name'] ?? '')],
            'warnings' => ['rule_created_inactive', 'actions_created_inactive'],
            'blocked_reasons' => [],
        ];
    }

    private function blockedResult(string $key, array $reasons): array
    {
        return ['mode' => 'template-install', 'db_write' => false, 'installed' => false, 'template' => ['key' => $key], 'warnings' => ['no_db_write'], 'blocked_reasons' => $reasons];
    }

    private function isWriteEnabled(): bool { return (bool) ($this->config['template_install_write'] ?? false); }

    private function normalizeActionType(string $actionType): string
    {
        $allowed = ['create_notification','create_agenda_event','create_ticket','send_email','webhook','update_record','create_task','custom'];
        return in_array($actionType, $allowed, true) ? $actionType : 'custom';
    }
    private function targetModuleByAction(string $actionType): string
    {
        return match ($actionType) {
            'create_notification', 'send_email' => 'mail',
            'create_ticket' => 'support',
            'create_task', 'create_agenda_event', 'update_record' => 'crm',
            'webhook' => 'integration',
            default => 'workflow',
        };
    }
}
