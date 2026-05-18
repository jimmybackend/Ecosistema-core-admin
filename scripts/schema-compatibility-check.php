#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Core\Database\PdoFactory;
use App\Support\Env;

$root = dirname(__DIR__);
$warnings = 0;
$failures = 0;

function out(string $status, string $message): void
{
    echo sprintf('[%s] %s%s', $status, $message, PHP_EOL);
}

function ok(string $message): void
{
    out('OK', $message);
}

function warn(string $message, int &$warnings): void
{
    $warnings++;
    out('WARN', $message);
}

function fail(string $message, int &$failures): void
{
    $failures++;
    out('FAIL', $message);
}

$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    warn('No existe vendor/autoload.php. Ejecuta composer install para habilitar este chequeo.', $warnings);
    exit(0);
}

require_once $autoload;
require_once $root . '/app/Support/Env.php';

Env::load($root . '/.env');

$config = require $root . '/config/database.php';

try {
    /** @var PDO $pdo */
    $pdo = PdoFactory::make($config);
} catch (Throwable $exception) {
    warn('DB no disponible, se omite verificación de esquema (read-only).', $warnings);
    exit(2);
}

$dbName = (string) (($config['connections']['mysql']['database'] ?? ''));
if ($dbName === '') {
    warn('DB_DATABASE no configurado. Se omite verificación de esquema read-only.', $warnings);
    exit(2);
}

$canonicalDbName = 'adbbmis1_eco';
if ($dbName === $canonicalDbName) {
    ok("DB canónica configurada: {$canonicalDbName}");
} else {
    warn("DB configurada ({$dbName}) distinta a la canónica esperada ({$canonicalDbName}).", $warnings);
}

$criticalColumns = [
    'core_users' => ['id', 'tenant_id', 'email', 'username', 'password_hash', 'status'],
    'core_sessions' => ['id', 'tenant_id', 'user_id', 'session_token_hash', 'expires_at'],
    'core_tenants' => ['id', 'name', 'slug', 'status'],
    'core_roles' => ['id', 'tenant_id', 'slug', 'name', 'is_system'],
    'core_permissions' => ['id', 'module_id', 'code', 'name'],
    'core_role_permissions' => ['tenant_id', 'role_id', 'permission_id'],
    'core_user_roles' => ['tenant_id', 'user_id', 'role_id', 'assigned_by_user_id'],
    'core_modules' => ['id', 'code', 'name'],
    'core_audit' => ['id', 'tenant_id', 'user_id', 'session_id', 'module_code', 'action', 'entity_table', 'entity_id', 'old_values', 'new_values', 'reason', 'ip_address', 'user_agent', 'created_at'],
    'cloud_files' => ['id', 'tenant_id', 'user_id', 'original_name', 'stored_name', 's3_key', 'status'],
    'cloud_folders' => ['id', 'tenant_id', 'user_id', 'bucket_id', 'root_id', 'parent_folder_id', 'name', 'prefix', 'prefix_hash', 'folder_type', 'access_type', 'password_hash', 'secure_hint', 'found_in_s3', 'is_system', 'is_deleted', 'deleted_at', 'created_at', 'updated_at'],
    'mail_messages' => ['id', 'tenant_id', 'user_id', 'subject', 'status', 'created_at'],
    'notifications_queue' => ['id', 'tenant_id', 'user_id', 'channel_id', 'status', 'created_at'],
    'crm_leads' => ['id', 'tenant_id', 'email', 'status', 'created_at'],
    'crm_marketing_campaigns' => ['id', 'tenant_id', 'name', 'code', 'status'],
    'workflow_rules' => ['id', 'tenant_id', 'name', 'trigger_module', 'trigger_event', 'is_active'],
    'workflow_runs' => ['id', 'tenant_id', 'rule_id', 'status', 'started_at', 'created_at'],
    'workflow_run_logs' => ['id', 'tenant_id', 'run_id', 'level', 'message', 'created_at'],
    'os_ai_proposals' => ['id', 'tenant_id', 'module_code', 'entity_table', 'entity_id', 'status', 'created_at'],
];

$statement = $pdo->prepare(
    'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table'
);

if ($statement === false) {
    warn('No fue posible preparar consulta INFORMATION_SCHEMA. Se omite verificación.', $warnings);
    exit(2);
}

foreach ($criticalColumns as $table => $columns) {
    $statement->execute([
        'schema' => $dbName,
        'table' => $table,
    ]);

    $existing = $statement->fetchAll(PDO::FETCH_COLUMN);
    if (!is_array($existing) || $existing === []) {
        fail("Tabla crítica no encontrada o sin columnas visibles: {$table}", $failures);
        continue;
    }

    $existingMap = array_fill_keys(array_map('strval', $existing), true);
    foreach ($columns as $column) {
        if (!isset($existingMap[$column])) {
            fail("Falta columna crítica {$table}.{$column}", $failures);
        } else {
            ok("Detectada columna crítica {$table}.{$column}");
        }
    }
}

if ($failures > 0) {
    out('SUMMARY', "{$failures} incompatibilidad(es) detectada(s); {$warnings} warning(s).");
    exit(1);
}

out('SUMMARY', 'Compatibilidad de esquema crítica OK (modo read-only: INFORMATION_SCHEMA/SELECT). No se realizaron escrituras.');
exit(0);
