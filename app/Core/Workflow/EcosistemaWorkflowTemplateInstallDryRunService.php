<?php

declare(strict_types=1);

namespace App\Core\Workflow;

final class EcosistemaWorkflowTemplateInstallDryRunService
{
    /** @var string[] */
    private const ALLOWED_ACTION_TYPES = [
        'create_notification',
        'create_agenda_event',
        'create_ticket',
        'send_email',
        'webhook',
        'update_record',
        'create_task',
        'custom',
    ];

    public function __construct(private readonly array $config)
    {
    }

    public function simulate(int $tenantId, int $userId, string $key): array
    {
        if ($tenantId <= 0 || $userId <= 0 || $key === '') {
            return $this->blockedResult($key, ['invalid_context']);
        }

        $catalog = EcosistemaWorkflowTemplateCatalog::all();
        $template = $catalog[$key] ?? null;
        if (!is_array($template)) {
            return $this->blockedResult($key, ['template_not_found']);
        }

        return [
            'mode' => 'template-install-dry-run',
            'feature_enabled' => $this->isFeatureEnabled(),
            'db_write' => false,
            'would_create_rule' => false,
            'would_create_actions' => false,
            'catalog_source' => 'static_php',
            'selected_template' => [
                'key' => (string) ($template['key'] ?? ''),
                'name' => (string) ($template['name'] ?? ''),
                'trigger_module' => (string) ($template['trigger_module'] ?? ''),
                'trigger_event' => (string) ($template['trigger_event'] ?? ''),
                'description_preview' => mb_substr(trim((string) ($template['description'] ?? '')), 0, 180),
                'conditions_json_present' => false,
                'conditions_json_exposed' => false,
            ],
            'rule_preview' => [
                'name' => (string) ($template['name'] ?? ''),
                'description_preview' => mb_substr(trim((string) ($template['description'] ?? '')), 0, 180),
                'trigger_module' => (string) ($template['trigger_module'] ?? ''),
                'trigger_event' => (string) ($template['trigger_event'] ?? ''),
                'is_active' => false,
                'created_by_user_id' => $userId,
                'tenant_from_session' => true,
            ],
            'actions_preview' => $this->buildActionsPreview((array) ($template['actions'] ?? [])),
            'warnings' => ['no_db_write', 'template_catalog_static'],
            'blocked_reasons' => $this->isFeatureEnabled() ? ['write_blocked_by_design'] : ['feature_disabled', 'write_blocked_by_design'],
        ];
    }

    private function buildActionsPreview(array $actions): array
    {
        $items = [];
        $order = 1;
        foreach ($actions as $actionType) {
            $type = trim((string) $actionType);
            if ($type === '') {
                continue;
            }
            $items[] = [
                'sort_order' => $order,
                'action_type' => in_array($type, self::ALLOWED_ACTION_TYPES, true) ? $type : 'custom',
                'target_module' => $this->targetModuleByAction($type),
                'config_json_present' => false,
                'config_json_exposed' => false,
            ];
            $order++;
        }

        return $items;
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

    private function blockedResult(string $key, array $reasons): array
    {
        return [
            'mode' => 'template-install-dry-run',
            'feature_enabled' => $this->isFeatureEnabled(),
            'db_write' => false,
            'would_create_rule' => false,
            'would_create_actions' => false,
            'catalog_source' => 'static_php',
            'selected_template' => ['key' => $key],
            'rule_preview' => [],
            'actions_preview' => [],
            'warnings' => ['no_db_write'],
            'blocked_reasons' => $reasons,
        ];
    }

    private function isFeatureEnabled(): bool
    {
        return (bool) (($this->config['template_install_dry_run'] ?? false));
    }
}
