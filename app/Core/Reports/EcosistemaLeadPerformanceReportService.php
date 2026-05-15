<?php

declare(strict_types=1);

namespace App\Core\Reports;

final class EcosistemaLeadPerformanceReportService
{
    public function __construct(private readonly EcosistemaLeadPerformanceReportRepository $repository)
    {
    }

    public function build(int $tenantId, array $query): array
    {
        $today = (new \DateTimeImmutable('today'))->format('Y-m-d');
        $from = (string) ($query['from'] ?? $today);
        $to = (string) ($query['to'] ?? $today);
        if (!$this->validDate($from)) { $from = $today; }
        if (!$this->validDate($to)) { $to = $today; }
        if ($from > $to) { [$from, $to] = [$to, $from]; }

        $result = [
            'filters' => ['from' => $from, 'to' => $to],
            'by_source' => [],
            'by_campaign' => [],
            'by_status' => [],
            'score_temperature' => ['leads' => 0, 'avg_score' => 0.0, 'hot_leads' => 0, 'warm_leads' => 0, 'cold_leads' => 0],
            'mode' => 'read-only',
        ];

        if ($tenantId <= 0) {
            return $result;
        }

        $result['by_source'] = array_map(fn (array $row): array => [
            'source_module' => (string) ($row['source_module'] ?? 'unknown'),
            'leads' => (int) ($row['leads'] ?? 0),
            'conversions' => (int) ($row['conversions'] ?? 0),
        ], $this->repository->bySource($tenantId, $from, $to));

        $result['by_campaign'] = array_map(fn (array $row): array => [
            'campaign_id' => (int) ($row['campaign_id'] ?? 0),
            'leads' => (int) ($row['leads'] ?? 0),
            'conversions' => (int) ($row['conversions'] ?? 0),
        ], $this->repository->byCampaign($tenantId, $from, $to));

        $result['by_status'] = array_map(fn (array $row): array => [
            'status' => (string) ($row['status'] ?? 'unknown'),
            'leads' => (int) ($row['leads'] ?? 0),
            'conversions' => (int) ($row['conversions'] ?? 0),
        ], $this->repository->byStatus($tenantId, $from, $to));

        $summary = $this->repository->scoreTemperatureSummary($tenantId, $from, $to);
        $result['score_temperature'] = [
            'leads' => max(0, (int) ($summary['leads'] ?? 0)),
            'avg_score' => round((float) ($summary['avg_score'] ?? 0), 2),
            'hot_leads' => max(0, (int) ($summary['hot_leads'] ?? 0)),
            'warm_leads' => max(0, (int) ($summary['warm_leads'] ?? 0)),
            'cold_leads' => max(0, (int) ($summary['cold_leads'] ?? 0)),
        ];

        return $result;
    }

    private function validDate(string $date): bool
    {
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $date);
        return $dt !== false && $dt->format('Y-m-d') === $date;
    }
}
