<?php

declare(strict_types=1);

use App\Support\Env;

$resolvedBucket = trim((string) Env::get('AWS_BUCKET', ''));
if ($resolvedBucket === '') {
    foreach (['AWS_S3_BUCKET', 'S3_BUCKET', 'CLOUD_BUCKET'] as $fallbackBucketKey) {
        $candidate = trim((string) Env::get($fallbackBucketKey, ''));
        if ($candidate !== '') {
            $resolvedBucket = $candidate;
            break;
        }
    }
}

return [
    'disk' => (string) Env::get('CLOUD_DISK', 's3'),
    's3_enabled' => filter_var((string) Env::get('CLOUD_S3_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN),
    'allow_downloads' => filter_var((string) Env::get('CLOUD_ALLOW_DOWNLOADS', 'false'), FILTER_VALIDATE_BOOLEAN),
    'allow_uploads' => filter_var((string) Env::get('CLOUD_ALLOW_UPLOADS', 'false'), FILTER_VALIDATE_BOOLEAN),
    'max_upload_mb' => max(1, (int) Env::get('CLOUD_MAX_UPLOAD_MB', '10')),
    'allowed_extensions' => array_values(array_filter(array_map(static fn(string $v): string => strtolower(trim($v)), explode(',', (string) Env::get('CLOUD_ALLOWED_EXTENSIONS', 'pdf,jpg,jpeg,png,txt,doc,docx,xls,xlsx'))))),
    'upload_prefix' => (string) Env::get('CLOUD_UPLOAD_PREFIX', 'uploads'),
    'local_storage_path' => (string) Env::get('CLOUD_LOCAL_STORAGE_PATH', 'storage/app/cloud'),
    's3_prefix' => trim((string) Env::get('AWS_S3_PREFIX', 'users'), '/'),
    's3_paths' => [
        'mail_attachments' => 'users/{user_id}/mail/attachments',
        'products_images' => 'users/{user_id}/products/images',
        'products_videos' => 'users/{user_id}/products/videos',
        'generated' => 'users/{user_id}/generated',
    ],
    's3' => [
        'access_key_id' => (string) Env::get('AWS_ACCESS_KEY_ID', ''),
        'secret_access_key' => (string) Env::get('AWS_SECRET_ACCESS_KEY', ''),
        'region' => (string) Env::get('AWS_DEFAULT_REGION', 'us-east-1'),
        'bucket' => $resolvedBucket,
        'endpoint' => (string) Env::get('AWS_ENDPOINT', ''),
        'use_path_style_endpoint' => filter_var((string) Env::get('AWS_USE_PATH_STYLE_ENDPOINT', 'false'), FILTER_VALIDATE_BOOLEAN),
    ],
];
