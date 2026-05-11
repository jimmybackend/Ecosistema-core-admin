<?php

declare(strict_types=1);

namespace App\Core\Auth;

final class AuthSession
{
    private const CSRF_KEY = 'csrf_token';
    private const LAST_ACTIVITY_KEY = 'auth_last_activity_at';

    public static function start(string $sessionName, bool $secure, string $sameSite = 'Lax'): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');

        session_name($sessionName);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => $sameSite,
        ]);

        session_start();
    }

    public static function enforceIdleTimeout(int $idleTimeout): bool
    {
        if ($idleTimeout <= 0 || !self::isAuthenticated()) {
            return true;
        }

        $lastActivity = $_SESSION[self::LAST_ACTIVITY_KEY] ?? null;
        $now = time();

        if (is_int($lastActivity) && ($now - $lastActivity) > $idleTimeout) {
            return false;
        }

        $_SESSION[self::LAST_ACTIVITY_KEY] = $now;
        return true;
    }

    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['auth_user_id'], $_SESSION['auth_core_session_id']);
    }

    public static function setAuth(array $data): void
    {
        session_regenerate_id(true);
        $_SESSION['auth_user_id'] = (int) $data['auth_user_id'];
        $_SESSION['auth_tenant_id'] = (int) $data['auth_tenant_id'];
        $_SESSION['auth_email'] = (string) $data['auth_email'];
        $_SESSION['auth_display_name'] = (string) $data['auth_display_name'];
        $_SESSION['auth_core_session_id'] = (int) $data['auth_core_session_id'];
        $_SESSION[self::LAST_ACTIVITY_KEY] = time();
    }

    public static function getAuth(): array
    {
        return [
            'auth_user_id' => $_SESSION['auth_user_id'] ?? null,
            'auth_tenant_id' => $_SESSION['auth_tenant_id'] ?? null,
            'auth_email' => $_SESSION['auth_email'] ?? null,
            'auth_display_name' => $_SESSION['auth_display_name'] ?? null,
            'auth_core_session_id' => $_SESSION['auth_core_session_id'] ?? null,
        ];
    }

    public static function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::CSRF_KEY] = $token;

        return $token;
    }

    public static function getCsrfToken(): string
    {
        $token = $_SESSION[self::CSRF_KEY] ?? null;
        if (!is_string($token) || $token === '') {
            return self::generateCsrfToken();
        }

        return $token;
    }

    public static function validateCsrfToken(?string $token): bool
    {
        $stored = $_SESSION[self::CSRF_KEY] ?? null;
        if (!is_string($stored) || $stored === '' || !is_string($token)) {
            return false;
        }

        return hash_equals($stored, $token);
    }

    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }

        session_destroy();
    }
}
