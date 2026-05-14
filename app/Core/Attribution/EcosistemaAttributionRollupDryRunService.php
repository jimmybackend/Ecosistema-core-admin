<?php

declare(strict_types=1);

namespace App\Core\Attribution;

final readonly class EcosistemaAttributionRollupDryRunService
{
    public function __construct(private EcosistemaAttributionRollupDryRunRepository $repository, private bool $enabled = false) {}

    public function simulate(int $tenantId, string $startDate, string $endDate): array
    {
        $result = [
            'mode' => 'dry-run',
            'enabled' => $this->enabled,
            'db_write' => false,
            'tenant_from_session' => true,
            'allowed' => false,
            'blocked_reason' => null,
            'range' => ['start_date' => $startDate, 'end_date' => $endDate],
            'metrics' => ['clicks' => 0, 'visits' => 0, 'sessions' => 0, 'submissions' => 0, 'attributions' => 0],
            'by_campaign' => [],
            'warnings' => [],
        ];

        if (!$this->enabled) { $result['blocked_reason'] = 'feature_disabled'; return $result; }
        if ($tenantId <= 0) { $result['blocked_reason'] = 'invalid_tenant'; return $result; }
        if (!$this->validDate($startDate) || !$this->validDate($endDate) || $startDate > $endDate) {
            $result['blocked_reason'] = 'invalid_date_range';
            return $result;
        }

        $data = $this->repository->aggregate($tenantId, $startDate, $endDate);
        $result['allowed'] = true;
        $result['metrics'] = [
            'clicks' => (int) ($data['clicks'] ?? 0),
            'visits' => (int) ($data['visits'] ?? 0),
            'sessions' => (int) ($data['sessions'] ?? 0),
            'submissions' => (int) ($data['submissions'] ?? 0),
            'attributions' => (int) ($data['attributions'] ?? 0),
        ];
        $result['by_campaign'] = array_map(static fn(array $row): array => [
            'campaign_id' => (int) ($row['campaign_id'] ?? 0),
            'campaign_label' => (string) ($row['campaign_label'] ?? 'Campaign'),
            'attributions_count' => (int) ($row['attributions_count'] ?? 0),
        ], (array) ($data['by_campaign'] ?? []));
        if ($result['metrics']['attributions'] === 0) { $result['warnings'][] = 'no_attributions_in_range'; }

        return $result;
    }

    private function validDate(string $value): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
    }
}
