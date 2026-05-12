<?php

declare(strict_types=1);

namespace App\Core\System;

final readonly class CronHealthCheckRunner
{
    public function __construct(private HealthService $healthService)
    {
    }

    /**
     * @return array{job:string,checks_found:int,checks_executed:int,success:int,failed:int,skipped:int,messages:list<string>}
     */
    public function run(): array
    {
        $definitions = $this->healthService->listHealthChecks();

        $summary = [
            'job' => 'health-checks',
            'checks_found' => count($definitions),
            'checks_executed' => 0,
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'messages' => [],
        ];

        foreach ($definitions as $definition) {
            $id = isset($definition['id']) ? (int) $definition['id'] : 0;
            $code = trim((string) ($definition['code'] ?? 'sin-codigo'));
            $type = strtolower(trim((string) ($definition['check_type'] ?? '')));

            if ($id <= 0) {
                $summary['skipped']++;
                $summary['messages'][] = sprintf('SKIPPED %s: definición inválida.', $code);
                continue;
            }

            if (!in_array($type, ['db', 'database'], true)) {
                $summary['skipped']++;
                $summary['messages'][] = sprintf('SKIPPED %s: check_type no permitido para cron seguro (%s).', $code, $type !== '' ? $type : 'empty');
                continue;
            }

            $summary['checks_executed']++;
            $message = $this->healthService->runHealthCheck($id, null, null, null, 'cron-runner');
            $normalized = strtolower($message);

            if (str_contains($normalized, 'correctamente')) {
                $summary['success']++;
                $summary['messages'][] = sprintf('OK %s: %s', $code, $message);
                continue;
            }

            $summary['failed']++;
            $summary['messages'][] = sprintf('FAIL %s: %s', $code, $message);
        }

        return $summary;
    }
}
