<?php

declare(strict_types=1);

namespace App\Core\Attribution;

use PDO;

final readonly class EcosistemaUrlLandingAttributionRepository
{
    public function __construct(private PDO $pdo) {}

    public function findClickById(int $tenantId, int $clickId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id,tenant_id,short_link_id,landing_page_id,campaign_id,visitor_uuid,clicked_at,ip_address,user_agent,referer,clicked_url FROM url_clicks WHERE tenant_id=:tenant_id AND id=:id LIMIT 1');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $clickId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    public function findVisitCandidates(int $tenantId, array $click, int $limit = 20): array
    {
        $safeLimit = max(1, min(100, $limit));
        $stmt = $this->pdo->prepare('SELECT id,tenant_id,landing_page_id,campaign_id,short_link_id,visitor_uuid,session_uuid,visited_at,ip_address,user_agent,referer,full_url FROM landing_visits WHERE tenant_id=:tenant_id AND short_link_id=:short_link_id AND landing_page_id=:landing_page_id AND campaign_id=:campaign_id AND visitor_uuid=:visitor_uuid ORDER BY visited_at DESC,id DESC LIMIT :limit');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':short_link_id', (int) ($click['short_link_id'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':landing_page_id', (int) ($click['landing_page_id'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':campaign_id', (int) ($click['campaign_id'] ?? 0), PDO::PARAM_INT);
        $stmt->bindValue(':visitor_uuid', (string) ($click['visitor_uuid'] ?? ''), PDO::PARAM_STR);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findSessionCandidates(int $tenantId, string $visitorUuid, int $limit = 20): array
    {
        $safeLimit = max(1, min(100, $limit));
        $stmt = $this->pdo->prepare('SELECT id,tenant_id,browser_session_uuid,visitor_uuid,started_at,last_activity_at,entry_url,referrer_url,ip_address,user_agent FROM browser_analytics_sessions WHERE tenant_id=:tenant_id AND visitor_uuid=:visitor_uuid ORDER BY last_activity_at DESC,id DESC LIMIT :limit');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':visitor_uuid', $visitorUuid, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $safeLimit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
