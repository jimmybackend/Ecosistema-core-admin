<?php

declare(strict_types=1);

namespace App\Core\Workflow;

final readonly class EcosistemaWorkflowRunService
{
    public function __construct(private EcosistemaWorkflowRunRepository $repository, private EcosistemaWorkflowAdapter $adapter)
    {
    }

    public function listRuns(int $tenantId, int $limit = 100, ?int $ruleId = null): array
    {
        $items = array_map(fn (array $row): array => $this->toRunDto($row), $this->repository->listRuns($tenantId, $limit));
        if ($ruleId !== null && $ruleId > 0) {
            $items = array_values(array_filter($items, static fn (array $item): bool => (int) ($item['rule_id'] ?? 0) === $ruleId));
        }

        return [
            'summary' => $this->normalizeSummary($this->repository->summarizeRuns($tenantId)),
            'items' => $items,
            'mode' => 'read-only',
            'db_write' => false,
            'execution_enabled' => false,
            'retry_enabled' => false,
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    public function findRunDetail(int $tenantId, int $runId): ?array
    {
        $run = $this->repository->findRun($tenantId, $runId);
        if (!is_array($run)) {
            return null;
        }

        return [
            'run' => $this->toRunDto($run),
            'logs' => array_map(fn (array $row): array => $this->toLogDto($row), $this->repository->listLogsForRun($tenantId, $runId, 200)),
            'module_links' => array_map(fn (array $row): array => $this->toModuleLinkDto($row), $this->repository->listModuleLinksForRun($tenantId, $runId, 100)),
            'mode' => 'read-only',
            'db_write' => false,
            'execution_enabled' => false,
            'retry_enabled' => false,
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    private function normalizeSummary(array $rows): array
    {
        return array_map(static fn (array $row): array => ['status' => (string) ($row['status'] ?? ''), 'total_runs' => (int) ($row['total_runs'] ?? 0)], $rows);
    }

    private function toRunDto(array $row): array
    {
        $sourceTable = trim((string) ($row['source_table'] ?? ''));
        $inputJson = trim((string) ($row['input_json'] ?? ''));
        $outputJson = trim((string) ($row['output_json'] ?? ''));
        $errorMessage = trim((string) ($row['error_message'] ?? ''));

        return [
            'id' => (int) ($row['id'] ?? 0), 'rule_id' => (int) ($row['rule_id'] ?? 0), 'rule_name' => (string) ($row['rule_name'] ?? ''),
            'triggered_by_user_id' => isset($row['triggered_by_user_id']) ? (int) $row['triggered_by_user_id'] : null,
            'triggered_by_label' => $this->triggeredByLabel($row), 'source_module' => (string) ($row['source_module'] ?? ''),
            'source_table_present' => $sourceTable !== '', 'source_table_preview' => mb_substr($sourceTable, 0, 80),
            'source_id_present' => isset($row['source_id']) && (string) $row['source_id'] !== '', 'source_id' => isset($row['source_id']) ? (int) $row['source_id'] : null,
            'status' => (string) ($row['status'] ?? ''), 'input_json_present' => $inputJson !== '', 'input_json_exposed' => false,
            'output_json_present' => $outputJson !== '', 'output_json_exposed' => false, 'error_message_present' => $errorMessage !== '',
            'error_message_preview' => mb_substr($errorMessage, 0, 160), 'started_at' => $row['started_at'] ?? null, 'finished_at' => $row['finished_at'] ?? null,
            'created_at' => $row['created_at'] ?? null, 'logs_count' => (int) ($row['logs_count'] ?? 0), 'links_count' => (int) ($row['links_count'] ?? 0),
            'mode' => 'read-only', 'db_write' => false, 'execution_enabled' => false, 'retry_enabled' => false,
        ];
    }

    private function toLogDto(array $row): array
    {
        $message = trim((string) ($row['message'] ?? ''));
        $contextJson = trim((string) ($row['context_json'] ?? ''));

        return [
            'id' => (int) ($row['id'] ?? 0), 'run_id' => (int) ($row['run_id'] ?? 0), 'action_id' => isset($row['action_id']) ? (int) $row['action_id'] : null,
            'action_type' => (string) ($row['action_type'] ?? ''), 'level' => (string) ($row['level'] ?? ''),
            'message_present' => $message !== '', 'message_preview' => mb_substr($message, 0, 160),
            'context_json_present' => $contextJson !== '', 'context_json_exposed' => false, 'created_at' => $row['created_at'] ?? null,
        ];
    }

    private function toModuleLinkDto(array $row): array
    {
        $entityTable = trim((string) ($row['entity_table'] ?? ''));
        $metadataJson = trim((string) ($row['metadata_json'] ?? ''));

        return [
            'id' => (int) ($row['id'] ?? 0), 'module_code' => (string) ($row['module_code'] ?? ''), 'entity_table_present' => $entityTable !== '',
            'entity_table_preview' => mb_substr($entityTable, 0, 80), 'entity_id_present' => isset($row['entity_id']) && (string) $row['entity_id'] !== '',
            'relation_type' => (string) ($row['relation_type'] ?? ''), 'metadata_json_present' => $metadataJson !== '', 'metadata_json_exposed' => false,
            'created_at' => $row['created_at'] ?? null,
        ];
    }

    private function triggeredByLabel(array $row): string
    {
        $name = trim((string) ($row['triggered_by_name'] ?? ''));
        if ($name !== '') { return $name; }
        $email = trim((string) ($row['triggered_by_email'] ?? ''));
        return $email !== '' ? $email : 'N/A';
    }
}
