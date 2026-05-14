<?php

declare(strict_types=1);

namespace App\Core\Attribution;

final readonly class EcosistemaCampaignAttributionService
{
    public function __construct(private EcosistemaCampaignAttributionRepository $repository) {}

    public function listCampaigns(int $tenantId): array
    {
        if ($tenantId <= 0) {
            return ['summary' => ['total' => 0], 'campaigns' => []];
        }

        $campaigns = array_map(fn(array $row): array => $this->campaignListDto($row), $this->repository->listCampaigns($tenantId));
        return ['summary' => ['total' => count($campaigns)], 'campaigns' => $campaigns];
    }

    public function campaignDetail(int $tenantId, int $campaignId): array
    {
        $result = ['found' => false, 'campaign' => null, 'funnel' => ['clicks' => 0, 'visits' => 0, 'submissions' => 0, 'leads' => 0, 'conversions' => 0]];
        if ($tenantId <= 0 || $campaignId <= 0) {
            return $result;
        }

        $campaign = $this->repository->findCampaign($tenantId, $campaignId);
        if ($campaign === null) {
            return $result;
        }

        $result['found'] = true;
        $result['campaign'] = $this->campaignDetailDto($campaign);
        $result['funnel'] = $this->repository->fetchFunnelCounts($tenantId, $campaignId);
        return $result;
    }

    private function campaignListDto(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'code' => (string) ($row['code'] ?? ''),
            'status' => (string) ($row['status'] ?? ''),
            'campaign_type' => (string) ($row['campaign_type'] ?? ''),
            'starts_at' => (string) ($row['starts_at'] ?? ''),
            'ends_at' => (string) ($row['ends_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
        ];
    }

    private function campaignDetailDto(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'code' => (string) ($row['code'] ?? ''),
            'status' => (string) ($row['status'] ?? ''),
            'campaign_type' => (string) ($row['campaign_type'] ?? ''),
            'objective' => (string) ($row['objective'] ?? ''),
            'description_preview' => $this->preview((string) ($row['description'] ?? ''), 120),
            'starts_at' => (string) ($row['starts_at'] ?? ''),
            'ends_at' => (string) ($row['ends_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
        ];
    }

    private function preview(string $value, int $max): string
    {
        $trim = trim($value);
        if ($trim === '') {
            return '';
        }

        return mb_strlen($trim) <= $max ? $trim : mb_substr($trim, 0, $max) . '…';
    }
}
