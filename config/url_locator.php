<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'enabled' => filter_var(Env::get('ECOSISTEMA_URL_LOCATOR_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
    'admin_write_enabled' => filter_var(Env::get('ECOSISTEMA_URL_LOCATOR_ADMIN_WRITE_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
    'redirect_dry_run_enabled' => filter_var(Env::get('ECOSISTEMA_URL_LOCATOR_REDIRECT_DRY_RUN_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
    'public_redirects_enabled' => filter_var(Env::get('ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECTS', 'false'), FILTER_VALIDATE_BOOL),
    'tracking_enabled' => filter_var(Env::get('ECOSISTEMA_URL_LOCATOR_TRACKING_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
    'language_redirects_enabled' => filter_var(Env::get('ECOSISTEMA_URL_LOCATOR_LANGUAGE_REDIRECTS', 'false'), FILTER_VALIDATE_BOOL),
    'collect_ip_enabled' => filter_var(Env::get('ECOSISTEMA_URL_LOCATOR_COLLECT_IP', 'false'), FILTER_VALIDATE_BOOL),
    'collect_user_agent_enabled' => filter_var(Env::get('ECOSISTEMA_URL_LOCATOR_COLLECT_USER_AGENT', 'true'), FILTER_VALIDATE_BOOL),
    'smart_links_enabled' => filter_var(Env::get('ECOSISTEMA_URL_LOCATOR_SMART_LINKS', 'false'), FILTER_VALIDATE_BOOL),
    'public_redirect_status' => (int) Env::get('ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECT_STATUS', '302'),
    'public_tenant_id' => (int) Env::get('ECOSISTEMA_URL_LOCATOR_PUBLIC_TENANT_ID', '0'),
    'slug_min_length' => 3,
    'slug_max_length' => 120,
    'allowed_statuses' => ['active', 'inactive', 'expired', 'blocked'],
    'allowed_smart_types' => [0, 1, 2, 3],
    'allow_private_target_urls' => false,
];
