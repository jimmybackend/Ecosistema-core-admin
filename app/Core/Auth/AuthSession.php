<?php

declare(strict_types=1);

namespace App\Core\Auth;

final class AuthSession
{
    private const CSRF_KEY = 'csrf_token';

    public static function start(string $sessionName, bool $secure): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name($sessionName);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
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
