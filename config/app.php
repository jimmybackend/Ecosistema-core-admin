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
        'campaign_creation_dry_run' => filter_var(Env::get('ECOSISTEMA_CAMPAIGN_CREATION_DRY_RUN', 'false'), FILTER_VALIDATE_BOOL),
        'campaign_creation_write' => filter_var(Env::get('ECOSISTEMA_CAMPAIGN_CREATION_WRITE', 'false'), FILTER_VALIDATE_BOOL),
        'campaign_create_landing_draft' => filter_var(Env::get('ECOSISTEMA_CAMPAIGN_CREATE_LANDING_DRAFT', 'false'), FILTER_VALIDATE_BOOL),
        'campaign_create_short_link' => filter_var(Env::get('ECOSISTEMA_CAMPAIGN_CREATE_SHORT_LINK', 'false'), FILTER_VALIDATE_BOOL),
    ],
    'ecosistema_mail_notifications' => [
        'enabled' => filter_var(Env::get('ECOSISTEMA_MAIL_NOTIFICATIONS_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
        'preview_dry_run' => filter_var(Env::get('ECOSISTEMA_MAIL_PREVIEW_DRY_RUN', 'false'), FILTER_VALIDATE_BOOL),
        'send_dry_run' => filter_var(Env::get('ECOSISTEMA_MAIL_SEND_DRY_RUN', 'false'), FILTER_VALIDATE_BOOL),
        'send_enabled' => filter_var(Env::get('ECOSISTEMA_MAIL_SEND_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
    ],

    'ecosistema_attribution' => [
        'enabled' => filter_var(Env::get('ECOSISTEMA_ATTRIBUTION_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
        'write_enabled' => filter_var(Env::get('ECOSISTEMA_ATTRIBUTION_WRITE', 'false'), FILTER_VALIDATE_BOOL),
        'rollup_dry_run' => filter_var(Env::get('ECOSISTEMA_ATTRIBUTION_ROLLUP_DRY_RUN', 'false'), FILTER_VALIDATE_BOOL),
        'rollup_write' => filter_var(Env::get('ECOSISTEMA_ATTRIBUTION_ROLLUP_WRITE', 'false'), FILTER_VALIDATE_BOOL),
    ],


    'ecosistema_url_locator' => [
        'tracking_enabled' => filter_var(Env::get('ECOSISTEMA_URL_LOCATOR_TRACKING_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
        'collect_ip_enabled' => filter_var(Env::get('ECOSISTEMA_URL_LOCATOR_COLLECT_IP', 'false'), FILTER_VALIDATE_BOOL),
        'collect_user_agent_enabled' => filter_var(Env::get('ECOSISTEMA_URL_LOCATOR_COLLECT_USER_AGENT', 'false'), FILTER_VALIDATE_BOOL),
    ],

    'ecosistema_browser_analytics' => [
        'enabled' => filter_var(Env::get('ECOSISTEMA_BROWSER_ANALYTICS_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
        'collector_write' => filter_var(Env::get('ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_WRITE', 'false'), FILTER_VALIDATE_BOOL),
        'collect_ip' => filter_var(Env::get('ECOSISTEMA_BROWSER_ANALYTICS_COLLECT_IP', 'false'), FILTER_VALIDATE_BOOL),
        'collect_user_agent' => filter_var(Env::get('ECOSISTEMA_BROWSER_ANALYTICS_COLLECT_USER_AGENT', 'false'), FILTER_VALIDATE_BOOL),
    ],
    'ecosistema_landing' => [
        'public_render_dry_run' => filter_var(Env::get('ECOSISTEMA_LANDING_PUBLIC_RENDER_DRY_RUN', 'false'), FILTER_VALIDATE_BOOL),
        'form_submit_dry_run' => filter_var(Env::get('ECOSISTEMA_LANDING_FORM_SUBMIT_DRY_RUN', 'false'), FILTER_VALIDATE_BOOL),
    ],

    'ecosistema_workflow' => [
        'enabled' => filter_var(Env::get('ECOSISTEMA_WORKFLOW_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
        'dry_run_enabled' => filter_var(Env::get('ECOSISTEMA_WORKFLOW_DRY_RUN_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
        'execution_enabled' => filter_var(Env::get('ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
        'action_send_email' => filter_var(Env::get('ECOSISTEMA_WORKFLOW_ACTION_SEND_EMAIL', 'false'), FILTER_VALIDATE_BOOL),
        'action_webhook' => filter_var(Env::get('ECOSISTEMA_WORKFLOW_ACTION_WEBHOOK', 'false'), FILTER_VALIDATE_BOOL),
        'action_update_record' => filter_var(Env::get('ECOSISTEMA_WORKFLOW_ACTION_UPDATE_RECORD', 'false'), FILTER_VALIDATE_BOOL),
        'action_create_notification' => filter_var(Env::get('ECOSISTEMA_WORKFLOW_ACTION_CREATE_NOTIFICATION', 'false'), FILTER_VALIDATE_BOOL),
        'action_create_task' => filter_var(Env::get('ECOSISTEMA_WORKFLOW_ACTION_CREATE_TASK', 'false'), FILTER_VALIDATE_BOOL),
        'action_create_ticket' => filter_var(Env::get('ECOSISTEMA_WORKFLOW_ACTION_CREATE_TICKET', 'false'), FILTER_VALIDATE_BOOL),
        'action_create_agenda_event' => filter_var(Env::get('ECOSISTEMA_WORKFLOW_ACTION_CREATE_AGENDA_EVENT', 'false'), FILTER_VALIDATE_BOOL),
        'action_custom' => filter_var(Env::get('ECOSISTEMA_WORKFLOW_ACTION_CUSTOM', 'false'), FILTER_VALIDATE_BOOL),
        'template_install_dry_run' => filter_var(Env::get('ECOSISTEMA_WORKFLOW_TEMPLATE_INSTALL_DRY_RUN', 'false'), FILTER_VALIDATE_BOOL),
        'template_install_write' => filter_var(Env::get('ECOSISTEMA_WORKFLOW_TEMPLATE_INSTALL_WRITE', 'false'), FILTER_VALIDATE_BOOL),
    ],

    'ecosistema_ai' => [
        'enabled' => filter_var(Env::get('ECOSISTEMA_AI_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
        'lead_summary_dry_run' => filter_var(Env::get('ECOSISTEMA_AI_LEAD_SUMMARY_DRY_RUN', 'false'), FILTER_VALIDATE_BOOL),
        'campaign_insight_dry_run' => filter_var(Env::get('ECOSISTEMA_AI_CAMPAIGN_INSIGHT_DRY_RUN', 'false'), FILTER_VALIDATE_BOOL),
        'provider_enabled' => filter_var(Env::get('ECOSISTEMA_AI_PROVIDER_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
        'write_proposals' => filter_var(Env::get('ECOSISTEMA_AI_WRITE_PROPOSALS', 'false'), FILTER_VALIDATE_BOOL),
    ],

    'ecosistema_security' => [
        'rate_limit_enabled' => filter_var(Env::get('ECOSISTEMA_RATE_LIMIT_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
        'rate_limit_dry_run' => filter_var(Env::get('ECOSISTEMA_RATE_LIMIT_DRY_RUN', 'false'), FILTER_VALIDATE_BOOL),
        'rate_limit_write_blocks' => filter_var(Env::get('ECOSISTEMA_RATE_LIMIT_WRITE_BLOCKS', 'false'), FILTER_VALIDATE_BOOL),
    ],

    'core_registration' => [
        'enabled' => filter_var(Env::get('CORE_REGISTRATION_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
        'mode' => Env::get('CORE_REGISTRATION_MODE', 'first_user'),
        'invite_code' => Env::get('CORE_REGISTRATION_INVITE_CODE', ''),
        'default_tenant_id' => Env::get('CORE_REGISTRATION_DEFAULT_TENANT_ID', ''),
        'default_role_id' => Env::get('CORE_REGISTRATION_DEFAULT_ROLE_ID', ''),
    ],
];
