<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'enabled' => filter_var((string) Env::get('ECOSISTEMA_DRIVE_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN),
    'mode' => (string) Env::get('ECOSISTEMA_DRIVE_MODE', 'contract'),
    'reference_repo' => (string) Env::get('ECOSISTEMA_DRIVE_REFERENCE_REPO', 's3'),
    'provider' => (string) Env::get('ECOSISTEMA_DRIVE_PROVIDER', 'aws-s3'),
    'aws_enabled' => filter_var((string) Env::get('ECOSISTEMA_DRIVE_AWS_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN),
    'aws_region' => (string) Env::get('ECOSISTEMA_DRIVE_AWS_REGION', ''),
    'aws_bucket' => (string) Env::get('ECOSISTEMA_DRIVE_AWS_BUCKET', ''),
    'aws_endpoint' => (string) Env::get('ECOSISTEMA_DRIVE_AWS_ENDPOINT', ''),
    'aws_access_key_id' => (string) Env::get('ECOSISTEMA_DRIVE_AWS_ACCESS_KEY_ID', ''),
    'aws_secret_access_key' => (string) Env::get('ECOSISTEMA_DRIVE_AWS_SECRET_ACCESS_KEY', ''),
    'aws_session_token' => (string) Env::get('ECOSISTEMA_DRIVE_AWS_SESSION_TOKEN', ''),
    'api_timeout' => max(1, (int) Env::get('ECOSISTEMA_DRIVE_API_TIMEOUT', '5')),
    'allow_remote_calls' => filter_var((string) Env::get('ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS', 'false'), FILTER_VALIDATE_BOOLEAN),
    'allow_signed_urls' => filter_var((string) Env::get('ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS', 'false'), FILTER_VALIDATE_BOOLEAN),
    'allow_remote_uploads' => filter_var((string) Env::get('ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS', 'false'), FILTER_VALIDATE_BOOLEAN),
    'allow_remote_downloads' => filter_var((string) Env::get('ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS', 'false'), FILTER_VALIDATE_BOOLEAN),
];
