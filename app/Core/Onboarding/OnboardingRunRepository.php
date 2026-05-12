<?php

declare(strict_types=1);

namespace App\Core\Onboarding;

use PDO;

final readonly class OnboardingRunRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function statsByTenant(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT status, COUNT(*) AS total FROM onboarding_runs WHERE tenant_id = :tenant_id GROUP BY status');
        $stmt->execute([':tenant_id' => $tenantId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $stats = ['pending' => 0, 'running' => 0, 'completed' => 0, 'failed' => 0, 'canceled' => 0, 'partial' => 0];
        foreach ($rows as $r) {
            $s = (string) $r['status'];
            if (isset($stats[$s])) {
                $stats[$s] = (int) $r['total'];
            }
        }
        return $stats;
    }

    public function latestByTenant(int $tenantId, int $limit = 50): array
    {
        $stmt = $this->pdo->prepare('SELECT r.id, r.tenant_id, r.user_id, r.flow_id, r.status, r.started_at, r.completed_at, r.failed_at, r.error_message, r.context_json, r.created_by_user_id, r.created_at, r.updated_at, f.name AS flow_name, f.flow_key, u.email AS user_email, u.display_name AS user_display_name FROM onboarding_runs r INNER JOIN onboarding_flows f ON f.id = r.flow_id LEFT JOIN core_users u ON u.id = r.user_id WHERE r.tenant_id = :tenant_id ORDER BY r.id DESC LIMIT :lim');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function usersByTenant(int $tenantId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, tenant_id, email, display_name, first_name, last_name, status FROM core_users WHERE tenant_id = :tenant_id ORDER BY id DESC');
        $stmt->execute([':tenant_id' => $tenantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findUserForTenant(int $tenantId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, tenant_id, email, display_name, first_name, last_name, status FROM core_users WHERE id = :id AND tenant_id = :tenant_id LIMIT 1');
        $stmt->execute([':id' => $userId, ':tenant_id' => $tenantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function findRunByIdAndTenant(int $runId, int $tenantId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, tenant_id, status FROM onboarding_runs WHERE id = :id AND tenant_id = :tenant_id LIMIT 1');
        $stmt->execute([':id' => $runId, ':tenant_id' => $tenantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function createRun(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO onboarding_runs (tenant_id, user_id, flow_id, status, context_json, created_by_user_id) VALUES (:tenant_id, :user_id, :flow_id, :status, :context_json, :created_by_user_id)');
        $stmt->bindValue(':tenant_id', (int) $data['tenant_id'], PDO::PARAM_INT);
        $userId = $data['user_id'];
        $stmt->bindValue(':user_id', $userId === null ? null : (int) $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':flow_id', (int) $data['flow_id'], PDO::PARAM_INT);
        $stmt->bindValue(':status', (string) $data['status']);
        $context = $data['context_json'];
        $stmt->bindValue(':context_json', $context === null ? null : (string) $context, $context === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':created_by_user_id', (int) $data['created_by_user_id'], PDO::PARAM_INT);
        $stmt->execute();
        return (int) $this->pdo->lastInsertId();
    }

    public function createRunStep(int $runId, int $stepId): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO onboarding_run_steps (run_id, step_id, status) VALUES (:run_id, :step_id, :status)');
        $stmt->execute([':run_id' => $runId, ':step_id' => $stepId, ':status' => 'pending']);
    }

    public function createRunLog(int $runId, ?int $runStepId, string $level, string $message, ?string $contextJson): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO onboarding_run_logs (run_id, run_step_id, level, message, context_json) VALUES (:run_id, :run_step_id, :level, :message, :context_json)');
        $stmt->bindValue(':run_id', $runId, PDO::PARAM_INT);
        $stmt->bindValue(':run_step_id', $runStepId, $runStepId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':level', $level);
        $stmt->bindValue(':message', $message);
        $stmt->bindValue(':context_json', $contextJson, $contextJson === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->execute();
    }

    public function startRun(int $tenantId, int $runId): bool
    {
        $stmt = $this->pdo->prepare("UPDATE onboarding_runs SET status = :running, started_at = COALESCE(started_at, NOW()) WHERE id = :id AND tenant_id = :tenant_id AND status IN ('pending','running')");
        $stmt->execute([':running' => 'running', ':id' => $runId, ':tenant_id' => $tenantId]);
        return $stmt->rowCount() > 0;
    }

    public function findNextPendingRunStep(int $runId, int $tenantId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT rs.id, rs.run_id, rs.step_id, rs.status, st.action_type FROM onboarding_run_steps rs INNER JOIN onboarding_runs r ON r.id = rs.run_id INNER JOIN onboarding_steps st ON st.id = rs.step_id WHERE rs.run_id = :run_id AND r.tenant_id = :tenant_id AND rs.status = :pending ORDER BY rs.id ASC LIMIT 1');
        $stmt->execute([':run_id' => $runId, ':tenant_id' => $tenantId, ':pending' => 'pending']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function updateRunStepStatus(int $runStepId, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE onboarding_run_steps SET status = :status, completed_at = NOW(), error_message = NULL WHERE id = :id');
        $stmt->execute([':status' => $status, ':id' => $runStepId]);
    }

    public function resolveRunFinalStatus(int $runId): ?string
    {
        $stmt = $this->pdo->prepare('SELECT status, COUNT(*) AS total FROM onboarding_run_steps WHERE run_id = :run_id GROUP BY status');
        $stmt->execute([':run_id' => $runId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $counts = [];
        foreach ($rows as $r) {
            $counts[(string) $r['status']] = (int) $r['total'];
        }

        if (($counts['pending'] ?? 0) > 0 || ($counts['running'] ?? 0) > 0) {
            return null;
        }
        if (($counts['failed'] ?? 0) > 0) {
            return 'failed';
        }
        if (($counts['completed'] ?? 0) > 0 && ($counts['skipped'] ?? 0) === 0) {
            return 'completed';
        }
        return 'partial';
    }

    public function markRunFinished(int $runId, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE onboarding_runs SET status = :status, completed_at = NOW() WHERE id = :id');
        $stmt->execute([':status' => $status, ':id' => $runId]);
    }
}
