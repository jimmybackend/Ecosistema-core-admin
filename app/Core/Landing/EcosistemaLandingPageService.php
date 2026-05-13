<?php

declare(strict_types=1);

namespace App\Core\Landing;

final readonly class EcosistemaLandingPageService
{
    public function __construct(private EcosistemaLandingPageRepository $repository, private EcosistemaLandingAdapter $adapter)
    {
    }

    public function listPages(int $tenantId, int $limit = 100): array
    {
        return [
            'summary' => $this->repository->summarizePages($tenantId),
            'pages' => array_map(fn(array $row): array => $this->toSafeDto($row), $this->repository->listRecentPages($tenantId, $limit)),
            'capabilities' => $this->adapter->capabilities(),
        ];
    }

    public function getPageDetail(int $tenantId, int $pageId): ?array
    {
        if ($pageId <= 0) {
            return null;
        }

        $page = $this->repository->findPage($tenantId, $pageId);
        if ($page === null) {
            return null;
        }

        return array_merge($this->toSafeDto($page), [
            'versions_summary' => array_map(fn(array $version): array => [
                'id' => (int) ($version['id'] ?? 0),
                'version_no' => (int) ($version['version_no'] ?? 0),
                'title' => (string) ($version['title'] ?? ''),
                'is_published' => (bool) ($version['is_published'] ?? false),
                'created_at' => $version['created_at'] ?? null,
                'layout_json_present' => trim((string) ($version['layout_json'] ?? '')) !== '',
                'layout_json_exposed' => false,
                'custom_css_present' => trim((string) ($version['custom_css'] ?? '')) !== '',
                'custom_css_exposed' => false,
                'custom_js_present' => trim((string) ($version['custom_js'] ?? '')) !== '',
                'custom_js_exposed' => false,
            ], $this->repository->listPageVersions($tenantId, $pageId)),
            'blocks_summary' => array_map(fn(array $block): array => [
                'id' => (int) ($block['id'] ?? 0),
                'version_id' => isset($block['version_id']) ? (int) $block['version_id'] : null,
                'parent_block_id' => isset($block['parent_block_id']) ? (int) $block['parent_block_id'] : null,
                'block_type' => (string) ($block['block_type'] ?? ''),
                'name' => (string) ($block['name'] ?? ''),
                'sort_order' => (int) ($block['sort_order'] ?? 0),
                'is_active' => (bool) ($block['is_active'] ?? false),
                'created_at' => $block['created_at'] ?? null,
                'updated_at' => $block['updated_at'] ?? null,
                'settings_json_present' => trim((string) ($block['settings_json'] ?? '')) !== '',
                'settings_json_exposed' => false,
                'content_json_present' => trim((string) ($block['content_json'] ?? '')) !== '',
                'content_json_exposed' => false,
            ], $this->repository->listPageBlocks($tenantId, $pageId)),
            'template_json_present' => trim((string) ($page['template_json'] ?? '')) !== '',
            'template_json_exposed' => false,
        ]);
    }

    private function toSafeDto(array $row): array
    {
        return [
            'id' => (int) ($row['id'] ?? 0),
            'title' => (string) ($row['title'] ?? ''),
            'slug' => (string) ($row['slug'] ?? ''),
            'description_preview' => $this->preview((string) ($row['description'] ?? ''), 140),
            'status' => (string) ($row['status'] ?? ''),
            'page_type' => (string) ($row['page_type'] ?? ''),
            'campaign_id' => isset($row['campaign_id']) ? (int) $row['campaign_id'] : null,
            'campaign_name' => (string) ($row['campaign_name'] ?? ''),
            'template_id' => isset($row['template_id']) ? (int) $row['template_id'] : null,
            'template_name' => (string) ($row['template_name'] ?? ''),
            'owner_user_id' => isset($row['owner_user_id']) ? (int) $row['owner_user_id'] : null,
            'owner_label' => (string) ($row['owner_label'] ?? ''),
            'public_url_present' => trim((string) ($row['public_url'] ?? '')) !== '',
            'public_url_preview' => $this->preview((string) ($row['public_url'] ?? ''), 48),
            'public_url_exposed' => false,
            'seo_title' => (string) ($row['seo_title'] ?? ''),
            'seo_description_preview' => $this->preview((string) ($row['seo_description'] ?? ''), 140),
            'custom_head_html_present' => trim((string) ($row['custom_head_html'] ?? '')) !== '',
            'custom_head_html_exposed' => false,
            'custom_body_html_present' => trim((string) ($row['custom_body_html'] ?? '')) !== '',
            'custom_body_html_exposed' => false,
            'published_at' => $row['published_at'] ?? null,
            'unpublished_at' => $row['unpublished_at'] ?? null,
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
            'mode' => 'read-only',
            'db_write' => false,
            'public_render' => false,
            'visit_tracking_write' => false,
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
