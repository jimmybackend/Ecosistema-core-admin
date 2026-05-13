<?php

declare(strict_types=1);

namespace App\Core\UrlLocator;

final class EcosistemaUrlLocatorLinkWriteService
{
    private array $reserved = ['admin','login','logout','dashboard','cloud','url','api','assets','public'];

    public function __construct(private EcosistemaUrlLocatorLinkWriteRepository $repository, private array $config)
    {
    }

    public function writeEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false) && (bool) ($this->config['admin_write_enabled'] ?? false);
    }

    public function create(int $tenantId, int $userId, array $input): array
    {
        $data = $this->validateAndNormalize($tenantId, $input, null);
        if ($data['errors'] !== []) { return $data; }
        $id = $this->repository->createLink($tenantId, $userId, $data['values']);
        return ['errors' => [], 'id' => $id, 'values' => $data['values']];
    }

    public function update(int $tenantId, int $linkId, array $input): array
    {
        $data = $this->validateAndNormalize($tenantId, $input, $linkId);
        if ($data['errors'] !== []) { return $data; }
        $ok = $this->repository->updateLink($tenantId, $linkId, $data['values']);
        return ['errors' => $ok ? [] : ['No se pudo actualizar el short link.'], 'id' => $linkId, 'values' => $data['values']];
    }

    private function validateAndNormalize(int $tenantId, array $input, ?int $exceptId): array
    {
        $errors = [];
        $slug = trim((string) ($input['slug'] ?? ''));
        if (!preg_match('/^[A-Za-z0-9_-]{3,120}$/', $slug) || str_starts_with($slug, '.')) { $errors[] = 'Slug inválido.'; }
        if (in_array(strtolower($slug), $this->reserved, true)) { $errors[] = 'Slug reservado.'; }
        if ($slug !== '' && $this->repository->slugExists($tenantId, $slug, $exceptId)) { $errors[] = 'Slug duplicado.'; }

        $targetUrl = trim((string) ($input['target_url'] ?? ''));
        if (!$this->isSafeUrl($targetUrl)) { $errors[] = 'target_url inválida o insegura.'; }

        $status = trim((string) ($input['status'] ?? 'inactive'));
        if (!in_array($status, (array) ($this->config['allowed_statuses'] ?? []), true)) { $errors[] = 'Status inválido.'; }

        $smartType = (int) ($input['smart_type'] ?? 0);
        if (!in_array($smartType, (array) ($this->config['allowed_smart_types'] ?? []), true)) { $errors[] = 'smart_type inválido.'; }

        $original = trim((string) ($input['original_url_after_ads'] ?? ''));
        if ($original !== '' && $smartType !== 0) { $errors[] = 'original_url_after_ads sólo aplica para smart_type=0.'; }
        if ($original !== '' && !$this->isSafeUrl($original)) { $errors[] = 'original_url_after_ads inválida.'; }

        $langFallback = trim((string) ($input['language_fallback_url'] ?? ''));
        if ($langFallback !== '' && !$this->isSafeUrl($langFallback)) { $errors[] = 'language_fallback_url inválida.'; }

        $defaultLang = trim((string) ($input['default_language_code'] ?? ''));
        if ($defaultLang !== '' && !$this->repository->languageIsActive($defaultLang)) { $errors[] = 'default_language_code inválido.'; }

        $campaignId = $this->nullableInt($input['campaign_id'] ?? null);
        if ($campaignId !== null && !$this->repository->campaignBelongsToTenant($tenantId, $campaignId)) { $errors[] = 'campaign_id no pertenece al tenant.'; }
        $landingPageId = $this->nullableInt($input['landing_page_id'] ?? null);
        if ($landingPageId !== null && !$this->repository->landingPageBelongsToTenant($tenantId, $landingPageId)) { $errors[] = 'landing_page_id no pertenece al tenant.'; }

        $maxClicks = $this->nullableInt($input['max_clicks'] ?? null);
        if ($maxClicks !== null && $maxClicks <= 0) { $errors[] = 'max_clicks debe ser positivo.'; }

        $expiresAt = trim((string) ($input['expires_at'] ?? ''));
        $expiresAt = $expiresAt === '' ? null : $expiresAt;
        if ($expiresAt !== null && strtotime($expiresAt) === false) { $errors[] = 'expires_at inválido.'; }

        $languageQuery = trim((string) ($input['language_query_param'] ?? 'lang'));
        if (!preg_match('/^[a-zA-Z0-9_]{1,32}$/', $languageQuery)) { $errors[] = 'language_query_param inválido.'; }

        $values = ['slug'=>$slug,'target_url'=>$targetUrl,'original_url_after_ads'=>$original !== '' ? $original : null,'default_language_code'=>$defaultLang !== '' ? $defaultLang : null,'language_detection_enabled'=>!empty($input['language_detection_enabled']) ? 1 : 0,'language_fallback_url'=>$langFallback !== '' ? $langFallback : null,'language_query_param'=>$languageQuery,'title'=>mb_substr(trim((string)($input['title'] ?? '')),0,255),'description'=>mb_substr(trim((string)($input['description'] ?? '')),0,2000),'status'=>$status,'smart_type'=>$smartType,'expires_at'=>$expiresAt,'max_clicks'=>$maxClicks,'utm_source'=>mb_substr(trim((string)($input['utm_source'] ?? '')),0,120),'utm_medium'=>mb_substr(trim((string)($input['utm_medium'] ?? '')),0,120),'utm_campaign'=>mb_substr(trim((string)($input['utm_campaign'] ?? '')),0,120),'utm_term'=>mb_substr(trim((string)($input['utm_term'] ?? '')),0,120),'utm_content'=>mb_substr(trim((string)($input['utm_content'] ?? '')),0,120),'campaign_id'=>$campaignId,'landing_page_id'=>$landingPageId];

        return ['errors' => $errors, 'values' => $values];
    }

    private function nullableInt(mixed $value): ?int { if ($value === null || $value === '') return null; return (int) $value; }
    private function isSafeUrl(string $url): bool
    {
        if ($url === '' || strlen($url) > 2048) return false;
        $parsed = parse_url($url);
        $scheme = strtolower((string)($parsed['scheme'] ?? ''));
        if (!in_array($scheme, ['http','https'], true)) return false;
        $host = strtolower((string)($parsed['host'] ?? ''));
        if (in_array($host, ['localhost','127.0.0.1','0.0.0.0'], true)) return false;
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false && filter_var($host, FILTER_VALIDATE_IP)) return false;
        return true;
    }
}
