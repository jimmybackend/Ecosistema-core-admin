<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'enabled' => filter_var(Env::get('ECOSISTEMA_URL_LOCATOR_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
    'admin_write_enabled' => filter_var(Env::get('ECOSISTEMA_URL_LOCATOR_ADMIN_WRITE_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
    'redirect_dry_run_enabled' => filter_var(Env::get('ECOSISTEMA_URL_LOCATOR_REDIRECT_DRY_RUN_ENABLED', 'false'), FILTER_VALIDATE_BOOL),
    'public_redirects_enabled' => false,
    'tracking_enabled' => false,
    'slug_min_length' => 3,
    'slug_max_length' => 120,
    'allowed_statuses' => ['active', 'inactive', 'expired', 'blocked'],
    'allowed_smart_types' => [0, 1, 2, 3],
    'allow_private_target_urls' => false,
];
