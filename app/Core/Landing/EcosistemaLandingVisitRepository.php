<?php

declare(strict_types=1);

namespace App\Core\Landing;

use PDO;

final readonly class EcosistemaLandingVisitRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listRecentVisits(int $tenantId, int $limit = 100): array
    {
        $safeLimit = $this->safeLimit($limit);
        $sql = 'SELECT lv.id,lv.landing_page_id,lv.campaign_id,lv.short_link_id,lv.visitor_uuid,lv.session_uuid,lv.ip_address,lv.user_agent,lv.referer,lv.full_url,lv.utm_source,lv.utm_medium,lv.utm_campaign,lv.utm_term,lv.utm_content,lv.country,lv.region,lv.city,lv.latitude,lv.longitude,lv.device_type,lv.browser_name,lv.os_name,lv.visited_at,lp.title AS landing_page_title,lp.slug AS landing_page_slug,c.name AS campaign_name,usl.slug AS short_link_slug FROM landing_visits lv LEFT JOIN landing_pages lp ON lp.id=lv.landing_page_id AND lp.tenant_id=lv.tenant_id LEFT JOIN crm_marketing_campaigns c ON c.id=lv.campaign_id LEFT JOIN url_short_links usl ON usl.id=lv.short_link_id WHERE lv.tenant_id=:tenant_id ORDER BY lv.visited_at DESC,lv.id DESC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listVisitsForPage(int $tenantId, int $pageId, int $limit = 100): array
    {
        if ($pageId <= 0) {
            return [];
        }

        $safeLimit = $this->safeLimit($limit);
        $sql = 'SELECT lv.id,lv.landing_page_id,lv.campaign_id,lv.short_link_id,lv.visitor_uuid,lv.session_uuid,lv.ip_address,lv.user_agent,lv.referer,lv.full_url,lv.utm_source,lv.utm_medium,lv.utm_campaign,lv.utm_term,lv.utm_content,lv.country,lv.region,lv.city,lv.latitude,lv.longitude,lv.device_type,lv.browser_name,lv.os_name,lv.visited_at,lp.title AS landing_page_title,lp.slug AS landing_page_slug,c.name AS campaign_name,usl.slug AS short_link_slug FROM landing_visits lv LEFT JOIN landing_pages lp ON lp.id=lv.landing_page_id AND lp.tenant_id=lv.tenant_id LEFT JOIN crm_marketing_campaigns c ON c.id=lv.campaign_id LEFT JOIN url_short_links usl ON usl.id=lv.short_link_id WHERE lv.tenant_id=:tenant_id AND lv.landing_page_id=:page_id ORDER BY lv.visited_at DESC,lv.id DESC LIMIT :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':page_id', $pageId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function summarizeVisits(int $tenantId): array
    {
        return $this->doSummaries($tenantId, null);
    }

    public function summarizeVisitsForPage(int $tenantId, int $pageId): array
    {
        if ($pageId <= 0) {
            return ['total' => 0, 'by_country' => [], 'by_device_type' => [], 'by_campaign' => []];
        }

        return $this->doSummaries($tenantId, $pageId);
    }

    private function doSummaries(int $tenantId, ?int $pageId): array
    {
        $summary = ['total' => 0, 'by_country' => [], 'by_device_type' => [], 'by_campaign' => []];
        $where = 'tenant_id=:tenant_id' . ($pageId !== null ? ' AND landing_page_id=:page_id' : '');

        $total = $this->pdo->prepare('SELECT COUNT(*) FROM landing_visits WHERE ' . $where);
        $total->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        if ($pageId !== null) {
            $total->bindValue(':page_id', $pageId, PDO::PARAM_INT);
        }
        $total->execute();
        $summary['total'] = (int) $total->fetchColumn();

        foreach ([
            'by_country' => 'SELECT COALESCE(country,\'\') AS bucket,COUNT(*) AS total FROM landing_visits WHERE ' . $where . ' GROUP BY country ORDER BY total DESC,bucket ASC LIMIT 20',
            'by_device_type' => 'SELECT COALESCE(device_type,\'\') AS bucket,COUNT(*) AS total FROM landing_visits WHERE ' . $where . ' GROUP BY device_type ORDER BY total DESC,bucket ASC LIMIT 20',
            'by_campaign' => 'SELECT COALESCE(c.name,\'\') AS bucket,COUNT(*) AS total FROM landing_visits lv LEFT JOIN crm_marketing_campaigns c ON c.id=lv.campaign_id WHERE lv.' . $where . ' GROUP BY c.name ORDER BY total DESC,bucket ASC LIMIT 20',
        ] as $key => $sql) {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
            if ($pageId !== null) {
                $stmt->bindValue(':page_id', $pageId, PDO::PARAM_INT);
            }
            $stmt->execute();
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
                $summary[$key][] = ['label' => (string) ($row['bucket'] ?? ''), 'total' => (int) ($row['total'] ?? 0)];
            }
        }

        return $summary;
    }

    private function safeLimit(int $limit): int
    {
        return max(1, min(200, $limit));
    }
}
