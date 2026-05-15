<?php
declare(strict_types=1);

namespace App\Core\Security;

use PDO;

final class EcosistemaRateLimitDryRunRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function countRecentApiRequests(int $tenantId, string $path, string $ipAddress, int $windowMinutes): int
    {
        $sql = 'SELECT COUNT(*) FROM system_api_requests WHERE tenant_id = :tenant_id AND path = :path AND ip_address = :ip_address AND created_at >= (NOW() - INTERVAL :window_minutes MINUTE)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':path', $path, PDO::PARAM_STR);
        $stmt->bindValue(':ip_address', $ipAddress, PDO::PARAM_STR);
        $stmt->bindValue(':window_minutes', $windowMinutes, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function countRecentFailedLoginsByIp(int $tenantId, string $ipAddress, int $windowMinutes): int
    {
        $sql = "SELECT COUNT(*) FROM security_login_attempts WHERE tenant_id = :tenant_id AND ip_address = :ip_address AND status = 'failed' AND attempted_at >= (NOW() - INTERVAL :window_minutes MINUTE)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':ip_address', $ipAddress, PDO::PARAM_STR);
        $stmt->bindValue(':window_minutes', $windowMinutes, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}
