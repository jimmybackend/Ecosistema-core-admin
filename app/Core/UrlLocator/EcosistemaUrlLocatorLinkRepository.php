<?php

declare(strict_types=1);

namespace App\Core\UrlLocator;

use PDO;

final readonly class EcosistemaUrlLocatorLinkRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listRecentLinks(int $tenantId, int $limit = 100): array
    {
        $safeLimit = max(1, min(200, $limit));
        $stmt = $this->pdo->prepare('SELECT l.id,l.campaign_id,l.landing_page_id,l.created_by_user_id,l.slug,l.target_url,l.original_url_after_ads,l.default_language_code,l.language_detection_enabled,l.title,l.description,l.status,l.smart_type,l.requires_access_token,l.access_token_hash,l.expires_at,l.max_clicks,l.click_count,l.utm_source,l.utm_medium,l.utm_campaign,l.utm_term,l.utm_content,l.created_at,l.updated_at,c.name AS campaign_name,p.title AS landing_page_title,COALESCE(u.display_name,u.email) AS created_by_label FROM url_short_links l LEFT JOIN crm_marketing_campaigns c ON c.id=l.campaign_id LEFT JOIN landing_pages p ON p.id=l.landing_page_id LEFT JOIN core_users u ON u.id=l.created_by_user_id WHERE l.tenant_id=:tenant_id ORDER BY l.updated_at DESC,l.id DESC LIMIT :limit');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function summarizeLinks(int $tenantId): array
    {
        $summary = ['total' => 0, 'by_status' => [], 'by_smart_type' => []];
        $t = $this->pdo->prepare('SELECT COUNT(*) FROM url_short_links WHERE tenant_id=:tenant_id');
        $t->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $t->execute();
        $summary['total'] = (int) $t->fetchColumn();

        $s = $this->pdo->prepare('SELECT status,COUNT(*) AS total FROM url_short_links WHERE tenant_id=:tenant_id GROUP BY status ORDER BY status ASC');
        $s->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $s->execute();
        foreach ($s->fetchAll(PDO::FETCH_ASSOC) ?: [] as $r) {
            $summary['by_status'][] = ['status' => (string) ($r['status'] ?? ''), 'total' => (int) ($r['total'] ?? 0)];
        }

        $m = $this->pdo->prepare('SELECT smart_type,COUNT(*) AS total FROM url_short_links WHERE tenant_id=:tenant_id GROUP BY smart_type ORDER BY smart_type ASC');
        $m->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $m->execute();
        foreach ($m->fetchAll(PDO::FETCH_ASSOC) ?: [] as $r) {
            $summary['by_smart_type'][] = ['smart_type' => (string) ($r['smart_type'] ?? ''), 'total' => (int) ($r['total'] ?? 0)];
        }

        return $summary;
    }

    public function findLink(int $tenantId, int $linkId): ?array
    {
        if ($linkId <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare('SELECT l.id,l.campaign_id,l.landing_page_id,l.created_by_user_id,l.slug,l.target_url,l.original_url_after_ads,l.default_language_code,l.language_detection_enabled,l.language_fallback_url,l.language_query_param,l.title,l.description,l.status,l.smart_type,l.requires_access_token,l.access_token_hash,l.expires_at,l.max_clicks,l.click_count,l.utm_source,l.utm_medium,l.utm_campaign,l.utm_term,l.utm_content,l.created_at,l.updated_at,c.name AS campaign_name,p.title AS landing_page_title,COALESCE(u.display_name,u.email) AS created_by_label FROM url_short_links l LEFT JOIN crm_marketing_campaigns c ON c.id=l.campaign_id LEFT JOIN landing_pages p ON p.id=l.landing_page_id LEFT JOIN core_users u ON u.id=l.created_by_user_id WHERE l.tenant_id=:tenant_id AND l.id=:id LIMIT 1');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $linkId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function listLinkLanguages(int $tenantId, int $linkId): array
    {
        if ($linkId <= 0) {
            return [];
        }

        $stmt = $this->pdo->prepare('SELECT sll.language_code,sll.target_url,sll.priority,sll.is_default_for_language,sll.is_active,sll.click_count FROM url_short_link_languages sll INNER JOIN url_short_links l ON l.id=sll.short_link_id LEFT JOIN url_languages ul ON ul.code=sll.language_code WHERE l.tenant_id=:tenant_id AND sll.short_link_id=:link_id ORDER BY sll.priority ASC,sll.language_code ASC');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':link_id', $linkId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findSmartSettings(int $tenantId, int $linkId): ?array
    {
        if ($linkId <= 0) {
            return null;
        }

        $stmt = $this->pdo->prepare('SELECT s.show_access_counter,s.track_location,s.track_attachments,s.track_final_click,s.allow_indexing,s.require_consent,s.custom_css,s.custom_js FROM url_smart_link_settings s INNER JOIN url_short_links l ON l.id=s.short_link_id WHERE l.tenant_id=:tenant_id AND s.short_link_id=:link_id LIMIT 1');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':link_id', $linkId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function listLinkMessageTemplates(int $tenantId, int $linkId, int $limit = 20): array
    {
        if ($linkId <= 0) {
            return [];
        }

        $safeLimit = max(1, min(100, $limit));
        $stmt = $this->pdo->prepare('SELECT mt.id,mt.template_name,mt.language_code,mt.status,mt.view_count,mt.body_html FROM url_message_templates mt INNER JOIN url_short_links l ON l.id=mt.short_link_id WHERE l.tenant_id=:tenant_id AND mt.short_link_id=:link_id ORDER BY mt.id DESC LIMIT :limit');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':link_id', $linkId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listLinkAdInterstitials(int $tenantId, int $linkId, int $limit = 20): array
    {
        if ($linkId <= 0) {
            return [];
        }

        $safeLimit = max(1, min(100, $limit));
        $stmt = $this->pdo->prepare('SELECT ai.id,ai.title,ai.ad_type,ai.status,ai.impression_count,ai.click_count,ai.media_s3_key,ai.ad_html FROM url_ad_interstitials ai INNER JOIN url_short_links l ON l.id=ai.short_link_id WHERE l.tenant_id=:tenant_id AND ai.short_link_id=:link_id ORDER BY ai.id DESC LIMIT :limit');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':link_id', $linkId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
