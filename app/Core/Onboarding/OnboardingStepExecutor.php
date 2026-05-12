<?php

declare(strict_types=1);

namespace App\Core\Onboarding;

final class OnboardingStepExecutor
{
    /**
     * @param array<string,mixed> $step
     * @return array{status:string,level:string,message:string,audit_action:string}
     */
    public function execute(array $step): array
    {
        $actionType = strtolower(trim((string) ($step['action_type'] ?? '')));

        if ($actionType === '' || in_array($actionType, ['manual', 'noop', 'checklist'], true)) {
            return [
                'status' => 'completed',
                'level' => 'info',
                'message' => 'Paso seguro ejecutado sin aprovisionamiento externo.',
                'audit_action' => 'onboarding.step_completed',
            ];
        }

        return [
            'status' => 'skipped',
            'level' => 'warning',
            'message' => 'Paso no soportado todavía; se omite ejecución externa.',
            'audit_action' => 'onboarding.step_skipped',
        ];
    }
}
