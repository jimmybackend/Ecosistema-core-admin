<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'name' => Env::get('APP_NAME', 'Ecosistema Core Admin'),
    'env' => Env::get('APP_ENV', 'local'),
    'debug' => filter_var(Env::get('APP_DEBUG', true), FILTER_VALIDATE_BOOL),
    'url' => Env::get('APP_URL', 'http://127.0.0.1:8000'),
    'timezone' => Env::get('TIMEZONE', 'UTC'),
    'layer' => 'Capa 3 — Configuración de entorno y conexión PDO segura',
    'session' => [
        'name' => Env::get('SESSION_NAME', 'ecosistema_core_admin'),
        'secure' => filter_var(Env::get('SESSION_SECURE', false), FILTER_VALIDATE_BOOL),
        'samesite' => Env::get('SESSION_SAMESITE', 'Lax'),
        'idle_timeout' => (int) Env::get('SESSION_IDLE_TIMEOUT', 1800),
    ],
    'ecosistema_crm' => [
        'enabled' => filter_var(Env::get('ECOSISTEMA_CRM_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
        'submission_to_lead_dry_run' => filter_var(Env::get('ECOSISTEMA_CRM_SUBMISSION_TO_LEAD_DRY_RUN', 'false'), FILTER_VALIDATE_BOOL),
        'submission_to_lead_write' => filter_var(Env::get('ECOSISTEMA_CRM_SUBMISSION_TO_LEAD_WRITE', 'false'), FILTER_VALIDATE_BOOL),
    ],
    'ecosistema_mail_notifications' => [
        'enabled' => filter_var(Env::get('ECOSISTEMA_MAIL_NOTIFICATIONS_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
        'preview_dry_run' => filter_var(Env::get('ECOSISTEMA_MAIL_PREVIEW_DRY_RUN', 'false'), FILTER_VALIDATE_BOOL),
        'send_dry_run' => filter_var(Env::get('ECOSISTEMA_MAIL_SEND_DRY_RUN', 'false'), FILTER_VALIDATE_BOOL),
        'send_enabled' => filter_var(Env::get('ECOSISTEMA_MAIL_SEND_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
    ],
    'core_registration' => [
        'enabled' => filter_var(Env::get('CORE_REGISTRATION_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
        'mode' => Env::get('CORE_REGISTRATION_MODE', 'first_user'),
        'invite_code' => Env::get('CORE_REGISTRATION_INVITE_CODE', ''),
        'default_tenant_id' => Env::get('CORE_REGISTRATION_DEFAULT_TENANT_ID', ''),
        'default_role_id' => Env::get('CORE_REGISTRATION_DEFAULT_ROLE_ID', ''),
    ],
];
