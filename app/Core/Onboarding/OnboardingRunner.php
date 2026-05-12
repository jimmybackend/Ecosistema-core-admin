<?php

declare(strict_types=1);

namespace App\Core\Onboarding;

use PDO;

final readonly class OnboardingRunner
{
    public function __construct(
        private PDO $pdo,
        private OnboardingRunRepository $runs,
        private OnboardingStepExecutor $executor,
    ) {
    }

    public function startRun(int $tenantId, int $runId): bool
    {
        return $this->runs->startRun($tenantId, $runId);
    }

    /**
     * @return array{ok:bool,run_id:int,audit_action:?string}
     */
    public function executeNextStep(int $tenantId, int $runId): array
    {
        $run = $this->runs->findRunByIdAndTenant($runId, $tenantId);
        if ($run === null) {
            return ['ok' => false, 'run_id' => 0, 'audit_action' => null];
        }

        $this->pdo->beginTransaction();
        try {
            $this->runs->startRun($tenantId, $runId);
            $this->runs->createRunLog($runId, null, 'info', 'Inicio de ejecución segura de siguiente paso.', null);

            $nextStep = $this->runs->findNextPendingRunStep($runId, $tenantId);
            if ($nextStep === null) {
                $finalStatus = $this->runs->resolveRunFinalStatus($runId);
                if ($finalStatus !== null) {
                    $this->runs->markRunFinished($runId, $finalStatus);
                    $this->runs->createRunLog($runId, null, 'info', 'Onboarding run finalizada sin pasos pendientes.', null);
                }
                $this->pdo->commit();
                return ['ok' => true, 'run_id' => $runId, 'audit_action' => $finalStatus === 'completed' ? 'onboarding.run_completed' : null];
            }

            $result = $this->executor->execute($nextStep);
            $this->runs->updateRunStepStatus((int) $nextStep['id'], $result['status']);
            $this->runs->createRunLog($runId, (int) $nextStep['id'], $result['level'], $result['message'], json_encode([
                'step_id' => (int) $nextStep['step_id'],
                'action_type' => (string) ($nextStep['action_type'] ?? ''),
            ], JSON_UNESCAPED_UNICODE));

            $finalStatus = $this->runs->resolveRunFinalStatus($runId);
            if ($finalStatus !== null) {
                $this->runs->markRunFinished($runId, $finalStatus);
                $this->runs->createRunLog(
                    $runId,
                    null,
                    $finalStatus === 'completed' ? 'info' : 'warning',
                    $finalStatus === 'completed' ? 'Onboarding run completada.' : 'Onboarding run finalizada como parcial.',
                    null
                );
            }

            $this->pdo->commit();
            return ['ok' => true, 'run_id' => $runId, 'audit_action' => $finalStatus === 'completed' ? 'onboarding.run_completed' : $result['audit_action']];
        } catch (\Throwable) {
            $this->pdo->rollBack();
            return ['ok' => false, 'run_id' => $runId, 'audit_action' => null];
        }
    }
}
