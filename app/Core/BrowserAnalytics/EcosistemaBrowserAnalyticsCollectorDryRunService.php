<?php
declare(strict_types=1);

namespace App\Core\BrowserAnalytics;

final class EcosistemaBrowserAnalyticsCollectorDryRunService
{
    public function simulate(int $tenantId, int $userId, array $payload, array $requestMeta = []): array
    {
        $warnings = [];
        $validationErrors = [];

        if (($payload['tenant_id'] ?? null) !== null) {
            $warnings[] = 'tenant_id_ignored_from_request';
        }
        if (($payload['user_id'] ?? null) !== null) {
            $warnings[] = 'user_id_ignored_from_request';
        }

        $eventType = $this->sanitizeText((string) ($payload['event_type'] ?? ''), 40);
        $eventName = $this->sanitizeText((string) ($payload['event_name'] ?? ''), 120);
        if ($eventType === '' || !preg_match('/^[a-z0-9._:-]{2,40}$/i', $eventType)) {
            $validationErrors[] = 'event_type_invalid';
        }
        if ($eventName === '' || mb_strlen($eventName) < 2) {
            $validationErrors[] = 'event_name_invalid';
        }

        $pageUrl = $this->sanitizeUrl((string) ($payload['page_url'] ?? ''));
        $path = $this->sanitizePath((string) ($payload['path'] ?? ''));
        $referrerUrl = $this->sanitizeUrl((string) ($payload['referrer_url'] ?? ''));

        if ($pageUrl === null) { $validationErrors[] = 'page_url_invalid'; }
        if ($path === null) { $validationErrors[] = 'path_invalid'; }
        if (($payload['referrer_url'] ?? '') !== '' && $referrerUrl === null) { $validationErrors[] = 'referrer_url_invalid'; }

        $campaignId = $this->optionalInt($payload['campaign_id'] ?? null);
        $landingPageId = $this->optionalInt($payload['landing_page_id'] ?? null);
        $shortLinkId = $this->optionalInt($payload['short_link_id'] ?? null);

        $maskedIp = $this->maskIp((string) ($requestMeta['ip_address'] ?? ''));
        $maskedAgent = $this->maskUserAgent((string) ($requestMeta['user_agent'] ?? ''));

        $sanitized = [
            'tenant_id' => $tenantId,
            'user_id' => $userId > 0 ? $userId : null,
            'event_type' => $eventType,
            'event_name' => $eventName,
            'page_url' => $pageUrl,
            'path' => $path,
            'referrer_url' => $referrerUrl,
            'campaign_id' => $campaignId,
            'landing_page_id' => $landingPageId,
            'short_link_id' => $shortLinkId,
            'ip_masked' => $maskedIp,
            'user_agent_masked' => $maskedAgent,
        ];

        $valid = $validationErrors === [];

        return [
            'mode' => 'dry-run',
            'collector_write' => false,
            'would_create_session' => $valid,
            'would_create_pageview' => $valid && $pageUrl !== null && $path !== null,
            'would_create_event' => $valid,
            'validation_status' => $valid ? 'ok' : 'invalid',
            'warnings' => array_values(array_unique(array_merge($warnings, $validationErrors))),
            'sanitized_payload' => $sanitized,
        ];
    }

    private function optionalInt(mixed $value): ?int
    {
        if ($value === null || $value === '') { return null; }
        $int = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $int === false ? null : (int) $int;
    }

    private function sanitizeText(string $value, int $max): string
    {
        return mb_substr(trim(strip_tags($value)), 0, $max);
    }

    private function sanitizeUrl(string $value): ?string
    {
        $v = trim($value);
        if ($v === '') { return null; }
        if (filter_var($v, FILTER_VALIDATE_URL) === false) { return null; }
        return mb_substr($v, 0, 512);
    }

    private function sanitizePath(string $value): ?string
    {
        $v = trim($value);
        if ($v === '' || $v[0] !== '/') { return null; }
        return preg_match('/^\/[A-Za-z0-9\-._~\/]*$/', $v) === 1 ? mb_substr($v, 0, 255) : null;
    }

    private function maskIp(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            return $parts[0] . '.' . $parts[1] . '.x.x';
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $chunks = explode(':', $ip);
            return (string) ($chunks[0] ?? '') . ':' . (string) ($chunks[1] ?? '') . ':xxxx:xxxx';
        }
        return 'unknown';
    }

    private function maskUserAgent(string $value): string
    {
        $clean = preg_replace('/\s+/', ' ', trim($value)) ?? '';
        if ($clean === '') { return 'unknown'; }
        return mb_substr($clean, 0, 32) . '…';
    }
}
