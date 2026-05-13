<?php

declare(strict_types=1);

namespace App\Core\UrlLocator;

use PDO;
use Throwable;

final readonly class EcosistemaUrlLocatorPublicRedirectService
{
    public function __construct(private PDO $pdo, private array $config = [])
    {
    }

    public function resolvePublicSlug(string $slug, array $requestContext = []): array
    {
        $result = [
            'allowed' => false,
            'http_status' => 302,
            'blocked_reason' => 'link_not_available',
            'target_url' => null,
            'selected_language' => null,
            'detected_language' => null,
            'tracking_enabled' => $this->trackingEnabled(),
            'track_payload' => null,
        ];

        if (!$this->redirectEnabled()) {
            $result['blocked_reason'] = 'redirect_disabled';
            return $result;
        }

        if (!$this->isValidSlug($slug)) {
            return $result;
        }

        $tenantId = (int) ($this->config['public_tenant_id'] ?? 0);
        if ($tenantId <= 0) {
            $result['blocked_reason'] = 'redirect_disabled';
            return $result;
        }

        $link = $this->findPublicLink($tenantId, $slug);
        if ($link === null) {
            return $result;
        }

        if ((string) ($link['status'] ?? '') !== 'active') {
            return $result;
        }

        if ($this->isExpired((string) ($link['expires_at'] ?? ''))) {
            $result['blocked_reason'] = 'link_expired';
            return $result;
        }

        if ($this->maxClicksReached($link)) {
            $result['blocked_reason'] = 'max_clicks_reached';
            return $result;
        }

        $language = $this->resolveLanguage($tenantId, (int) $link['id'], $requestContext);
        $targetUrl = $this->resolveTargetUrl($tenantId, $link, $language['selected_language']);
        if ($targetUrl === null || !$this->isSafeTargetUrl($targetUrl)) {
            return $result;
        }

        $result['allowed'] = true;
        $result['blocked_reason'] = null;
        $result['target_url'] = $targetUrl;
        $result['selected_language'] = $language['selected_language'];
        $result['detected_language'] = $language['detected_language'];
        $result['http_status'] = $this->redirectHttpStatus();

        if ($result['tracking_enabled']) {
            $result['track_payload'] = [
                'tenant_id' => $tenantId,
                'short_link_id' => (int) $link['id'],
                'campaign_id' => isset($link['campaign_id']) ? (int) $link['campaign_id'] : null,
                'landing_page_id' => isset($link['landing_page_id']) ? (int) $link['landing_page_id'] : null,
                'accept_language_header' => (string) ($requestContext['accept_language_header'] ?? ''),
                'detected_language' => $language['detected_language'],
                'selected_language' => $language['selected_language'],
                'referer' => (string) ($requestContext['referer'] ?? ''),
                'clicked_url' => (string) ($requestContext['public_url'] ?? ''),
                'ip_address' => $this->collectIpEnabled() ? (string) ($requestContext['ip_address'] ?? '') : null,
                'user_agent' => $this->collectUserAgentEnabled() ? (string) ($requestContext['user_agent'] ?? '') : null,
                'visitor_uuid' => null,
                'clicked_at' => gmdate('Y-m-d H:i:s'),
            ];
        }

        return $result;
    }

    public function executeRedirect(array $resolved): never
    {
        if (($resolved['allowed'] ?? false) !== true || !isset($resolved['target_url'])) {
            throw new \RuntimeException('Redirect blocked');
        }

        if (($resolved['tracking_enabled'] ?? false) === true && is_array($resolved['track_payload'] ?? null)) {
            $this->storeClick((array) $resolved['track_payload']);
        }

        header('Location: ' . (string) $resolved['target_url'], true, (int) ($resolved['http_status'] ?? 302));
        exit;
    }

    private function findPublicLink(int $tenantId, string $slug): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id,tenant_id,campaign_id,landing_page_id,slug,target_url,language_fallback_url,status,expires_at,max_clicks,click_count FROM url_short_links WHERE tenant_id=:tenant_id AND slug=:slug LIMIT 1');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':slug', $slug);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    private function resolveLanguage(int $tenantId, int $linkId, array $requestContext): array
    {
        $detected = $this->detectLanguage((string) ($requestContext['accept_language_header'] ?? ''));
        if (!$this->languageRedirectsEnabled()) {
            return ['selected_language' => null, 'detected_language' => $detected];
        }

        $stmt = $this->pdo->prepare('SELECT language_code,target_url,is_active,priority FROM url_short_link_languages WHERE short_link_id=:link_id ORDER BY priority ASC,language_code ASC');
        $stmt->bindValue(':link_id', $linkId, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $active = [];
        foreach ($rows as $row) {
            if ((int) ($row['is_active'] ?? 0) !== 1) {
                continue;
            }
            $code = strtolower((string) ($row['language_code'] ?? ''));
            $target = trim((string) ($row['target_url'] ?? ''));
            if ($code !== '' && $target !== '') {
                $active[$code] = $target;
            }
        }

        if ($detected !== null && isset($active[$detected])) {
            return ['selected_language' => $detected, 'detected_language' => $detected];
        }

        return ['selected_language' => null, 'detected_language' => $detected];
    }

    private function resolveTargetUrl(int $tenantId, array $link, ?string $selectedLanguage): ?string
    {
        if ($selectedLanguage !== null && $this->languageRedirectsEnabled()) {
            $stmt = $this->pdo->prepare('SELECT target_url FROM url_short_link_languages WHERE short_link_id=:link_id AND language_code=:language_code AND is_active=1 LIMIT 1');
            $stmt->bindValue(':link_id', (int) $link['id'], PDO::PARAM_INT);
            $stmt->bindValue(':language_code', $selectedLanguage);
            $stmt->execute();
            $langTarget = $stmt->fetchColumn();
            if (is_string($langTarget) && trim($langTarget) !== '') {
                return trim($langTarget);
            }
        }

        $fallback = trim((string) ($link['language_fallback_url'] ?? ''));
        if ($fallback !== '') {
            return $fallback;
        }

        $target = trim((string) ($link['target_url'] ?? ''));
        return $target !== '' ? $target : null;
    }

    private function storeClick(array $payload): void
    {
        try {
            $this->pdo->beginTransaction();
            $insert = $this->pdo->prepare('INSERT INTO url_clicks (tenant_id,short_link_id,campaign_id,landing_page_id,visitor_uuid,ip_address,user_agent,accept_language_header,detected_language,selected_language,referer,clicked_url,country,region,city,device_type,browser_name,os_name,clicked_at) VALUES (:tenant_id,:short_link_id,:campaign_id,:landing_page_id,:visitor_uuid,:ip_address,:user_agent,:accept_language_header,:detected_language,:selected_language,:referer,:clicked_url,:country,:region,:city,:device_type,:browser_name,:os_name,:clicked_at)');
            $insert->execute([
                ':tenant_id' => $payload['tenant_id'], ':short_link_id' => $payload['short_link_id'], ':campaign_id' => $payload['campaign_id'],
                ':landing_page_id' => $payload['landing_page_id'], ':visitor_uuid' => $payload['visitor_uuid'], ':ip_address' => $payload['ip_address'],
                ':user_agent' => $payload['user_agent'], ':accept_language_header' => $payload['accept_language_header'],
                ':detected_language' => $payload['detected_language'], ':selected_language' => $payload['selected_language'],
                ':referer' => $payload['referer'], ':clicked_url' => $payload['clicked_url'], ':country' => null, ':region' => null,
                ':city' => null, ':device_type' => null, ':browser_name' => null, ':os_name' => null, ':clicked_at' => $payload['clicked_at'],
            ]);

            $update = $this->pdo->prepare('UPDATE url_short_links SET click_count=click_count+1 WHERE tenant_id=:tenant_id AND id=:id');
            $update->bindValue(':tenant_id', (int) $payload['tenant_id'], PDO::PARAM_INT);
            $update->bindValue(':id', (int) $payload['short_link_id'], PDO::PARAM_INT);
            $update->execute();
            $this->pdo->commit();
        } catch (Throwable) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
        }
    }

    private function redirectEnabled(): bool { return (bool) ($this->config['enabled'] ?? false) && (bool) ($this->config['public_redirects_enabled'] ?? false); }
    private function trackingEnabled(): bool { return (bool) ($this->config['tracking_enabled'] ?? false); }
    private function languageRedirectsEnabled(): bool { return (bool) ($this->config['language_redirects_enabled'] ?? false); }
    private function collectIpEnabled(): bool { return (bool) ($this->config['collect_ip_enabled'] ?? false); }
    private function collectUserAgentEnabled(): bool { return (bool) ($this->config['collect_user_agent_enabled'] ?? false); }

    private function isValidSlug(string $slug): bool { return (bool) preg_match('/^[A-Za-z0-9_-]{3,120}$/', $slug); }

    private function isExpired(string $expiresAt): bool
    {
        $value = trim($expiresAt);
        return $value !== '' && strtotime($value) !== false && strtotime($value) < time();
    }

    private function maxClicksReached(array $link): bool
    {
        $maxClicks = isset($link['max_clicks']) ? (int) $link['max_clicks'] : 0;
        return $maxClicks > 0 && (int) ($link['click_count'] ?? 0) >= $maxClicks;
    }

    private function isSafeTargetUrl(string $url): bool
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return false;
        }
        if (!(bool) ($this->config['allow_private_target_urls'] ?? false)) {
            $host = (string) ($parts['host'] ?? '');
            if ($host === 'localhost' || preg_match('/^(127\.|10\.|192\.168\.|172\.(1[6-9]|2\d|3[01])\.)/', $host)) {
                return false;
            }
        }

        return true;
    }

    private function detectLanguage(string $header): ?string
    {
        $head = trim(explode(',', $header)[0] ?? '');
        if ($head === '') { return null; }
        $code = strtolower(trim(explode(';', $head)[0]));
        $normalized = substr($code, 0, 2);
        return preg_match('/^[a-z]{2}$/', $normalized) ? $normalized : null;
    }

    private function redirectHttpStatus(): int
    {
        $code = (int) ($this->config['public_redirect_status'] ?? 302);
        return in_array($code, [301, 302], true) ? $code : 302;
    }
}
