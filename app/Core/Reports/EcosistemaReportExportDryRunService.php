<?php

declare(strict_types=1);

namespace App\Core\Reports;

final readonly class EcosistemaReportExportDryRunService
{
    public function __construct(private EcosistemaReportExportDryRunRepository $repository, private bool $enabled = false) {}

    public function simulate(int $tenantId, array $input): array
    {
        $reportType = (string) ($input['report_type'] ?? 'marketing_funnel');
        $format = strtolower((string) ($input['format'] ?? 'csv'));
        $limit = max(1, min(50, (int) ($input['limit'] ?? 10)));

        $result = [
            'mode' => 'dry-run',
            'enabled' => $this->enabled,
            'allowed' => false,
            'db_write' => false,
            'tenant_from_session' => true,
            'report_type' => $reportType,
            'format' => $format,
            'limit' => $limit,
            'blocked_reason' => null,
            'allowed_columns' => [],
            'rows_preview' => [],
            'rows_total_previewed' => 0,
            'pii_guard' => true,
        ];

        $definitions = [
            'marketing_funnel' => ['id', 'campaign_id', 'landing_page_id', 'session_id', 'submitted_at'],
            'lead_performance' => ['id', 'source_module', 'lead_temperature', 'score', 'created_at', 'converted_at'],
            'dashboard_inventory' => ['id', 'dashboard_key', 'name', 'visibility', 'is_active', 'created_at'],
        ];

        if (!$this->enabled) { $result['blocked_reason'] = 'feature_disabled'; return $result; }
        if ($tenantId <= 0) { $result['blocked_reason'] = 'invalid_tenant'; return $result; }
        if (!isset($definitions[$reportType])) { $result['blocked_reason'] = 'unsupported_report_type'; return $result; }
        if (!in_array($format, ['csv', 'xlsx'], true)) { $result['blocked_reason'] = 'unsupported_format'; return $result; }

        $rows = $this->repository->listRows($tenantId, $reportType, $limit);
        $allowedColumns = $definitions[$reportType];

        $result['allowed'] = true;
        $result['allowed_columns'] = $allowedColumns;
        $result['rows_preview'] = array_map(fn(array $row): array => $this->sanitizeRow($row, $allowedColumns), $rows);
        $result['rows_total_previewed'] = count($result['rows_preview']);

        return $result;
    }

    private function sanitizeRow(array $row, array $allowedColumns): array
    {
        $safe = [];
        foreach ($allowedColumns as $column) {
            $value = $row[$column] ?? null;
            if (in_array($column, ['session_id'], true)) {
                $safe[$column] = is_string($value) && $value !== '' ? substr($value, 0, 6) . '…' : null;
                continue;
            }
            $safe[$column] = is_scalar($value) || $value === null ? $value : null;
        }

        return $safe;
    }
}
