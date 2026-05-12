<?php

declare(strict_types=1);

namespace App\Core\Auth;

use DateTimeImmutable;
use PDO;

final class SessionRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function create(int $tenantId, int $userId, string $sessionTokenHash, ?string $ipAddress, ?string $userAgent, DateTimeImmutable $expiresAt): int
    {
        $sql = 'INSERT INTO core_sessions (tenant_id, user_id, session_token_hash, refresh_token_hash, source, ip_address, user_agent, expires_at, revoked_at, created_at) VALUES (:tenant_id, :user_id, :session_token_hash, :refresh_token_hash, :source, :ip_address, :user_agent, :expires_at, NULL, NOW())';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':tenant_id' => $tenantId,
            ':user_id' => $userId,
            ':session_token_hash' => $sessionTokenHash,
            ':refresh_token_hash' => null,
            ':source' => 'browser',
            ':ip_address' => $ipAddress,
            ':user_agent' => $userAgent,
            ':expires_at' => $expiresAt->format('Y-m-d H:i:s'),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function revokeById(int $coreSessionId): void
    {
        $sql = 'UPDATE core_sessions SET revoked_at = NOW() WHERE id = :id AND revoked_at IS NULL';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $coreSessionId]);
    }

    /**
     * @return array{candidates:int,revoked:int}
     */
    public function revokeExpiredSessions(DateTimeImmutable $threshold): array
    {
        $countSql = 'SELECT COUNT(*) FROM core_sessions WHERE revoked_at IS NULL AND expires_at < :threshold';
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute([':threshold' => $threshold->format('Y-m-d H:i:s')]);
        $candidates = (int) $countStmt->fetchColumn();

        $updateSql = 'UPDATE core_sessions SET revoked_at = NOW() WHERE revoked_at IS NULL AND expires_at < :threshold';
        $updateStmt = $this->pdo->prepare($updateSql);
        $updateStmt->execute([':threshold' => $threshold->format('Y-m-d H:i:s')]);

        return [
            'candidates' => $candidates,
            'revoked' => $updateStmt->rowCount(),
        ];
    }

}
