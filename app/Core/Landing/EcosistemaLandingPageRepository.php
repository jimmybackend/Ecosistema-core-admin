<?php

declare(strict_types=1);

namespace App\Core\Landing;

use PDO;

final readonly class EcosistemaLandingPageRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listRecentPages(int $tenantId, int $limit = 100): array
    {
        $safeLimit = $this->safeLimit($limit);
        $sql = 'SELECT lp.id,lp.campaign_id,lp.template_id,lp.owner_user_id,lp.title,lp.slug,lp.description,lp.status,lp.page_type,lp.public_url,lp.seo_title,lp.seo_description,lp.custom_head_html,lp.custom_body_html,lp.published_at,lp.unpublished_at,lp.created_at,lp.updated_at,c.name AS campaign_name,t.name AS template_name,COALESCE(u.display_name,u.email) AS owner_label FROM landing_pages lp LEFT JOIN crm_marketing_campaigns c ON c.id=lp.campaign_id LEFT JOIN landing_templates t ON t.id=lp.template_id LEFT JOIN core_users u ON u.id=lp.owner_user_id WHERE lp.tenant_id=:tenant_id ORDER BY lp.updated_at DESC,lp.id DESC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findPage(int $tenantId, int $pageId): ?array
    {
        if ($pageId <= 0) {
            return null;
        }

        $sql = 'SELECT lp.id,lp.campaign_id,lp.template_id,lp.owner_user_id,lp.title,lp.slug,lp.description,lp.status,lp.page_type,lp.public_url,lp.seo_title,lp.seo_description,lp.custom_head_html,lp.custom_body_html,lp.published_at,lp.unpublished_at,lp.created_at,lp.updated_at,c.name AS campaign_name,t.name AS template_name,t.template_json,COALESCE(u.display_name,u.email) AS owner_label FROM landing_pages lp LEFT JOIN crm_marketing_campaigns c ON c.id=lp.campaign_id LEFT JOIN landing_templates t ON t.id=lp.template_id LEFT JOIN core_users u ON u.id=lp.owner_user_id WHERE lp.tenant_id=:tenant_id AND lp.id=:id LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $pageId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }


    public function findPublishedPageBySlug(int $tenantId, string $slug): ?array
    {
        $safeSlug = trim($slug);
        if ($tenantId <= 0 || $safeSlug === '') {
            return null;
        }

        $sql = "SELECT lp.id,lp.tenant_id,lp.campaign_id,lp.template_id,lp.title,lp.slug,lp.description,lp.status,lp.page_type,lp.public_url,lp.seo_title,lp.seo_description,lp.published_at,lp.unpublished_at,lp.created_at,lp.updated_at FROM landing_pages lp WHERE lp.tenant_id=:tenant_id AND lp.slug=:slug AND LOWER(COALESCE(lp.status,''))='published' LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':slug', $safeSlug, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function summarizePages(int $tenantId): array
    {
        $summary = ['total' => 0, 'by_status' => [], 'by_page_type' => []];

        $total = $this->pdo->prepare('SELECT COUNT(*) FROM landing_pages WHERE tenant_id=:tenant_id');
        $total->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $total->execute();
        $summary['total'] = (int) $total->fetchColumn();

        $status = $this->pdo->prepare('SELECT status,COUNT(*) AS total FROM landing_pages WHERE tenant_id=:tenant_id GROUP BY status ORDER BY status ASC');
        $status->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $status->execute();
        foreach ($status->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $summary['by_status'][] = ['status' => (string) ($row['status'] ?? ''), 'total' => (int) ($row['total'] ?? 0)];
        }

        $type = $this->pdo->prepare('SELECT page_type,COUNT(*) AS total FROM landing_pages WHERE tenant_id=:tenant_id GROUP BY page_type ORDER BY page_type ASC');
        $type->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $type->execute();
        foreach ($type->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $summary['by_page_type'][] = ['page_type' => (string) ($row['page_type'] ?? ''), 'total' => (int) ($row['total'] ?? 0)];
        }

        return $summary;
    }

    public function listPageVersions(int $tenantId, int $pageId, int $limit = 20): array
    {
        if ($pageId <= 0) {
            return [];
        }

        $safeLimit = $this->safeLimit($limit);
        $sql = 'SELECT id,version_no,title,is_published,created_at,layout_json,custom_css,custom_js FROM landing_page_versions WHERE tenant_id=:tenant_id AND landing_page_id=:page_id ORDER BY version_no DESC,id DESC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':page_id', $pageId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listPageBlocks(int $tenantId, int $pageId, int $limit = 100): array
    {
        if ($pageId <= 0) {
            return [];
        }

        $safeLimit = $this->safeLimit($limit);
        $sql = 'SELECT id,version_id,parent_block_id,block_type,name,sort_order,is_active,created_at,updated_at,settings_json,content_json FROM landing_page_blocks WHERE tenant_id=:tenant_id AND landing_page_id=:page_id ORDER BY sort_order ASC,id ASC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':page_id', $pageId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


    public function findPublishedVersion(int $tenantId, int $pageId): ?array
    {
        if ($pageId <= 0) {
            return null;
        }

        $sql = 'SELECT id,landing_page_id,version_no,title,layout_json,custom_css,custom_js,is_published,created_at FROM landing_page_versions WHERE tenant_id=:tenant_id AND landing_page_id=:page_id AND is_published=1 ORDER BY version_no DESC,id DESC LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':page_id', $pageId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function listBlocksByVersion(int $tenantId, int $pageId, int $versionId, int $limit = 100): array
    {
        if ($pageId <= 0 || $versionId <= 0) {
            return [];
        }

        $safeLimit = $this->safeLimit($limit);
        $sql = 'SELECT id,version_id,parent_block_id,block_type,name,sort_order,is_active,settings_json,content_json,created_at,updated_at FROM landing_page_blocks WHERE tenant_id=:tenant_id AND landing_page_id=:page_id AND version_id=:version_id ORDER BY sort_order ASC,id ASC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':page_id', $pageId, PDO::PARAM_INT);
        $stmt->bindValue(':version_id', $versionId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
    private function safeLimit(int $limit): int
    {
        return max(1, min(200, $limit));
    }
}
