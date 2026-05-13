<?php

declare(strict_types=1);

namespace App\Core\UrlLocator;

final readonly class EcosistemaUrlLocatorLinkService
{
    public function __construct(private EcosistemaUrlLocatorLinkRepository $repository, private EcosistemaUrlLocatorAdapter $adapter)
    {
    }

    public function listLinks(int $tenantId, int $limit = 100): array
    {
        $summary = $this->repository->summarizeLinks($tenantId);
        $links = array_map(fn(array $r): array => $this->toSafeDto($r), $this->repository->listRecentLinks($tenantId, $limit));

        return ['summary' => $summary, 'links' => $links, 'capabilities' => $this->adapter->capabilities()];
    }

    public function getLinkDetail(int $tenantId, int $linkId): ?array
    {
        if ($linkId <= 0) {
            return null;
        }

        $row = $this->repository->findLink($tenantId, $linkId);
        if ($row === null) {
            return null;
        }

        $detail = $this->toSafeDto($row);
        $detail['original_url_after_ads_preview'] = $this->safePreview((string) ($row['original_url_after_ads'] ?? ''), 64);
        $detail['language_fallback_url_present'] = trim((string) ($row['language_fallback_url'] ?? '')) !== '';
        $detail['language_fallback_url_exposed'] = false;
        $detail['language_query_param'] = (string) ($row['language_query_param'] ?? '');
        $detail['utm_preview_safe'] = $this->utmPreviewSafe($row);
        $detail['languages'] = array_map(fn(array $l): array => [
            'language_code' => (string) ($l['language_code'] ?? ''),
            'target_url_present' => trim((string) ($l['target_url'] ?? '')) !== '',
            'target_url_exposed' => false,
            'priority' => isset($l['priority']) ? (int) $l['priority'] : null,
            'is_default_for_language' => (bool) ($l['is_default_for_language'] ?? false),
            'is_active' => (bool) ($l['is_active'] ?? false),
            'click_count' => (int) ($l['click_count'] ?? 0),
        ], $this->repository->listLinkLanguages($tenantId, $linkId));

        $smart = $this->repository->findSmartSettings($tenantId, $linkId);
        $detail['smart_settings'] = $smart === null ? null : [
            'smart_type' => (string) ($detail['smart_type'] ?? ''),
            'show_access_counter' => (bool) ($smart['show_access_counter'] ?? false),
            'track_location' => (bool) ($smart['track_location'] ?? false),
            'track_attachments' => (bool) ($smart['track_attachments'] ?? false),
            'track_final_click' => (bool) ($smart['track_final_click'] ?? false),
            'allow_indexing' => (bool) ($smart['allow_indexing'] ?? false),
            'require_consent' => (bool) ($smart['require_consent'] ?? false),
            'custom_css_present' => trim((string) ($smart['custom_css'] ?? '')) !== '',
            'custom_css_exposed' => false,
            'custom_js_present' => trim((string) ($smart['custom_js'] ?? '')) !== '',
            'custom_js_exposed' => false,
        ];

        $detail['message_templates_summary'] = array_map(fn(array $m): array => [
            'id' => (int) ($m['id'] ?? 0),
            'template_name' => (string) ($m['template_name'] ?? ''),
            'language_code' => (string) ($m['language_code'] ?? ''),
            'status' => (string) ($m['status'] ?? ''),
            'view_count' => (int) ($m['view_count'] ?? 0),
            'body_html_present' => trim((string) ($m['body_html'] ?? '')) !== '',
            'body_html_exposed' => false,
        ], $this->repository->listLinkMessageTemplates($tenantId, $linkId));

        $detail['ad_interstitials_summary'] = array_map(fn(array $a): array => [
            'id' => (int) ($a['id'] ?? 0),
            'title' => (string) ($a['title'] ?? ''),
            'ad_type' => (string) ($a['ad_type'] ?? ''),
            'status' => (string) ($a['status'] ?? ''),
            'impression_count' => (int) ($a['impression_count'] ?? 0),
            'click_count' => (int) ($a['click_count'] ?? 0),
            'media_s3_key_present' => trim((string) ($a['media_s3_key'] ?? '')) !== '',
            'media_s3_key_exposed' => false,
            'ad_html_present' => trim((string) ($a['ad_html'] ?? '')) !== '',
            'ad_html_exposed' => false,
        ], $this->repository->listLinkAdInterstitials($tenantId, $linkId));

        $detail['redirect_enabled'] = false;
        $detail['tracking_write'] = false;

        return $detail;
    }

    private function toSafeDto(array $row): array
    {
        $target = trim((string) ($row['target_url'] ?? ''));
        $after = trim((string) ($row['original_url_after_ads'] ?? ''));
        $hash = trim((string) ($row['access_token_hash'] ?? ''));

        return [
            'id' => (int) ($row['id'] ?? 0),
            'slug' => (string) ($row['slug'] ?? ''),
            'title' => (string) ($row['title'] ?? ''),
            'description_preview' => mb_substr(trim((string) ($row['description'] ?? '')), 0, 120),
            'status' => (string) ($row['status'] ?? ''),
            'smart_type' => (string) ($row['smart_type'] ?? ''),
            'smart_type_label' => $this->smartTypeLabel((string) ($row['smart_type'] ?? '')),
            'campaign_id' => isset($row['campaign_id']) ? (int) $row['campaign_id'] : null,
            'campaign_name' => (string) ($row['campaign_name'] ?? ''),
            'landing_page_id' => isset($row['landing_page_id']) ? (int) $row['landing_page_id'] : null,
            'landing_page_title' => (string) ($row['landing_page_title'] ?? ''),
            'created_by_user_id' => isset($row['created_by_user_id']) ? (int) $row['created_by_user_id'] : null,
            'created_by_label' => (string) ($row['created_by_label'] ?? ''),
            'target_url_present' => $target !== '',
            'target_url_preview' => $this->safePreview($target, 64),
            'target_url_exposed' => false,
            'original_url_after_ads_present' => $after !== '',
            'original_url_after_ads_exposed' => false,
            'requires_access_token' => (bool) ($row['requires_access_token'] ?? false),
            'access_token_hash_present' => $hash !== '',
            'access_token_hash_exposed' => false,
            'default_language_code' => (string) ($row['default_language_code'] ?? ''),
            'language_detection_enabled' => (bool) ($row['language_detection_enabled'] ?? false),
            'expires_at' => $row['expires_at'] ?? null,
            'max_clicks' => $row['max_clicks'] !== null ? (int) $row['max_clicks'] : null,
            'click_count' => (int) ($row['click_count'] ?? 0),
            'utm_present' => $this->utmPresent($row),
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
            'mode' => 'read-only',
            'db_write' => false,
            'public_redirect' => false,
            'tracking_write' => false,
        ];
    }

    private function safePreview(string $value, int $length): ?string
    {
        $trim = trim($value);
        return $trim === '' ? null : mb_substr($trim, 0, $length) . '…';
    }

    private function smartTypeLabel(string $s): string
    {
        return match ($s) {
            'direct' => 'Directo',
            'ads' => 'Con anuncios',
            'smart' => 'Smart',
            'interstitial' => 'Interstitial',
            default => $s === '' ? 'Sin definir' : $s,
        };
    }

    private function utmPresent(array $row): bool
    {
        return trim((string) ($row['utm_source'] ?? '')) !== '' || trim((string) ($row['utm_medium'] ?? '')) !== '' || trim((string) ($row['utm_campaign'] ?? '')) !== '' || trim((string) ($row['utm_term'] ?? '')) !== '' || trim((string) ($row['utm_content'] ?? '')) !== '';
    }

    private function utmPreviewSafe(array $row): string
    {
        $parts = [];
        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as $key) {
            if (trim((string) ($row[$key] ?? '')) !== '') {
                $parts[] = $key;
            }
        }

        return $parts === [] ? 'none' : implode(', ', $parts);
    }
}
