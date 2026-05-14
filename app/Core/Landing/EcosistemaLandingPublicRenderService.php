<?php

declare(strict_types=1);

namespace App\Core\Landing;

final readonly class EcosistemaLandingPublicRenderService
{
    public function __construct(private EcosistemaLandingPageRepository $repository)
    {
    }

    public function renderBySlug(int $tenantId, string $slug, bool $enabled): array
    {
        $safeSlug = trim($slug);
        if ($tenantId <= 0 || $safeSlug === '') {
            return $this->blocked('Landing no disponible.');
        }

        if (!$enabled) {
            return $this->blocked('Landing no disponible.');
        }

        $page = $this->repository->findPublishedPageBySlug($tenantId, $safeSlug);
        if ($page === null) {
            return $this->blocked('Landing no disponible.');
        }

        if (!$this->isPublicationWindowOpen($page)) {
            return $this->blocked('Landing no disponible.');
        }

        $publishedVersion = $this->repository->findPublishedVersion($tenantId, (int) ($page['id'] ?? 0));
        if ($publishedVersion === null) {
            return $this->blocked('Landing no disponible.');
        }

        $blocks = $this->repository->listBlocksByVersion($tenantId, (int) $page['id'], (int) $publishedVersion['id']);

        return [
            'allowed' => true,
            'reason' => null,
            'db_write' => false,
            'visit_tracking_write' => false,
            'form_processing_write' => false,
            'page' => [
                'id' => (int) ($page['id'] ?? 0),
                'title' => (string) ($page['title'] ?? ''),
                'slug' => (string) ($page['slug'] ?? ''),
                'description' => (string) ($page['description'] ?? ''),
                'seo_title' => (string) ($page['seo_title'] ?? ''),
                'seo_description' => (string) ($page['seo_description'] ?? ''),
            ],
            'published_version' => [
                'id' => (int) ($publishedVersion['id'] ?? 0),
                'version_no' => (int) ($publishedVersion['version_no'] ?? 0),
                'title' => (string) ($publishedVersion['title'] ?? ''),
            ],
            'blocks' => $this->sanitizeBlocks($blocks),
            'sensitive_data' => [
                'custom_head_html_exposed' => false,
                'custom_body_html_exposed' => false,
                'layout_json_exposed' => false,
                'custom_css_exposed' => false,
                'custom_js_exposed' => false,
                'settings_json_exposed' => false,
                'content_json_exposed' => false,
            ],
        ];
    }

    private function blocked(string $reason): array
    {
        return [
            'allowed' => false,
            'reason' => $reason,
            'db_write' => false,
            'visit_tracking_write' => false,
            'form_processing_write' => false,
            'page' => null,
            'published_version' => null,
            'blocks' => [],
            'sensitive_data' => [
                'custom_head_html_exposed' => false,
                'custom_body_html_exposed' => false,
                'layout_json_exposed' => false,
                'custom_css_exposed' => false,
                'custom_js_exposed' => false,
                'settings_json_exposed' => false,
                'content_json_exposed' => false,
            ],
        ];
    }

    private function sanitizeBlocks(array $blocks): array
    {
        $safe = [];
        foreach ($blocks as $block) {
            $safe[] = [
                'id' => (int) ($block['id'] ?? 0),
                'parent_block_id' => isset($block['parent_block_id']) ? (int) $block['parent_block_id'] : null,
                'block_type' => (string) ($block['block_type'] ?? ''),
                'name' => (string) ($block['name'] ?? ''),
                'sort_order' => (int) ($block['sort_order'] ?? 0),
                'settings_json_present' => trim((string) ($block['settings_json'] ?? '')) !== '',
                'content_json_present' => trim((string) ($block['content_json'] ?? '')) !== '',
            ];
        }

        return $safe;
    }

    private function isPublicationWindowOpen(array $page): bool
    {
        $now = time();
        $publishedAt = trim((string) ($page['published_at'] ?? ''));
        $unpublishedAt = trim((string) ($page['unpublished_at'] ?? ''));

        if ($publishedAt !== '' && strtotime($publishedAt) !== false && strtotime($publishedAt) > $now) {
            return false;
        }

        if ($unpublishedAt !== '' && strtotime($unpublishedAt) !== false && strtotime($unpublishedAt) <= $now) {
            return false;
        }

        return true;
    }
}
