<?php

declare(strict_types=1);

namespace App\Http\Response;

use App\Core\Auth\AuthSession;
use App\Http\View\View;

final class ErrorResponder
{
    public static function render(array $config, int $statusCode, ?string $message = null): void
    {
        http_response_code($statusCode);
        header('Content-Type: text/html; charset=UTF-8');

        $messages = [403 => 'Acceso denegado.', 404 => 'Página no encontrada.', 419 => 'Sesión o token CSRF inválido.', 500 => 'Ocurrió un error interno.'];
        $safeMessage = $message ?? ($messages[$statusCode] ?? 'Ocurrió un error interno.');
        $authenticated = AuthSession::isAuthenticated();

        View::render($authenticated ? 'layouts.admin' : 'layouts.auth', [
            'title' => sprintf('%d | Ecosistema Core Admin', $statusCode),
            'contentView' => sprintf('pages/errors/%d', $statusCode),
            'auth' => $authenticated ? AuthSession::getAuth() : [],
            'csrfToken' => $authenticated ? AuthSession::getCsrfToken() : '',
            'contentData' => ['statusCode' => $statusCode, 'message' => $safeMessage],
        ]);
    }
}
