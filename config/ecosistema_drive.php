<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'enabled' => filter_var((string) Env::get('ECOSISTEMA_DRIVE_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN),
    'mode' => (string) Env::get('ECOSISTEMA_DRIVE_MODE', 'contract'),
    'reference_repo' => (string) Env::get('ECOSISTEMA_DRIVE_REFERENCE_REPO', 's3'),
    'api_timeout' => max(1, (int) Env::get('ECOSISTEMA_DRIVE_API_TIMEOUT', '5')),
    'allow_remote_calls' => filter_var((string) Env::get('ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS', 'false'), FILTER_VALIDATE_BOOLEAN),
    'allow_signed_urls' => filter_var((string) Env::get('ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS', 'false'), FILTER_VALIDATE_BOOLEAN),
    'allow_remote_uploads' => filter_var((string) Env::get('ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS', 'false'), FILTER_VALIDATE_BOOLEAN),
    'allow_remote_downloads' => filter_var((string) Env::get('ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS', 'false'), FILTER_VALIDATE_BOOLEAN),
];
