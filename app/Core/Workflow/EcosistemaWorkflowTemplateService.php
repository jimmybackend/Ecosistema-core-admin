<?php

declare(strict_types=1);

namespace App\Core\Workflow;

final class EcosistemaWorkflowTemplateService
{
    public function listTemplates(int $tenantId): array
    {
        if ($tenantId <= 0) {
            return ['items' => [], 'mode' => 'read-only', 'db_write' => false, 'catalog_source' => 'static_php'];
        }

        $items = array_values(array_map(fn (array $row): array => $this->toTemplateDto($row), EcosistemaWorkflowTemplateCatalog::all()));

        return ['items' => $items, 'mode' => 'read-only', 'db_write' => false, 'catalog_source' => 'static_php'];
    }

    public function findTemplateByKey(int $tenantId, string $key): ?array
    {
        if ($tenantId <= 0 || $key === '') {
            return null;
        }

        $catalog = EcosistemaWorkflowTemplateCatalog::all();
        if (!isset($catalog[$key]) || !is_array($catalog[$key])) {
            return null;
        }

        return ['template' => $this->toTemplateDto($catalog[$key]), 'mode' => 'read-only', 'db_write' => false, 'catalog_source' => 'static_php'];
    }

    private function toTemplateDto(array $row): array
    {
        $actions = array_values(array_filter(array_map(static fn (mixed $a): string => trim((string) $a), (array) ($row['actions'] ?? [])), static fn (string $a): bool => $a !== ''));
        return [
            'key' => (string) ($row['key'] ?? ''),
            'name' => (string) ($row['name'] ?? ''),
            'description_preview' => mb_substr(trim((string) ($row['description'] ?? '')), 0, 180),
            'trigger_module' => (string) ($row['trigger_module'] ?? ''),
            'trigger_event' => (string) ($row['trigger_event'] ?? ''),
            'actions' => $actions,
            'actions_count' => count($actions),
            'status' => (string) ($row['status'] ?? 'suggested'),
            'conditions_json_present' => false,
            'config_json_present' => false,
            'mode' => 'read-only',
            'db_write' => false,
        ];
    }
}
