<?php

declare(strict_types=1);

namespace App\Core\Auth;

use DateTimeImmutable;

final class AuthService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly SessionRepository $sessions,
    ) {
    }

    public function attempt(string $email, string $password, ?string $ipAddress, ?string $userAgent, int $sessionHours = 8): ?array
    {
        $user = $this->users->findActiveByEmail($email);

        if ($user === null || !password_verify($password, (string) $user['password_hash'])) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = (new DateTimeImmutable())->modify(sprintf('+%d hours', $sessionHours));

        $coreSessionId = $this->sessions->create(
            (int) $user['tenant_id'],
            (int) $user['id'],
            $tokenHash,
            $ipAddress,
            $userAgent,
            $expiresAt
        );

        $this->users->updateLastLoginAt((int) $user['id']);

        return [
            'auth_user_id' => (int) $user['id'],
            'auth_tenant_id' => (int) $user['tenant_id'],
            'auth_email' => (string) $user['email'],
            'auth_display_name' => (string) $user['display_name'],
            'auth_core_session_id' => $coreSessionId,
        ];
    }

    public function logout(?int $coreSessionId): void
    {
        if ($coreSessionId !== null) {
            $this->sessions->revokeById($coreSessionId);
        }
    }
}
