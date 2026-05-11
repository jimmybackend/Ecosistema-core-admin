<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'mailer' => Env::get('MAIL_MAILER', 'smtp'),
    'host' => Env::get('MAIL_HOST', 'smtp.example.com'),
    'port' => (int) Env::get('MAIL_PORT', 587),
    'username' => Env::get('MAIL_USERNAME', 'change-me'),
    'password' => Env::get('MAIL_PASSWORD', 'change-me'),
    'encryption' => Env::get('MAIL_ENCRYPTION', 'tls'),
    'from' => [
        'address' => Env::get('MAIL_FROM_ADDRESS', 'no-reply@example.com'),
        'name' => Env::get('MAIL_FROM_NAME', 'Ecosistema'),
    ],
    'send_enabled' => filter_var(Env::get('MAIL_SEND_ENABLED', false), FILTER_VALIDATE_BOOL),
    'allow_test_send' => filter_var(Env::get('MAIL_ALLOW_TEST_SEND', false), FILTER_VALIDATE_BOOL),
];
