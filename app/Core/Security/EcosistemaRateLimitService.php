<?php
declare(strict_types=1);

namespace App\Core\Security;

final class EcosistemaRateLimitService
{
    public function __construct(private EcosistemaRateLimitRepository $repository)
    {
    }

    public function enforce(int $tenantId, int $userId, array $payload, bool $enabled, bool $writeBlocksEnabled): array
    {
        $warnings = [];
        if (isset($payload['tenant_id'])) { $warnings[] = 'tenant_id_ignored_from_request'; }

        $path = $this->sanitizePath((string) ($payload['path'] ?? ''));
        $ipAddress = $this->sanitizeIp((string) ($payload['ip_address'] ?? ''));
        $windowMinutes = $this->sanitizeInt($payload['window_minutes'] ?? null, 1, 120) ?? 15;
        $maxRequests = $this->sanitizeInt($payload['max_requests'] ?? null, 1, 2000) ?? 120;
        $maxLoginFailures = $this->sanitizeInt($payload['max_login_failures'] ?? null, 1, 2000) ?? 20;
        $blockMinutes = $this->sanitizeInt($payload['block_minutes'] ?? null, 5, 10080) ?? 60;

        if ($path === null) { $warnings[] = 'path_invalid'; }
        if ($ipAddress === null) { $warnings[] = 'ip_address_invalid'; }

        $apiCount = 0; $loginFailures = 0;
        if ($path !== null && $ipAddress !== null) {
            $apiCount = $this->repository->countRecentApiRequests($tenantId, $path, $ipAddress, $windowMinutes);
            $loginFailures = $this->repository->countRecentFailedLoginsByIp($tenantId, $ipAddress, $windowMinutes);
        }

        $wouldBlockByApi = $apiCount >= $maxRequests;
        $wouldBlockByLogin = $loginFailures >= $maxLoginFailures;
        $wouldBlock = $wouldBlockByApi || $wouldBlockByLogin;
        $reasonCode = $wouldBlockByApi ? 'api_requests_threshold' : ($wouldBlockByLogin ? 'login_failures_threshold' : null);

        $didWrite = false;
        if ($enabled && $writeBlocksEnabled && $wouldBlock && $path !== null && $ipAddress !== null && $tenantId > 0 && $userId > 0) {
            $reason = 'Rate limit (' . $reasonCode . ') window=' . $windowMinutes . 'm';
            $didWrite = $this->repository->insertBlockedIp($tenantId, $ipAddress, $reason, $userId, $blockMinutes);
            $this->repository->insertIncident(
                $tenantId,
                $userId,
                'Rate limit threshold reached',
                'Automated controlled enforcement for threshold=' . $reasonCode,
                'medium',
                'open',
                'security',
                'system_api_requests',
                null
            );
        }

        return [
            'mode' => 'controlled-enforcement',
            'enabled' => $enabled,
            'write_blocks_enabled' => $writeBlocksEnabled,
            'tenant_from_session' => true,
            'db_write' => $didWrite,
            'request_blocked' => $enabled && $wouldBlock,
            'would_block' => $wouldBlock,
            'would_block_reason' => $reasonCode,
            'simulated_input' => [
                'path_preview' => $this->maskPath($path),
                'ip_preview' => $this->maskIp($ipAddress),
                'window_minutes' => $windowMinutes,
                'max_requests' => $maxRequests,
                'max_login_failures' => $maxLoginFailures,
                'block_minutes' => $blockMinutes,
            ],
            'metrics' => ['api_requests_in_window' => $apiCount, 'failed_logins_in_window' => $loginFailures],
            'warnings' => $warnings,
        ];
    }

    private function sanitizeInt(mixed $value, int $min, int $max): ?int { if ($value === null || $value === '') { return null; } $validated = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]); return $validated === false ? null : (int) $validated; }
    private function sanitizePath(string $path): ?string { $path = trim($path); if ($path === '' || !str_starts_with($path, '/')) { return null; } if (mb_strlen($path) > 255) { return null; } return preg_match('/^\/[A-Za-z0-9\-._~\/?=&]*$/', $path) === 1 ? $path : null; }
    private function sanitizeIp(string $ip): ?string { $ip = trim($ip); if ($ip === '') { return null; } return filter_var($ip, FILTER_VALIDATE_IP) === false ? null : $ip; }
    private function maskIp(?string $ip): string { if (!is_string($ip) || $ip === '') { return 'invalid'; } if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) { $p = explode('.', $ip); return $p[0] . '.' . $p[1] . '.x.x'; } $chunks = explode(':', $ip); return (string) ($chunks[0] ?? 'x') . ':' . (string) ($chunks[1] ?? 'x') . ':xxxx:xxxx'; }
    private function maskPath(?string $path): string { if (!is_string($path) || $path === '') { return 'invalid'; } $clean = (string) preg_replace('/\?.*$/', '', $path); return mb_substr($clean, 0, 80); }
}
