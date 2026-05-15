<?php
declare(strict_types=1);

namespace App\Core\Security;

final class EcosistemaRateLimitDryRunService
{
    public function __construct(private EcosistemaRateLimitDryRunRepository $repository)
    {
    }

    public function simulate(int $tenantId, array $payload, bool $enabled, bool $dryRunEnabled): array
    {
        $warnings = [];

        if (isset($payload['tenant_id'])) {
            $warnings[] = 'tenant_id_ignored_from_request';
        }

        $path = $this->sanitizePath((string) ($payload['path'] ?? ''));
        $ipAddress = $this->sanitizeIp((string) ($payload['ip_address'] ?? ''));
        $windowMinutes = $this->sanitizeInt($payload['window_minutes'] ?? null, 1, 120) ?? 15;
        $maxRequests = $this->sanitizeInt($payload['max_requests'] ?? null, 1, 2000) ?? 120;
        $maxLoginFailures = $this->sanitizeInt($payload['max_login_failures'] ?? null, 1, 2000) ?? 20;

        if ($path === null) { $warnings[] = 'path_invalid'; }
        if ($ipAddress === null) { $warnings[] = 'ip_address_invalid'; }

        $apiCount = 0;
        $loginFailures = 0;
        if ($path !== null && $ipAddress !== null) {
            $apiCount = $this->repository->countRecentApiRequests($tenantId, $path, $ipAddress, $windowMinutes);
            $loginFailures = $this->repository->countRecentFailedLoginsByIp($tenantId, $ipAddress, $windowMinutes);
        }

        $wouldBlockByApi = $apiCount >= $maxRequests;
        $wouldBlockByLogin = $loginFailures >= $maxLoginFailures;

        return [
            'mode' => 'dry-run',
            'enabled' => $enabled,
            'dry_run_enabled' => $dryRunEnabled,
            'tenant_from_session' => true,
            'db_write' => false,
            'request_blocked' => false,
            'would_block' => $wouldBlockByApi || $wouldBlockByLogin,
            'would_block_reason' => $wouldBlockByApi ? 'api_requests_threshold' : ($wouldBlockByLogin ? 'login_failures_threshold' : null),
            'simulated_input' => [
                'path_preview' => $this->maskPath($path),
                'ip_preview' => $this->maskIp($ipAddress),
                'window_minutes' => $windowMinutes,
                'max_requests' => $maxRequests,
                'max_login_failures' => $maxLoginFailures,
            ],
            'metrics' => [
                'api_requests_in_window' => $apiCount,
                'failed_logins_in_window' => $loginFailures,
            ],
            'warnings' => $warnings,
        ];
    }

    private function sanitizeInt(mixed $value, int $min, int $max): ?int
    {
        if ($value === null || $value === '') { return null; }
        $validated = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);
        return $validated === false ? null : (int) $validated;
    }

    private function sanitizePath(string $path): ?string
    {
        $path = trim($path);
        if ($path === '' || !str_starts_with($path, '/')) { return null; }
        if (mb_strlen($path) > 255) { return null; }
        return preg_match('/^\/[A-Za-z0-9\-._~\/]*$/', $path) === 1 ? $path : null;
    }

    private function sanitizeIp(string $ip): ?string
    {
        $ip = trim($ip);
        if ($ip === '') { return null; }
        return filter_var($ip, FILTER_VALIDATE_IP) === false ? null : $ip;
    }

    private function maskIp(?string $ip): string
    {
        if (!is_string($ip) || $ip === '') { return 'invalid'; }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            return $parts[0] . '.' . $parts[1] . '.x.x';
        }
        $chunks = explode(':', $ip);
        return (string) ($chunks[0] ?? 'x') . ':' . (string) ($chunks[1] ?? 'x') . ':xxxx:xxxx';
    }

    private function maskPath(?string $path): string
    {
        if (!is_string($path) || $path === '') { return 'invalid'; }
        $qPos = strpos($path, '?');
        $clean = $qPos === false ? $path : substr($path, 0, $qPos);
        return mb_substr($clean, 0, 80);
    }
}
