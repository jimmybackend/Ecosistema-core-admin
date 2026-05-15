<?php

declare(strict_types=1);

namespace App\Core\Ai;

final readonly class EcosistemaAiCampaignInsightDryRunService
{
    public function __construct(private EcosistemaAiCampaignInsightDryRunRepository $repository, private array $config = []) {}

    public function build(int $tenantId, int $campaignId): array
    {
        $enabled = (bool) ($this->config['enabled'] ?? false);
        $dryRunEnabled = (bool) ($this->config['campaign_insight_dry_run'] ?? false);
        if ($tenantId <= 0 || $campaignId <= 0) {
            return ['ok' => false, 'allowed' => false, 'blocked_reason' => 'invalid_context'];
        }

        $campaign = $this->repository->findCampaign($tenantId, $campaignId);
        if ($campaign === null) {
            return ['ok' => false, 'allowed' => false, 'blocked_reason' => 'campaign_not_found'];
        }

        $rollups = $this->repository->listRecentRollups($tenantId, $campaignId, 7);
        $leadCount = $this->repository->countCampaignLeads($tenantId, $campaignId);
        $eventCount = $this->repository->countCampaignEvents($tenantId, $campaignId);

        return [
            'ok' => true,
            'allowed' => $enabled && $dryRunEnabled,
            'blocked_reason' => ($enabled && $dryRunEnabled) ? null : 'feature_disabled_by_flags',
            'mode' => 'dry-run',
            'provider' => 'none',
            'external_ai_called' => false,
            'proposal_persisted' => false,
            'context' => [
                'campaign' => $this->sanitizeCampaign($campaign),
                'metrics' => [
                    'lead_count' => $leadCount,
                    'service_event_count' => $eventCount,
                    'rollup_days_count' => count($rollups),
                    'rollups' => array_map(fn(array $row): array => $this->sanitizeRollup($row), $rollups),
                ],
                'sanitized' => true,
                'db_write' => false,
                'tenant_from_session' => true,
            ],
        ];
    }

    private function sanitizeCampaign(array $campaign): array
    {
        return [
            'id' => (int) ($campaign['id'] ?? 0),
            'name_preview' => $this->preview((string) ($campaign['name'] ?? ''), 80),
            'code_preview' => $this->preview((string) ($campaign['code'] ?? ''), 30),
            'status' => (string) ($campaign['status'] ?? ''),
            'has_budget' => isset($campaign['budget']) && $campaign['budget'] !== null && $campaign['budget'] !== '',
            'starts_at' => $campaign['starts_at'] ?? null,
            'ends_at' => $campaign['ends_at'] ?? null,
            'updated_at' => $campaign['updated_at'] ?? null,
        ];
    }

    private function sanitizeRollup(array $row): array
    {
        return [
            'rollup_date' => (string) ($row['rollup_date'] ?? ''),
            'sessions' => (int) ($row['sessions'] ?? 0),
            'pageviews' => (int) ($row['pageviews'] ?? 0),
            'clicks' => (int) ($row['clicks'] ?? 0),
            'submissions' => (int) ($row['submissions'] ?? 0),
            'conversions' => (int) ($row['conversions'] ?? 0),
        ];
    }

    private function preview(string $value, int $max): ?string
    {
        $trim = trim($value);
        if ($trim === '') {
            return null;
        }

        $head = mb_substr($trim, 0, $max);
        return $head === $trim ? $head : ($head . '…');
    }
}
