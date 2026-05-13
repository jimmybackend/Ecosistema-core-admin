<?php

declare(strict_types=1);

namespace App\Core\Landing;

final readonly class EcosistemaLandingVisitService
{
    public function __construct(private EcosistemaLandingVisitRepository $repository, private EcosistemaLandingAdapter $adapter)
    {
    }

    public function listVisits(int $tenantId, int $limit = 100): array
    {
        return [
            'summary' => $this->repository->summarizeVisits($tenantId),
            'visits' => array_map(fn(array $row): array => $this->toSafeDto($row), $this->repository->listRecentVisits($tenantId, $limit)),
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    public function listVisitsForPage(int $tenantId, int $pageId, int $limit = 100): array
    {
        if ($pageId <= 0) {
            return ['summary' => ['total' => 0, 'by_country' => [], 'by_device_type' => [], 'by_campaign' => []], 'visits' => [], 'capabilities' => $this->adapter->capabilities()];
        }

        return [
            'summary' => $this->repository->summarizeVisitsForPage($tenantId, $pageId),
            'visits' => array_map(fn(array $row): array => $this->toSafeDto($row), $this->repository->listVisitsForPage($tenantId, $pageId, $limit)),
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    private function toSafeDto(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'landing_page_id' => isset($row['landing_page_id']) ? (int) $row['landing_page_id'] : null,
            'landing_page_title' => (string) ($row['landing_page_title'] ?? ''),
            'landing_page_slug' => (string) ($row['landing_page_slug'] ?? ''),
            'campaign_id' => isset($row['campaign_id']) ? (int) $row['campaign_id'] : null,
            'campaign_name' => (string) ($row['campaign_name'] ?? ''),
            'short_link_id' => isset($row['short_link_id']) ? (int) $row['short_link_id'] : null,
            'short_link_slug' => (string) ($row['short_link_slug'] ?? ''),
            'visitor_uuid_present' => trim((string) ($row['visitor_uuid'] ?? '')) !== '',
            'visitor_uuid_exposed' => false,
            'session_uuid_present' => trim((string) ($row['session_uuid'] ?? '')) !== '',
            'session_uuid_exposed' => false,
            'ip_address_present' => trim((string) ($row['ip_address'] ?? '')) !== '',
            'ip_address_preview' => $this->preview((string) ($row['ip_address'] ?? ''), 7),
            'ip_address_exposed' => false,
            'user_agent_present' => trim((string) ($row['user_agent'] ?? '')) !== '',
            'user_agent_preview' => $this->preview((string) ($row['user_agent'] ?? ''), 48),
            'referer_present' => trim((string) ($row['referer'] ?? '')) !== '',
            'referer_preview' => $this->preview((string) ($row['referer'] ?? ''), 48),
            'full_url_present' => trim((string) ($row['full_url'] ?? '')) !== '',
            'full_url_preview' => $this->preview((string) ($row['full_url'] ?? ''), 48),
            'utm_source' => (string) ($row['utm_source'] ?? ''),
            'utm_medium' => (string) ($row['utm_medium'] ?? ''),
            'utm_campaign' => (string) ($row['utm_campaign'] ?? ''),
            'utm_term_present' => trim((string) ($row['utm_term'] ?? '')) !== '',
            'utm_content_present' => trim((string) ($row['utm_content'] ?? '')) !== '',
            'geo_present' => trim((string) ($row['country'] ?? '')) !== '' || trim((string) ($row['region'] ?? '')) !== '' || trim((string) ($row['city'] ?? '')) !== '',
            'country' => (string) ($row['country'] ?? ''),
            'region' => (string) ($row['region'] ?? ''),
            'city' => (string) ($row['city'] ?? ''),
            'coordinates_present' => trim((string) ($row['latitude'] ?? '')) !== '' || trim((string) ($row['longitude'] ?? '')) !== '',
            'coordinates_exposed' => false,
            'device_type' => (string) ($row['device_type'] ?? ''),
            'browser_name' => (string) ($row['browser_name'] ?? ''),
            'os_name' => (string) ($row['os_name'] ?? ''),
            'visited_at' => $row['visited_at'] ?? null,
            'mode' => 'read-only',
            'db_write' => false,
            'tracking_write' => false,
        ];
    }

    private function preview(string $value, int $max): ?string
    {
        $trim = trim($value);
        if ($trim === '') {
            return null;
        }

        $head = mb_substr($trim, 0, $max);
        return $head === $trim ? $head : $head . '…';
    }
}
