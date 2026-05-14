<?php

declare(strict_types=1);

namespace App\Core\Crm;

final readonly class EcosistemaCrmCampaignService
{
    public function __construct(private EcosistemaCrmCampaignRepository $repository, private EcosistemaCrmAdapter $adapter) {}

    public function listCampaigns(int $tenantId, int $limit = 100): array
    {
        return [
            'summary' => $this->repository->summarizeCampaigns($tenantId),
            'campaigns' => array_map(fn(array $row): array => $this->toSafeDto($row), $this->repository->listCampaigns($tenantId, $limit)),
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    public function getCampaign(int $tenantId, int $campaignId): ?array
    {
        $row = $this->repository->findCampaign($tenantId, $campaignId);
        if ($row === null) {
            return null;
        }

        return array_merge($this->toSafeDto($row), [
            'total_visits' => (int) ($row['total_visits'] ?? 0),
            'total_clicks' => (int) ($row['total_clicks'] ?? 0),
            'total_submissions' => (int) ($row['total_submissions'] ?? 0),
        ]);
    }

    private function toSafeDto(array $row): array
    {
        $landingUrl = trim((string) ($row['landing_url'] ?? ''));
        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'code' => (string) ($row['code'] ?? ''),
            'description_preview' => $this->preview((string) ($row['description'] ?? ''), 140),
            'campaign_type' => (string) ($row['campaign_type'] ?? ''),
            'objective' => (string) ($row['objective'] ?? ''),
            'status' => (string) ($row['status'] ?? ''),
            'budget_present' => $row['budget'] !== null && $row['budget'] !== '',
            'budget_preview' => $row['budget'] === null || $row['budget'] === '' ? null : '***',
            'currency' => (string) ($row['currency'] ?? ''),
            'starts_at' => $row['starts_at'] ?? null,
            'ends_at' => $row['ends_at'] ?? null,
            'landing_url_present' => $landingUrl !== '',
            'landing_url_preview' => $this->sanitizeLandingUrl($landingUrl),
            'source_module' => (string) ($row['source_module'] ?? ''),
            'source_table' => (string) ($row['source_table'] ?? ''),
            'source_id' => isset($row['source_id']) ? (int) $row['source_id'] : null,
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
            'mode' => 'read-only',
            'db_write' => false,
        ];
    }

    private function preview(string $value, int $max): ?string
    {
        $trim = trim($value);
        if ($trim === '') { return null; }
        $head = mb_substr($trim, 0, $max);
        return $head === $trim ? $head : $head . '…';
    }

    private function sanitizeLandingUrl(string $url): ?string
    {
        if ($url === '') { return null; }
        $parts = parse_url($url);
        if (!is_array($parts)) { return $this->preview($url, 72); }
        $base = ($parts['scheme'] ?? 'https') . '://' . ($parts['host'] ?? '') . ($parts['path'] ?? '');
        return isset($parts['query']) ? $base . '?…' : $base;
    }
}
