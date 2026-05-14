<?php
declare(strict_types=1);

namespace App\Core\BrowserAnalytics;

use PDO;

final readonly class EcosistemaBrowserAnalyticsCollectorService
{
    public function __construct(private EcosistemaBrowserAnalyticsCollectorRepository $repository, private PDO $pdo)
    {
    }

    public function collect(int $tenantId, int $userId, array $payload, array $serverContext = []): array
    {
        $session = (array)($payload['session'] ?? []);
        $pageview = (array)($payload['pageview'] ?? []);
        $event = (array)($payload['event'] ?? []);

        $allowedSessionKeys = ['core_session_id','browser_session_uuid','visitor_uuid','started_at','last_activity_at','entry_url','exit_url','referrer_url','device_type','browser_name','browser_version','os_name','os_version','country','region','city','latitude','longitude','utm_source','utm_medium','utm_campaign','utm_term','utm_content','consent_status'];
        $allowedPageviewKeys = ['campaign_id','landing_page_id','short_link_id','crm_lead_id','page_url','page_title','referrer_url','path','query_string','hash_fragment','viewed_at','duration_ms','scroll_depth_percent','is_landing_view','is_campaign_view','meta_json'];
        $allowedEventKeys = ['pageview_id','campaign_id','landing_page_id','short_link_id','crm_lead_id','event_type','event_name','element_id','element_text','element_url','value_numeric','value_text','score_points','metadata_json','occurred_at'];

        if (array_diff(array_keys($session), $allowedSessionKeys) || array_diff(array_keys($pageview), $allowedPageviewKeys) || array_diff(array_keys($event), $allowedEventKeys)) {
            throw new \InvalidArgumentException('Invalid collector payload.');
        }

        $collectIp = filter_var((string) getenv('ECOSISTEMA_BROWSER_ANALYTICS_COLLECT_IP'), FILTER_VALIDATE_BOOL);
        $collectUa = filter_var((string) getenv('ECOSISTEMA_BROWSER_ANALYTICS_COLLECT_USER_AGENT'), FILTER_VALIDATE_BOOL);

        $sessionData = [
            'user_id' => $userId > 0 ? $userId : null,
            'core_session_id' => $this->truncate((string)($session['core_session_id'] ?? ''), 128),
            'browser_session_uuid' => $this->truncate((string)($session['browser_session_uuid'] ?? ''), 64),
            'visitor_uuid' => $this->truncate((string)($session['visitor_uuid'] ?? ''), 64),
            'started_at' => (string)($session['started_at'] ?? date('Y-m-d H:i:s')),
            'last_activity_at' => (string)($session['last_activity_at'] ?? date('Y-m-d H:i:s')),
            'entry_url' => $this->safeUrl($session['entry_url'] ?? null),
            'exit_url' => $this->safeUrl($session['exit_url'] ?? null),
            'referrer_url' => $this->safeUrl($session['referrer_url'] ?? null),
            'ip_address' => $collectIp ? $this->truncate((string)($serverContext['ip_address'] ?? ''), 45) : null,
            'user_agent' => $collectUa ? $this->truncate((string)($serverContext['user_agent'] ?? ''), 255) : null,
            'device_type' => $this->truncate((string)($session['device_type'] ?? ''), 32),
            'browser_name' => $this->truncate((string)($session['browser_name'] ?? ''), 64),
            'browser_version' => $this->truncate((string)($session['browser_version'] ?? ''), 64),
            'os_name' => $this->truncate((string)($session['os_name'] ?? ''), 64),
            'os_version' => $this->truncate((string)($session['os_version'] ?? ''), 64),
            'country' => $this->truncate((string)($session['country'] ?? ''), 80),
            'region' => $this->truncate((string)($session['region'] ?? ''), 120),
            'city' => $this->truncate((string)($session['city'] ?? ''), 120),
            'latitude' => isset($session['latitude']) ? (float) $session['latitude'] : null,
            'longitude' => isset($session['longitude']) ? (float) $session['longitude'] : null,
            'utm_source' => $this->truncate((string)($session['utm_source'] ?? ''), 128),
            'utm_medium' => $this->truncate((string)($session['utm_medium'] ?? ''), 128),
            'utm_campaign' => $this->truncate((string)($session['utm_campaign'] ?? ''), 128),
            'utm_term' => $this->truncate((string)($session['utm_term'] ?? ''), 128),
            'utm_content' => $this->truncate((string)($session['utm_content'] ?? ''), 128),
            'consent_status' => $this->truncate((string)($session['consent_status'] ?? 'unknown'), 32),
        ];

        if ($sessionData['browser_session_uuid'] === '') {
            throw new \InvalidArgumentException('Missing browser_session_uuid.');
        }

        $this->pdo->beginTransaction();
        try {
            $this->repository->createOrUpdateSession($tenantId, $sessionData);
            $sessionId = $this->repository->findSessionIdByUuid($tenantId, $sessionData['browser_session_uuid']) ?? 0;
            if ($sessionId <= 0) {
                throw new \RuntimeException('Unable to resolve session.');
            }

            $pageviewId = null;
            if ($pageview !== []) {
                $pageviewId = $this->repository->insertPageview($tenantId, [
                    'session_id' => $sessionId,
                    'user_id' => $userId > 0 ? $userId : null,
                    'campaign_id' => isset($pageview['campaign_id']) ? (int)$pageview['campaign_id'] : null,
                    'landing_page_id' => isset($pageview['landing_page_id']) ? (int)$pageview['landing_page_id'] : null,
                    'short_link_id' => isset($pageview['short_link_id']) ? (int)$pageview['short_link_id'] : null,
                    'crm_lead_id' => isset($pageview['crm_lead_id']) ? (int)$pageview['crm_lead_id'] : null,
                    'page_url' => $this->safeUrl($pageview['page_url'] ?? null),
                    'page_title' => $this->truncate((string)($pageview['page_title'] ?? ''), 255),
                    'referrer_url' => $this->safeUrl($pageview['referrer_url'] ?? null),
                    'path' => $this->truncate((string)($pageview['path'] ?? ''), 255),
                    'query_string' => $this->truncate((string)($pageview['query_string'] ?? ''), 500),
                    'hash_fragment' => $this->truncate((string)($pageview['hash_fragment'] ?? ''), 255),
                    'viewed_at' => (string)($pageview['viewed_at'] ?? date('Y-m-d H:i:s')),
                    'duration_ms' => isset($pageview['duration_ms']) ? max(0, (int)$pageview['duration_ms']) : null,
                    'scroll_depth_percent' => isset($pageview['scroll_depth_percent']) ? max(0.0, min(100.0, (float)$pageview['scroll_depth_percent'])) : null,
                    'is_landing_view' => empty($pageview['is_landing_view']) ? 0 : 1,
                    'is_campaign_view' => empty($pageview['is_campaign_view']) ? 0 : 1,
                    'meta_json' => $this->sanitizeJson($pageview['meta_json'] ?? null),
                ]);
            }

            $eventId = null;
            if ($event !== []) {
                $eventId = $this->repository->insertEvent($tenantId, [
                    'session_id' => $sessionId,
                    'pageview_id' => isset($event['pageview_id']) ? (int)$event['pageview_id'] : $pageviewId,
                    'user_id' => $userId > 0 ? $userId : null,
                    'campaign_id' => isset($event['campaign_id']) ? (int)$event['campaign_id'] : null,
                    'landing_page_id' => isset($event['landing_page_id']) ? (int)$event['landing_page_id'] : null,
                    'short_link_id' => isset($event['short_link_id']) ? (int)$event['short_link_id'] : null,
                    'crm_lead_id' => isset($event['crm_lead_id']) ? (int)$event['crm_lead_id'] : null,
                    'event_type' => $this->truncate((string)($event['event_type'] ?? ''), 64),
                    'event_name' => $this->truncate((string)($event['event_name'] ?? ''), 120),
                    'element_id' => $this->truncate((string)($event['element_id'] ?? ''), 120),
                    'element_text' => $this->truncate((string)($event['element_text'] ?? ''), 255),
                    'element_url' => $this->safeUrl($event['element_url'] ?? null),
                    'value_numeric' => isset($event['value_numeric']) ? (float)$event['value_numeric'] : null,
                    'value_text' => $this->truncate((string)($event['value_text'] ?? ''), 255),
                    'score_points' => isset($event['score_points']) ? (int)$event['score_points'] : null,
                    'metadata_json' => $this->sanitizeJson($event['metadata_json'] ?? null),
                    'occurred_at' => (string)($event['occurred_at'] ?? date('Y-m-d H:i:s')),
                ]);
            }

            $this->pdo->commit();
            return ['ok' => true, 'session_id' => $sessionId, 'pageview_id' => $pageviewId, 'event_id' => $eventId];
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    private function truncate(string $value, int $max): string { return mb_substr(trim($value), 0, $max); }
    private function safeUrl(mixed $value): ?string { $url = trim((string)$value); return ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) ? mb_substr($url, 0, 1024) : null; }
    private function sanitizeJson(mixed $value): ?string { if ($value === null || $value === '') { return null; } $decoded = is_string($value) ? json_decode($value, true) : $value; if (!is_array($decoded)) { return null; } return mb_substr((string)json_encode($decoded, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), 0, 4000); }
}
