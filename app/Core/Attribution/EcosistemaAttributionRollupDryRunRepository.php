<?php

declare(strict_types=1);

namespace App\Core\Attribution;

use PDO;

final readonly class EcosistemaAttributionRollupDryRunRepository
{
    public function __construct(private PDO $pdo) {}

    public function aggregate(int $tenantId, string $startDate, string $endDate): array
    {
        $params = [':tenant_id' => $tenantId, ':start_date' => $startDate . ' 00:00:00', ':end_date' => $endDate . ' 23:59:59'];

        $clicks = $this->count('SELECT COUNT(*) FROM url_clicks WHERE tenant_id=:tenant_id AND clicked_at BETWEEN :start_date AND :end_date', $params);
        $visits = $this->count('SELECT COUNT(*) FROM landing_visits WHERE tenant_id=:tenant_id AND visited_at BETWEEN :start_date AND :end_date', $params);
        $sessions = $this->count('SELECT COUNT(*) FROM browser_analytics_sessions WHERE tenant_id=:tenant_id AND started_at BETWEEN :start_date AND :end_date', $params);
        $submissions = $this->count('SELECT COUNT(*) FROM landing_form_submissions WHERE tenant_id=:tenant_id AND submitted_at BETWEEN :start_date AND :end_date', $params);
        $attributions = $this->count('SELECT COUNT(*) FROM browser_analytics_attribution WHERE tenant_id=:tenant_id AND conversion_at BETWEEN :start_date AND :end_date', $params);

        $byCampaign = $this->rows(
            'SELECT COALESCE(c.name, CONCAT("Campaign #", a.campaign_id)) AS campaign_label, a.campaign_id, COUNT(*) AS attributions_count
             FROM browser_analytics_attribution a
             LEFT JOIN crm_marketing_campaigns c ON c.id=a.campaign_id AND c.tenant_id=a.tenant_id
             WHERE a.tenant_id=:tenant_id AND a.conversion_at BETWEEN :start_date AND :end_date
             GROUP BY a.campaign_id, c.name
             ORDER BY attributions_count DESC, campaign_label ASC
             LIMIT 25',
            $params
        );

        return ['clicks' => $clicks, 'visits' => $visits, 'sessions' => $sessions, 'submissions' => $submissions, 'attributions' => $attributions, 'by_campaign' => $byCampaign];
    }

    private function count(string $sql, array $params): int
    {
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $name => $value) {
            $stmt->bindValue($name, $value, $name === ':tenant_id' ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    private function rows(string $sql, array $params): array
    {
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $name => $value) {
            $stmt->bindValue($name, $value, $name === ':tenant_id' ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
