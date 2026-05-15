<?php

declare(strict_types=1);

namespace App\Core\Reports;

final readonly class EcosistemaReportExportService
{
    public function __construct(private EcosistemaReportExportRepository $repository, private bool $writeEnabled = false, private bool $includePii = false) {}

    public function requestExport(int $tenantId, int $userId, array $input): array
    {
        $reportType = (string) ($input['report_type'] ?? 'dashboard_inventory');
        $format = strtolower((string) ($input['format'] ?? 'csv'));
        $sourceId = (int) ($input['source_id'] ?? 0);
        $confirmPii = filter_var($input['confirm_pii'] ?? false, FILTER_VALIDATE_BOOL);

        $result = ['ok'=>false,'allowed'=>false,'db_write'=>false,'tenant_from_session'=>true,'report_type'=>$reportType,'format'=>$format,'source_id'=>$sourceId,'status'=>'blocked','blocked_reason'=>null,'pii_requested'=>$confirmPii,'pii_included'=>false,'export_id'=>null];

        if ($tenantId <= 0 || $userId <= 0) { $result['blocked_reason'] = 'invalid_auth_context'; return $result; }
        if (!in_array($reportType, ['dashboard_inventory'], true)) { $result['blocked_reason'] = 'unsupported_report_type'; return $result; }
        if (!in_array($format, ['csv', 'xlsx'], true)) { $result['blocked_reason'] = 'unsupported_format'; return $result; }
        if ($sourceId <= 0) { $result['blocked_reason'] = 'invalid_source_id'; return $result; }
        if ($confirmPii && !$this->includePii) { $result['blocked_reason'] = 'pii_not_allowed'; return $result; }

        $dashboard = $this->repository->findDashboardById($tenantId, $sourceId);
        if ($dashboard === null) { $result['blocked_reason'] = 'source_not_found'; return $result; }

        $result['allowed'] = true;
        $result['status'] = $this->writeEnabled ? 'queued' : 'dry-blocked';
        $result['pii_included'] = $confirmPii && $this->includePii;
        $result['source_preview'] = [
            'id' => (int) ($dashboard['id'] ?? 0),
            'dashboard_key' => (string) ($dashboard['dashboard_key'] ?? ''),
            'name' => (string) ($dashboard['name'] ?? ''),
            'visibility' => (string) ($dashboard['visibility'] ?? ''),
        ];

        if (!$this->writeEnabled) { $result['blocked_reason'] = 'write_disabled'; return $result; }

        $metadata = ['mode'=>'controlled-export','pii_requested'=>$confirmPii,'pii_included'=>$result['pii_included'],'source_preview'=>$result['source_preview']];
        $exportId = $this->repository->createExportRequest($tenantId, $userId, $reportType, $sourceId, $format, (string) json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $result['ok'] = $exportId > 0;
        $result['db_write'] = $result['ok'];
        $result['export_id'] = $exportId > 0 ? $exportId : null;
        $result['blocked_reason'] = $result['ok'] ? null : 'write_failed';

        return $result;
    }
}
