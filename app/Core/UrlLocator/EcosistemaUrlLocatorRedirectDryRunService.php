<?php

declare(strict_types=1);

namespace App\Core\UrlLocator;

final readonly class EcosistemaUrlLocatorRedirectDryRunService
{
    public function __construct(private EcosistemaUrlLocatorLinkRepository $repository)
    {
    }

    public function resolveByLinkId(int $tenantId, int $linkId, array $context = []): array
    {
        $result = $this->baseResult($linkId);
        if ($tenantId <= 0 || $linkId <= 0) {
            $result['blocked_reason'] = 'invalid_request';
            return $result;
        }

        $link = $this->repository->findLink($tenantId, $linkId);
        if ($link === null) {
            $result['blocked_reason'] = 'link_not_found';
            return $result;
        }

        $requested = $this->normalizeLanguage((string) ($context['requested_language'] ?? ''));
        $detected = $this->normalizeLanguage($this->detectLanguage((string) ($context['accept_language_header'] ?? '')));
        $now = $this->resolveNow((string) ($context['preview_now'] ?? ''));
        $status = strtolower(trim((string) ($link['status'] ?? 'inactive')));
        $smartType = (int) ($link['smart_type'] ?? 3);

        $result['link_id'] = (int) ($link['id'] ?? 0);
        $result['slug'] = (string) ($link['slug'] ?? '');
        $result['status'] = $status;
        $result['smart_type'] = $smartType;
        $result['smart_type_label'] = $this->smartTypeLabel($smartType);
        $result['language_detection_enabled'] = (bool) ($link['language_detection_enabled'] ?? false);
        $result['requested_language'] = $requested;
        $result['detected_language'] = $detected;
        $result['access_token_required'] = (bool) ($link['requires_access_token'] ?? false);
        $result['access_token_checked'] = false;
        $result['original_url_after_ads_present'] = trim((string) ($link['original_url_after_ads'] ?? '')) !== '';

        if ($status !== 'active') {
            $result['blocked_reason'] = 'status_' . $status;
            return $result;
        }

        $expiresAt = trim((string) ($link['expires_at'] ?? ''));
        if ($expiresAt !== '' && strtotime($expiresAt) !== false && strtotime($expiresAt) < $now) {
            $result['blocked_reason'] = 'expired_at';
            return $result;
        }

        $maxClicks = isset($link['max_clicks']) ? (int) $link['max_clicks'] : null;
        $clickCount = (int) ($link['click_count'] ?? 0);
        if ($maxClicks !== null && $maxClicks > 0 && $clickCount >= $maxClicks) {
            $result['blocked_reason'] = 'max_clicks_reached';
            return $result;
        }

        $selected = $this->resolveLanguage($tenantId, $linkId, $link, $requested, $detected, $result);
        $target = $this->resolveTarget($smartType, $link, $selected, $result);

        $result['selected_language'] = $selected['selected_language'];
        $result['fallback_used'] = $selected['fallback_used'];
        $result['target_url_present'] = $target !== null;
        $result['target_url_preview'] = $target !== null ? $this->safePreview($target, 64) : null;
        $result['would_show_interstitial'] = $smartType === 0 && $result['original_url_after_ads_present'];
        $result['would_show_message_template'] = in_array($smartType, [1, 2], true);
        $result['eligible'] = $target !== null;
        $result['would_redirect'] = $target !== null;
        if ($target === null) {
            $result['blocked_reason'] = 'target_unavailable';
        }

        return $result;
    }

    private function baseResult(int $linkId): array
    {
        return [
            'mode' => 'dry-run', 'redirect_executed' => false, 'db_write' => false, 'click_logged' => false,
            'click_count_incremented' => false, 'public_redirects' => false, 'link_id' => $linkId, 'slug' => null,
            'status' => null, 'eligible' => false, 'blocked_reason' => null, 'smart_type' => null,
            'smart_type_label' => 'unknown', 'language_detection_enabled' => false, 'requested_language' => null,
            'detected_language' => null, 'selected_language' => null, 'fallback_used' => false,
            'target_url_present' => false, 'target_url_preview' => null, 'target_url_exposed' => false,
            'original_url_after_ads_present' => false, 'original_url_after_ads_exposed' => false,
            'access_token_required' => false, 'access_token_checked' => false, 'would_show_interstitial' => false,
            'would_show_message_template' => false, 'would_redirect' => false, 'would_log_click' => false,
            'warnings' => [],
        ];
    }

    private function resolveLanguage(int $tenantId, int $linkId, array $link, ?string $requested, ?string $detected, array &$result): array
    {
        $langs = $this->repository->listLinkLanguages($tenantId, $linkId);
        $active = [];
        foreach ($langs as $row) {
            if (!empty($row['is_active']) && trim((string) ($row['target_url'] ?? '')) !== '') {
                $active[strtolower((string) ($row['language_code'] ?? ''))] = (string) $row['target_url'];
            }
        }

        $selected = null;
        $fallbackUsed = false;
        if (!empty($link['language_detection_enabled'])) {
            if ($requested !== null && isset($active[$requested])) {
                $selected = $requested;
            } elseif ($detected !== null && isset($active[$detected])) {
                $selected = $detected;
            }
        }

        $default = $this->normalizeLanguage((string) ($link['default_language_code'] ?? ''));
        if ($selected === null && $default !== null && isset($active[$default])) {
            $selected = $default;
            $fallbackUsed = true;
        }

        if ($selected === null && $active !== []) {
            $selected = array_key_first($active);
            $fallbackUsed = true;
            $result['warnings'][] = 'default_language_missing_or_inactive';
        }

        return ['selected_language' => $selected, 'fallback_used' => $fallbackUsed, 'active' => $active];
    }

    private function resolveTarget(int $smartType, array $link, array $language): ?string
    {
        if ($smartType === 3 && $language['selected_language'] !== null) {
            return $language['active'][$language['selected_language']] ?? null;
        }

        if ($smartType === 0) {
            $after = trim((string) ($link['original_url_after_ads'] ?? ''));
            return $after !== '' ? $after : null;
        }

        if (in_array($smartType, [1, 2], true)) {
            return trim((string) ($link['target_url'] ?? '')) !== '' ? (string) $link['target_url'] : null;
        }

        $fallback = trim((string) ($link['language_fallback_url'] ?? ''));
        if ($fallback !== '') {
            return $fallback;
        }

        $target = trim((string) ($link['target_url'] ?? ''));
        return $target !== '' ? $target : null;
    }

    private function detectLanguage(string $acceptLanguage): ?string
    {
        $head = trim(explode(',', $acceptLanguage)[0] ?? '');
        if ($head === '') { return null; }
        $code = strtolower(trim(explode(';', $head)[0]));
        return substr($code, 0, 2);
    }

    private function normalizeLanguage(string $value): ?string
    {
        $clean = strtolower(trim($value));
        if (!preg_match('/^[a-z]{2,5}(-[a-z]{2})?$/', $clean)) { return null; }
        return substr($clean, 0, 2);
    }

    private function resolveNow(string $previewNow): int
    {
        $t = trim($previewNow);
        if ($t !== '' && strtotime($t) !== false) { return (int) strtotime($t); }
        return time();
    }

    private function safePreview(string $value, int $length): string
    {
        return mb_substr(trim($value), 0, $length) . '…';
    }

    private function smartTypeLabel(int $smartType): string
    {
        return match ($smartType) { 0 => 'ads_interstitial', 1 => 'message_template', 2 => 'message_template_attachments', 3 => 'multilanguage_url', default => 'unknown' };
    }
}
