<?php

declare(strict_types=1);

namespace App\Core\Workflow;

use PDO;
use Throwable;

final readonly class EcosistemaWorkflowExecutionRepository
{
    public function __construct(private PDO $pdo, private EcosistemaWorkflowRuleRepository $rules)
    {
    }

    public function createRun(int $tenantId, int $ruleId, ?int $userId, array $source, array $input): int
    {
        if ($this->rules->findRule($tenantId, $ruleId) === null) {
            throw new \InvalidArgumentException('rule_not_found');
        }

        $this->pdo->beginTransaction();
        try {
            $sql = 'INSERT INTO workflow_runs (tenant_id, rule_id, triggered_by_user_id, source_module, source_table, source_id, status, input_json, output_json, error_message, started_at, finished_at, created_at) VALUES (:tenant_id, :rule_id, :user_id, :source_module, :source_table, :source_id, :status, :input_json, :output_json, :error_message, NOW(), NULL, NOW())';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
            $stmt->bindValue(':rule_id', $ruleId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':source_module', $this->safeText((string) ($source['source_module'] ?? ''), 100));
            $stmt->bindValue(':source_table', $this->safeText((string) ($source['source_table'] ?? ''), 120));
            $sourceId = isset($source['source_id']) ? (int) $source['source_id'] : null;
            $stmt->bindValue(':source_id', $sourceId, $sourceId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $stmt->bindValue(':status', 'queued');
            $stmt->bindValue(':input_json', $this->safeJson($input));
            $stmt->bindValue(':output_json', '');
            $stmt->bindValue(':error_message', '');
            $stmt->execute();

            $runId = (int) $this->pdo->lastInsertId();
            $this->pdo->commit();
            return $runId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateRunStatus(int $tenantId, int $runId, string $status, array $output = [], ?string $errorMessage = null): bool
    {
        $sql = 'UPDATE workflow_runs SET status = :status, output_json = :output_json, error_message = :error_message, finished_at = :finished_at WHERE tenant_id = :tenant_id AND id = :run_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':status', $this->safeStatus($status));
        $stmt->bindValue(':output_json', $this->safeJson($output));
        $stmt->bindValue(':error_message', $this->safeText((string) ($errorMessage ?? ''), 255));
        $stmt->bindValue(':finished_at', in_array($status, ['success', 'failed', 'canceled'], true) ? date('Y-m-d H:i:s') : null);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':run_id', $runId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function insertRunLog(int $tenantId, int $runId, ?int $actionId, string $level, string $message, array $context = []): int
    {
        $sql = 'INSERT INTO workflow_run_logs (tenant_id, run_id, action_id, level, message, context_json, created_at) VALUES (:tenant_id, :run_id, :action_id, :level, :message, :context_json, NOW())';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':run_id', $runId, PDO::PARAM_INT);
        $stmt->bindValue(':action_id', $actionId, $actionId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':level', $this->safeLevel($level));
        $stmt->bindValue(':message', $this->safeText($message, 180));
        $stmt->bindValue(':context_json', $this->safeJson($context));
        $stmt->execute();
        return (int) $this->pdo->lastInsertId();
    }

    public function insertModuleWorkflowLink(int $tenantId, int $runId, int $ruleId, string $moduleCode, ?string $entityTable, ?int $entityId, string $relationType, ?int $userId = null, array $metadata = []): ?int
    {
        $sql = 'INSERT INTO module_workflow_links (tenant_id, module_code, entity_table, entity_id, workflow_rule_id, workflow_run_id, relation_type, metadata_json, created_by_user_id, created_at) VALUES (:tenant_id, :module_code, :entity_table, :entity_id, :workflow_rule_id, :workflow_run_id, :relation_type, :metadata_json, :user_id, NOW())';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':module_code', $this->safeText($moduleCode, 80));
        $stmt->bindValue(':entity_table', $this->safeText((string) ($entityTable ?? ''), 120));
        $stmt->bindValue(':entity_id', $entityId, $entityId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':workflow_rule_id', $ruleId, PDO::PARAM_INT);
        $stmt->bindValue(':workflow_run_id', $runId, PDO::PARAM_INT);
        $stmt->bindValue(':relation_type', $this->safeRelationType($relationType));
        $stmt->bindValue(':metadata_json', $this->safeJson($metadata));
        $stmt->bindValue(':user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        if (!$stmt->execute()) {
            return null;
        }

        return (int) $this->pdo->lastInsertId();
    }

    private function safeJson(array $payload): string
    {
        return (string) json_encode($this->sanitizeArray($payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function sanitizeArray(array $payload): array
    {
        $blocked = ['password', 'secret', 'token', 'authorization', 'smtp', 'webhook_url', 'config_json'];
        $clean = [];
        foreach ($payload as $k => $v) {
            $key = strtolower((string) $k);
            if (in_array($key, $blocked, true)) { continue; }
            $clean[$k] = is_array($v) ? $this->sanitizeArray($v) : $this->safeText((string) $v, 160);
        }

        return $clean;
    }

    private function safeText(string $value, int $max): string { return mb_substr(trim($value), 0, $max); }
    private function safeLevel(string $value): string { return in_array($value, ['debug','info','warning','error'], true) ? $value : 'info'; }
    private function safeStatus(string $value): string { return in_array($value, ['queued','running','success','failed','canceled'], true) ? $value : 'failed'; }
    private function safeRelationType(string $value): string { return in_array($value, ['trigger','action','condition','result','error','manual','system','other'], true) ? $value : 'other'; }
}
