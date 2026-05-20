#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Core\Database\PdoFactory;
use App\Support\Env;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    echo "[FAIL] vendor/autoload.php faltante. Ejecuta composer install.\n";
    exit(1);
}
require_once $autoload;
require_once $root . '/app/Support/Env.php';

Env::load($root . '/.env');
$config = require $root . '/config/database.php';

$options = getopt('', ['tenant::', 'user::']);
$tenantId = (int)($options['tenant'] ?? 1);
$userId = (int)($options['user'] ?? 1);

$required = [
    'cloud_buckets' => ['id','tenant_id','bucket_name','provider','region','base_prefix','is_default','status','config_json','created_at','updated_at'],
    'cloud_user_roots' => ['id','tenant_id','user_id','bucket_id','root_prefix','display_name','quota_bytes','used_bytes','file_count','status','created_at','updated_at'],
    'cloud_folders' => ['id','tenant_id','user_id','bucket_id','root_id','parent_folder_id','name','prefix','folder_type','access_type','password_hash','secure_hint','found_in_s3','is_system','is_deleted','deleted_at','created_at','updated_at'],
    'cloud_files' => ['id','tenant_id','user_id','bucket_id','root_id','folder_id','original_name','stored_name','s3_key','mime_type','extension','size_bytes','checksum_sha256','etag','storage_class','metadata_json','origin_module','origin_table','origin_id','access_type','password_hash','secure_hint','encrypted','encryption_key_ref','found_in_s3','virus_scan_status','status','uploaded_by_user_id','uploaded_at','updated_at','deleted_at'],
    'cloud_file_versions' => ['id','tenant_id','file_id','bucket_id','version_no','s3_key','s3_version_id','size_bytes','checksum_sha256','created_by_user_id','created_at'],
    'cloud_file_access_logs' => ['id','tenant_id','file_id','user_id','share_id','session_id','action','ip_address','user_agent','country','region','city','metadata_json','created_at'],
    'cloud_storage_usage_daily' => [],
    'mail_external_attachments' => ['id','tenant_id','message_id','cloud_file_id','legacy_source','legacy_table','legacy_attachment_id','external_url','original_filename','mime_type','size_bytes','import_status','error_message','raw_payload_json','imported_at','created_at'],
    'mail_attachments' => ['id','tenant_id','message_id','cloud_file_id','original_filename','content_id','disposition','mime_type','size_bytes','open_count','download_count','created_at'],
];

try {
    $pdo = PdoFactory::make($config);
} catch (Throwable $e) {
    echo "[FAIL] DB no disponible: {$e->getMessage()}\n";
    exit(2);
}

$dbName = (string)($config['connections']['mysql']['database'] ?? '');
$stmt = $pdo->prepare('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table ORDER BY ORDINAL_POSITION');

$fail = 0;
echo "cloud-schema-debug tenant={$tenantId} user={$userId} db={$dbName}\n";
foreach ($required as $table => $columns) {
    $stmt->execute(['schema' => $dbName, 'table' => $table]);
    $existing = array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    if ($existing === []) {
        echo "[FAIL] {$table}: tabla no encontrada\n";
        $fail++;
        continue;
    }
    $missing = array_values(array_diff($columns, $existing));
    echo "[TABLE] {$table}\n";
    echo '  existing: ' . implode(', ', $existing) . "\n";
    echo '  required: ' . implode(', ', $columns) . "\n";
    echo '  missing: ' . ($missing === [] ? '-' : implode(', ', $missing)) . "\n";
    if ($missing !== []) { $fail++; }
}

echo $fail === 0 ? "[RESULT] OK\n" : "[RESULT] FAIL ({$fail} tablas con faltantes)\n";
exit($fail === 0 ? 0 : 1);
