<?php

declare(strict_types=1);

namespace App\Core\UrlLocator;

use PDO;

final readonly class EcosistemaUrlLocatorClickRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function listRecentClicks(int $tenantId, int $limit = 100): array
    {
        $safeLimit = $this->safeLimit($limit);
        $stmt = $this->pdo->prepare('SELECT c.id,c.short_link_id,c.campaign_id,c.landing_page_id,c.visitor_uuid,c.ip_address,c.user_agent,c.accept_language_header,c.detected_language,c.selected_language,c.referer,c.clicked_url,c.country,c.region,c.city,c.latitude,c.longitude,c.device_type,c.browser_name,c.os_name,c.clicked_at,l.slug AS short_link_slug,l.title AS short_link_title FROM url_clicks c INNER JOIN url_short_links l ON l.id=c.short_link_id AND l.tenant_id=c.tenant_id WHERE c.tenant_id=:tenant_id ORDER BY c.clicked_at DESC,c.id DESC LIMIT :limit');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function listClicksForLink(int $tenantId, int $linkId, int $limit = 100): array
    {
        if ($linkId <= 0) {
            return [];
        }

        $safeLimit = $this->safeLimit($limit);
        $stmt = $this->pdo->prepare('SELECT c.id,c.short_link_id,c.campaign_id,c.landing_page_id,c.visitor_uuid,c.ip_address,c.user_agent,c.accept_language_header,c.detected_language,c.selected_language,c.referer,c.clicked_url,c.country,c.region,c.city,c.latitude,c.longitude,c.device_type,c.browser_name,c.os_name,c.clicked_at,l.slug AS short_link_slug,l.title AS short_link_title FROM url_clicks c INNER JOIN url_short_links l ON l.id=c.short_link_id AND l.tenant_id=c.tenant_id WHERE c.tenant_id=:tenant_id AND c.short_link_id=:link_id ORDER BY c.clicked_at DESC,c.id DESC LIMIT :limit');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':link_id', $linkId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function summarizeClicks(int $tenantId): array
    {
        return [
            'total' => $this->countByQuery('SELECT COUNT(*) FROM url_clicks WHERE tenant_id=:tenant_id', $tenantId),
            'by_device_type' => $this->bucket('SELECT COALESCE(NULLIF(device_type,\'\'),\'unknown\') AS bucket,COUNT(*) AS total FROM url_clicks WHERE tenant_id=:tenant_id GROUP BY bucket ORDER BY total DESC,bucket ASC', $tenantId),
            'by_detected_language' => $this->bucket('SELECT COALESCE(NULLIF(detected_language,\'\'),\'unknown\') AS bucket,COUNT(*) AS total FROM url_clicks WHERE tenant_id=:tenant_id GROUP BY bucket ORDER BY total DESC,bucket ASC', $tenantId),
            'by_country' => $this->bucket('SELECT COALESCE(NULLIF(country,\'\'),\'unknown\') AS bucket,COUNT(*) AS total FROM url_clicks WHERE tenant_id=:tenant_id GROUP BY bucket ORDER BY total DESC,bucket ASC', $tenantId),
        ];
    }

    public function summarizeClicksForLink(int $tenantId, int $linkId): array
    {
        if ($linkId <= 0) {
            return ['total' => 0, 'by_device_type' => [], 'by_detected_language' => [], 'by_country' => []];
        }

        return [
            'total' => $this->countByQuery('SELECT COUNT(*) FROM url_clicks WHERE tenant_id=:tenant_id AND short_link_id=:link_id', $tenantId, $linkId),
            'by_device_type' => $this->bucket('SELECT COALESCE(NULLIF(device_type,\'\'),\'unknown\') AS bucket,COUNT(*) AS total FROM url_clicks WHERE tenant_id=:tenant_id AND short_link_id=:link_id GROUP BY bucket ORDER BY total DESC,bucket ASC', $tenantId, $linkId),
            'by_detected_language' => $this->bucket('SELECT COALESCE(NULLIF(detected_language,\'\'),\'unknown\') AS bucket,COUNT(*) AS total FROM url_clicks WHERE tenant_id=:tenant_id AND short_link_id=:link_id GROUP BY bucket ORDER BY total DESC,bucket ASC', $tenantId, $linkId),
            'by_country' => $this->bucket('SELECT COALESCE(NULLIF(country,\'\'),\'unknown\') AS bucket,COUNT(*) AS total FROM url_clicks WHERE tenant_id=:tenant_id AND short_link_id=:link_id GROUP BY bucket ORDER BY total DESC,bucket ASC', $tenantId, $linkId),
        ];
    }

    private function safeLimit(int $limit): int
    {
        return max(1, min(200, $limit));
    }

    private function countByQuery(string $sql, int $tenantId, ?int $linkId = null): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        if ($linkId !== null) {
            $stmt->bindValue(':link_id', $linkId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    private function bucket(string $sql, int $tenantId, ?int $linkId = null): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        if ($linkId !== null) {
            $stmt->bindValue(':link_id', $linkId, PDO::PARAM_INT);
        }
        $stmt->execute();

        $rows = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $rows[] = ['value' => (string) ($row['bucket'] ?? 'unknown'), 'total' => (int) ($row['total'] ?? 0)];
        }

        return $rows;
    }
}
