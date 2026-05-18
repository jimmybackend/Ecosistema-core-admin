<?php
declare(strict_types=1);

namespace App\Core\Security;

use PDO;

final class EcosistemaRateLimitRepository
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

    public function insertBlockedIp(int $tenantId, string $ipAddress, string $reason, int $blockedByUserId, int $expiresInMinutes): bool
    {
        $sql = 'INSERT INTO security_blocked_ips (tenant_id, ip_address, reason, blocked_by_user_id, blocked_at, expires_at) VALUES (:tenant_id, :ip_address, :reason, :blocked_by_user_id, NOW(), DATE_ADD(NOW(), INTERVAL :expires_in_minutes MINUTE))';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':ip_address', $ipAddress, PDO::PARAM_STR);
        $stmt->bindValue(':reason', $reason, PDO::PARAM_STR);
        $stmt->bindValue(':blocked_by_user_id', $blockedByUserId, PDO::PARAM_INT);
        $stmt->bindValue(':expires_in_minutes', $expiresInMinutes, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function insertIncident(int $tenantId, int $reportedByUserId, string $title, string $description, string $severity, string $status, string $sourceModule, string $sourceTable, ?int $sourceId): bool
    {
        $sql = 'INSERT INTO security_incidents (tenant_id, reported_by_user_id, title, description, severity, status, source_module, source_table, source_id, detected_at, created_at, updated_at) VALUES (:tenant_id, :reported_by_user_id, :title, :description, :severity, :status, :source_module, :source_table, :source_id, NOW(), NOW(), NOW())';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->bindValue(':reported_by_user_id', $reportedByUserId, PDO::PARAM_INT);
        $stmt->bindValue(':title', $title, PDO::PARAM_STR);
        $stmt->bindValue(':description', $description, PDO::PARAM_STR);
        $stmt->bindValue(':severity', $severity, PDO::PARAM_STR);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':source_module', $sourceModule, PDO::PARAM_STR);
        $stmt->bindValue(':source_table', $sourceTable, PDO::PARAM_STR);
        $stmt->bindValue(':source_id', $sourceId, $sourceId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

        return $stmt->execute();
    }
}
