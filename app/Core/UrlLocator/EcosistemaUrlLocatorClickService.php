<?php

declare(strict_types=1);

namespace App\Core\UrlLocator;

final readonly class EcosistemaUrlLocatorClickService
{
    public function __construct(private EcosistemaUrlLocatorClickRepository $repository, private EcosistemaUrlLocatorAdapter $adapter)
    {
    }

    public function listClicks(int $tenantId, int $limit = 100): array
    {
        return [
            'summary' => $this->repository->summarizeClicks($tenantId),
            'clicks' => array_map(fn(array $r): array => $this->toSafeClickDto($r), $this->repository->listRecentClicks($tenantId, $limit)),
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    public function listClicksByLink(int $tenantId, int $linkId, int $limit = 100): array
    {
        return [
            'summary' => $this->repository->summarizeClicksForLink($tenantId, $linkId),
            'clicks' => array_map(fn(array $r): array => $this->toSafeClickDto($r), $this->repository->listClicksForLink($tenantId, $linkId, $limit)),
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    private function toSafeClickDto(array $row): array
    {
        $visitorUuid = trim((string) ($row['visitor_uuid'] ?? ''));
        $ipAddress = trim((string) ($row['ip_address'] ?? ''));
        $userAgent = trim((string) ($row['user_agent'] ?? ''));
        $acceptLanguage = trim((string) ($row['accept_language_header'] ?? ''));
        $referer = trim((string) ($row['referer'] ?? ''));
        $clickedUrl = trim((string) ($row['clicked_url'] ?? ''));
        $country = trim((string) ($row['country'] ?? ''));
        $region = trim((string) ($row['region'] ?? ''));
        $city = trim((string) ($row['city'] ?? ''));
        $latitude = trim((string) ($row['latitude'] ?? ''));
        $longitude = trim((string) ($row['longitude'] ?? ''));

        return [
            'id' => (int) ($row['id'] ?? 0),
            'short_link_id' => (int) ($row['short_link_id'] ?? 0),
            'short_link_slug' => (string) ($row['short_link_slug'] ?? ''),
            'short_link_title' => (string) ($row['short_link_title'] ?? ''),
            'campaign_id' => $row['campaign_id'] !== null ? (int) $row['campaign_id'] : null,
            'landing_page_id' => $row['landing_page_id'] !== null ? (int) $row['landing_page_id'] : null,
            'visitor_uuid_present' => $visitorUuid !== '',
            'visitor_uuid_exposed' => false,
            'ip_address_present' => $ipAddress !== '',
            'ip_address_preview' => $this->maskIp($ipAddress),
            'ip_address_exposed' => false,
            'user_agent_present' => $userAgent !== '',
            'user_agent_preview' => $this->preview($userAgent, 72),
            'accept_language_header_preview' => $this->preview($acceptLanguage, 48),
            'detected_language' => (string) ($row['detected_language'] ?? ''),
            'selected_language' => (string) ($row['selected_language'] ?? ''),
            'referer_present' => $referer !== '',
            'referer_preview' => $this->preview($referer, 72),
            'clicked_url_present' => $clickedUrl !== '',
            'clicked_url_preview' => $this->preview($clickedUrl, 72),
            'geo_present' => $country !== '' || $region !== '' || $city !== '',
            'country' => $country,
            'region' => $region,
            'city' => $city,
            'coordinates_present' => $latitude !== '' || $longitude !== '',
            'coordinates_exposed' => false,
            'device_type' => (string) ($row['device_type'] ?? ''),
            'browser_name' => (string) ($row['browser_name'] ?? ''),
            'os_name' => (string) ($row['os_name'] ?? ''),
            'clicked_at' => $row['clicked_at'] ?? null,
            'mode' => 'read-only',
            'db_write' => false,
            'tracking_write' => false,
        ];
    }

    private function preview(string $value, int $limit): ?string
    {
        $trim = trim($value);
        return $trim === '' ? null : mb_substr($trim, 0, $limit) . '…';
    }

    private function maskIp(string $value): ?string
    {
        $trim = trim($value);
        if ($trim === '') {
            return null;
        }

        if (str_contains($trim, '.')) {
            $parts = explode('.', $trim);
            if (count($parts) === 4) {
                return $parts[0] . '.' . $parts[1] . '.*.*';
            }
        }

        if (str_contains($trim, ':')) {
            $parts = explode(':', $trim);
            return ($parts[0] ?? '') . ':' . ($parts[1] ?? '') . ':*:*';
        }

        return 'masked';
    }
}
