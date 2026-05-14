<?php

declare(strict_types=1);

namespace App\Core\Attribution;

use PDO;

final readonly class EcosistemaAttributionRollupRepository
{
    public function __construct(private PDO $pdo) {}

    public function aggregateForDate(int $tenantId, string $rollupDate): array
    {
        $params = [':tenant_id' => $tenantId, ':start_at' => $rollupDate . ' 00:00:00', ':end_at' => $rollupDate . ' 23:59:59'];

        return [
            'sessions_count' => $this->count('SELECT COUNT(*) FROM browser_analytics_sessions WHERE tenant_id=:tenant_id AND started_at BETWEEN :start_at AND :end_at', $params),
            'users_count' => $this->count('SELECT COUNT(DISTINCT visitor_uuid) FROM browser_analytics_sessions WHERE tenant_id=:tenant_id AND started_at BETWEEN :start_at AND :end_at', $params),
            'pageviews_count' => $this->count('SELECT COUNT(*) FROM landing_visits WHERE tenant_id=:tenant_id AND visited_at BETWEEN :start_at AND :end_at', $params),
            'campaign_clicks_count' => $this->count('SELECT COUNT(*) FROM url_clicks WHERE tenant_id=:tenant_id AND clicked_at BETWEEN :start_at AND :end_at', $params),
            'form_submits_count' => $this->count('SELECT COUNT(*) FROM landing_form_submissions WHERE tenant_id=:tenant_id AND submitted_at BETWEEN :start_at AND :end_at', $params),
            'conversions_count' => $this->count('SELECT COUNT(*) FROM browser_analytics_attribution WHERE tenant_id=:tenant_id AND conversion_at BETWEEN :start_at AND :end_at', $params),
        ];
    }

    public function countExistingRollupsForDate(int $tenantId, string $rollupDate): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM browser_analytics_daily_rollups WHERE tenant_id=:tenant_id AND rollup_date=:rollup_date');
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':rollup_date', $rollupDate, PDO::PARAM_STR);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    private function count(string $sql, array $params): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $params[':tenant_id'], PDO::PARAM_INT);
        $stmt->bindValue(':start_at', $params[':start_at'], PDO::PARAM_STR);
        $stmt->bindValue(':end_at', $params[':end_at'], PDO::PARAM_STR);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}
