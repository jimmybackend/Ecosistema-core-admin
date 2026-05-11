<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'disk' => (string) Env::get('CLOUD_DISK', 's3'),
    's3_enabled' => filter_var((string) Env::get('CLOUD_S3_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN),
    'allow_downloads' => filter_var((string) Env::get('CLOUD_ALLOW_DOWNLOADS', 'false'), FILTER_VALIDATE_BOOLEAN),
    'allow_uploads' => filter_var((string) Env::get('CLOUD_ALLOW_UPLOADS', 'false'), FILTER_VALIDATE_BOOLEAN),
    's3' => [
        'access_key_id' => (string) Env::get('AWS_ACCESS_KEY_ID', ''),
        'secret_access_key' => (string) Env::get('AWS_SECRET_ACCESS_KEY', ''),
        'region' => (string) Env::get('AWS_DEFAULT_REGION', 'us-east-1'),
        'bucket' => (string) Env::get('AWS_BUCKET', ''),
        'endpoint' => (string) Env::get('AWS_ENDPOINT', ''),
        'use_path_style_endpoint' => filter_var((string) Env::get('AWS_USE_PATH_STYLE_ENDPOINT', 'false'), FILTER_VALIDATE_BOOLEAN),
    ],
];
