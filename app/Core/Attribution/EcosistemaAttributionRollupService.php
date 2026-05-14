<?php

declare(strict_types=1);

namespace App\Core\Attribution;

final readonly class EcosistemaAttributionRollupService
{
    public function __construct(
        private EcosistemaAttributionRollupRepository $repository,
        private bool $moduleEnabled = false,
        private bool $rollupWriteEnabled = false,
    ) {}

    public function generate(int $tenantId, string $rollupDate): array
    {
        $result = [
            'mode' => 'controlled-write',
            'allowed' => false,
            'db_write' => false,
            'written' => false,
            'blocked_reason' => null,
            'tenant_from_session' => true,
            'rollup_date' => $rollupDate,
            'metrics_preview' => [],
            'warnings' => [],
        ];

        if (!$this->moduleEnabled || !$this->rollupWriteEnabled) {
            $result['blocked_reason'] = 'feature_flag_disabled';
            return $result;
        }
        if ($tenantId <= 0) {
            $result['blocked_reason'] = 'invalid_tenant';
            return $result;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rollupDate) !== 1) {
            $result['blocked_reason'] = 'invalid_rollup_date';
            return $result;
        }

        $existing = $this->repository->countExistingRollupsForDate($tenantId, $rollupDate);
        $result['metrics_preview'] = $this->repository->aggregateForDate($tenantId, $rollupDate);
        $result['warnings'][] = 'write_blocked_until_idempotency_strategy_confirmed';
        if ($existing > 0) {
            $result['warnings'][] = 'existing_rollups_detected_for_date';
        }
        $result['blocked_reason'] = 'idempotency_not_guaranteed';

        return $result;
    }
}
