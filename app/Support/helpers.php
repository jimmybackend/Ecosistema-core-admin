<?php

declare(strict_types=1);

use App\Core\Auth\AuthSession;
use App\Core\Auth\AuthorizationRepository;
use App\Core\Auth\AuthorizationService;
use App\Core\Database\PdoFactory;
use App\Http\Response\ErrorResponder;

if (!function_exists('e')) {
    function e(null|bool|int|float|string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('requirePermission')) {
    function requirePermission(array $config, string $permissionCode): bool
    {
        $auth = AuthSession::getAuth();
        $userId = (int) ($auth['auth_user_id'] ?? 0);
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new AuthorizationService(new AuthorizationRepository($pdo));

            if ($service->can($userId, $tenantId, $permissionCode)) {
                return true;
            }
        } catch (\Throwable) {
        }

        ErrorResponder::render($config, 403);

        return false;
    }
}


if (!function_exists('renderError')) {
    function renderError(array $config, int $statusCode, ?string $message = null): void
    {
        ErrorResponder::render($config, $statusCode, $message);
    }
}

if (!function_exists('ensureValidCsrfToken')) {
    function ensureValidCsrfToken(array $config, mixed $csrfToken): bool
    {
        if (AuthSession::validateCsrfToken(is_string($csrfToken) ? $csrfToken : null)) {
            return true;
        }

        renderError($config, 419);

        return false;
    }
}
