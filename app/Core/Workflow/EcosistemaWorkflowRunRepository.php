<?php

declare(strict_types=1);

namespace App\Core\Workflow;

use PDO;

final readonly class EcosistemaWorkflowRunRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listRuns(int $tenantId, int $limit = 100): array
    {
        $safeLimit = $this->sanitizeLimit($limit);
        $sql = 'SELECT r.id, r.rule_id, wr.name AS rule_name, r.triggered_by_user_id, u.display_name AS triggered_by_name, u.email AS triggered_by_email, r.source_module, r.source_table, r.source_id, r.status, r.input_json, r.output_json, r.error_message, r.started_at, r.finished_at, r.created_at, (SELECT COUNT(*) FROM workflow_run_logs l WHERE l.tenant_id = r.tenant_id AND l.run_id = r.id) AS logs_count, (SELECT COUNT(*) FROM module_workflow_links ml WHERE ml.tenant_id = r.tenant_id AND ml.workflow_run_id = r.id) AS links_count FROM workflow_runs r LEFT JOIN workflow_rules wr ON wr.id = r.rule_id AND wr.tenant_id = r.tenant_id LEFT JOIN core_users u ON u.id = r.triggered_by_user_id AND u.tenant_id = r.tenant_id WHERE r.tenant_id = :tenant_id ORDER BY r.created_at DESC, r.id DESC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findRun(int $tenantId, int $runId): ?array
    {
        if ($runId <= 0) {
            return null;
        }

        $sql = 'SELECT r.id, r.rule_id, wr.name AS rule_name, r.triggered_by_user_id, u.display_name AS triggered_by_name, u.email AS triggered_by_email, r.source_module, r.source_table, r.source_id, r.status, r.input_json, r.output_json, r.error_message, r.started_at, r.finished_at, r.created_at, (SELECT COUNT(*) FROM workflow_run_logs l WHERE l.tenant_id = r.tenant_id AND l.run_id = r.id) AS logs_count, (SELECT COUNT(*) FROM module_workflow_links ml WHERE ml.tenant_id = r.tenant_id AND ml.workflow_run_id = r.id) AS links_count FROM workflow_runs r LEFT JOIN workflow_rules wr ON wr.id = r.rule_id AND wr.tenant_id = r.tenant_id LEFT JOIN core_users u ON u.id = r.triggered_by_user_id AND u.tenant_id = r.tenant_id WHERE r.tenant_id = :tenant_id AND r.id = :id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $runId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function listLogsForRun(int $tenantId, int $runId, int $limit = 200): array
    {
        if ($runId <= 0) {
            return [];
        }

        $safeLimit = $this->sanitizeLimit($limit);
        $sql = 'SELECT l.id, l.run_id, l.action_id, l.level, l.message, l.context_json, l.created_at, a.action_type FROM workflow_run_logs l LEFT JOIN workflow_actions a ON a.id = l.action_id AND a.tenant_id = l.tenant_id WHERE l.tenant_id = :tenant_id AND l.run_id = :run_id ORDER BY l.created_at DESC, l.id DESC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':run_id', $runId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listModuleLinksForRun(int $tenantId, int $runId, int $limit = 100): array
    {
        if ($runId <= 0) {
            return [];
        }

        $safeLimit = $this->sanitizeLimit($limit);
        $sql = 'SELECT id, module_code, entity_table, entity_id, relation_type, metadata_json, created_at FROM module_workflow_links WHERE tenant_id = :tenant_id AND workflow_run_id = :run_id ORDER BY created_at DESC, id DESC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':run_id', $runId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function summarizeRuns(int $tenantId): array
    {
        $sql = 'SELECT status, COUNT(*) AS total_runs FROM workflow_runs WHERE tenant_id = :tenant_id GROUP BY status ORDER BY status ASC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function sanitizeLimit(int $limit): int
    {
        return max(1, min(300, $limit));
    }
}
