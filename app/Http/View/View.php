<?php

declare(strict_types=1);

namespace App\Http\View;

final class View
{
    private const BASE_PATH = __DIR__ . '/../../../resources/views/';

    public static function render(string $view, array $data = []): void
    {
        echo self::make($view, $data);
    }

    public static function make(string $view, array $data = []): string
    {
        $file = self::BASE_PATH . str_replace('.', '/', $view) . '.php';

        if (!is_file($file)) {
            http_response_code(500);
            return 'Vista no disponible.';
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $file;

        return (string) ob_get_clean();
    }
}
