<?php

declare(strict_types=1);

namespace App\Core\Landing;

final readonly class EcosistemaLandingPublicRenderDryRunService
{
    public function __construct(private EcosistemaLandingPageRepository $repository)
    {
    }

    public function simulate(int $tenantId, int $pageId, bool $enabled): array
    {
        if ($pageId <= 0) {
            return $this->blocked('ID de landing page inválido.');
        }

        if (!$enabled) {
            return $this->blocked('Dry-run de render público deshabilitado por configuración.');
        }

        $page = $this->repository->findPage($tenantId, $pageId);
        if ($page === null) {
            return $this->blocked('Landing page no encontrada para el tenant actual.');
        }

        $status = strtolower(trim((string) ($page['status'] ?? '')));
        if ($status !== 'published') {
            return $this->blocked('Landing page no publicada; render público simulado bloqueado.', $page);
        }

        $publishedVersion = $this->repository->findPublishedVersion($tenantId, $pageId);
        if ($publishedVersion === null) {
            return $this->blocked('No existe versión publicada para esta landing page.', $page);
        }

        $blocks = $this->repository->listBlocksByVersion($tenantId, $pageId, (int) $publishedVersion['id']);

        return [
            'allowed' => true,
            'reason' => null,
            'mode' => 'dry-run',
            'db_write' => false,
            'visit_tracking_write' => false,
            'form_processing_write' => false,
            'page' => $this->safePage($page),
            'published_version' => $this->safeVersion($publishedVersion),
            'blocks' => array_map(fn(array $block): array => $this->safeBlock($block), $blocks),
            'sensitive_data' => [
                'template_json_exposed' => false,
                'layout_json_exposed' => false,
                'custom_css_exposed' => false,
                'custom_js_exposed' => false,
                'settings_json_exposed' => false,
                'content_json_exposed' => false,
                'public_url_exposed' => false,
            ],
        ];
    }

    private function blocked(string $reason, ?array $page = null): array
    {
        return [
            'allowed' => false,
            'reason' => $reason,
            'mode' => 'dry-run',
            'db_write' => false,
            'visit_tracking_write' => false,
            'form_processing_write' => false,
            'page' => is_array($page) ? $this->safePage($page) : null,
            'published_version' => null,
            'blocks' => [],
            'sensitive_data' => [
                'template_json_exposed' => false,
                'layout_json_exposed' => false,
                'custom_css_exposed' => false,
                'custom_js_exposed' => false,
                'settings_json_exposed' => false,
                'content_json_exposed' => false,
                'public_url_exposed' => false,
            ],
        ];
    }

    private function safePage(array $page): array
    {
        return [
            'id' => (int) ($page['id'] ?? 0),
            'title' => (string) ($page['title'] ?? ''),
            'slug' => (string) ($page['slug'] ?? ''),
            'status' => (string) ($page['status'] ?? ''),
            'public_url_present' => trim((string) ($page['public_url'] ?? '')) !== '',
            'public_url_preview' => $this->preview((string) ($page['public_url'] ?? ''), 48),
            'template_json_present' => trim((string) ($page['template_json'] ?? '')) !== '',
            'custom_head_html_present' => trim((string) ($page['custom_head_html'] ?? '')) !== '',
            'custom_body_html_present' => trim((string) ($page['custom_body_html'] ?? '')) !== '',
        ];
    }

    private function safeVersion(array $version): array
    {
        return [
            'id' => (int) ($version['id'] ?? 0),
            'version_no' => (int) ($version['version_no'] ?? 0),
            'title' => (string) ($version['title'] ?? ''),
            'is_published' => ((int) ($version['is_published'] ?? 0)) === 1,
            'layout_json_present' => trim((string) ($version['layout_json'] ?? '')) !== '',
            'custom_css_present' => trim((string) ($version['custom_css'] ?? '')) !== '',
            'custom_js_present' => trim((string) ($version['custom_js'] ?? '')) !== '',
            'layout_json_preview' => $this->preview((string) ($version['layout_json'] ?? ''), 120),
        ];
    }

    private function safeBlock(array $block): array
    {
        return [
            'id' => (int) ($block['id'] ?? 0),
            'parent_block_id' => isset($block['parent_block_id']) ? (int) $block['parent_block_id'] : null,
            'block_type' => (string) ($block['block_type'] ?? ''),
            'name' => (string) ($block['name'] ?? ''),
            'sort_order' => (int) ($block['sort_order'] ?? 0),
            'is_active' => ((int) ($block['is_active'] ?? 0)) === 1,
            'settings_json_present' => trim((string) ($block['settings_json'] ?? '')) !== '',
            'content_json_present' => trim((string) ($block['content_json'] ?? '')) !== '',
            'settings_json_preview' => $this->preview((string) ($block['settings_json'] ?? ''), 120),
            'content_json_preview' => $this->preview((string) ($block['content_json'] ?? ''), 120),
        ];
    }

    private function preview(string $value, int $max): ?string
    {
        $trim = trim($value);
        if ($trim == '') {
            return null;
        }

        $head = mb_substr($trim, 0, $max);
        return $head === $trim ? $head : $head . '…';
    }
}
