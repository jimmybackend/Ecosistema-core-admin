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

$criticalColumns = [
    'core_users' => ['id', 'tenant_id', 'email', 'username', 'password_hash', 'status'],
    'core_sessions' => ['id', 'tenant_id', 'user_id', 'session_token_hash', 'expires_at'],
    'core_tenants' => ['id', 'name', 'slug', 'status'],
    'core_roles' => ['id', 'tenant_id', 'slug', 'name', 'is_system'],
    'core_permissions' => ['id', 'module_id', 'code', 'name'],
    'core_role_permissions' => ['tenant_id', 'role_id', 'permission_id'],
    'core_user_roles' => ['tenant_id', 'user_id', 'role_id', 'assigned_by_user_id'],
    'core_modules' => ['id', 'code', 'name'],
    'core_audit' => ['id', 'tenant_id', 'user_id', 'action', 'entity_type'],
    'cloud_files' => ['id', 'tenant_id', 'user_id', 'original_name', 'stored_name', 's3_key', 'status'],
    'cloud_folders' => ['id', 'tenant_id', 'user_id', 'name', 'status'],
    'mail_messages' => ['id', 'tenant_id', 'user_id', 'subject', 'status', 'created_at'],
    'notifications_queue' => ['id', 'tenant_id', 'user_id', 'channel_id', 'status', 'created_at'],
    'crm_leads' => ['id', 'tenant_id', 'email', 'status', 'created_at'],
    'crm_marketing_campaigns' => ['id', 'tenant_id', 'name', 'code', 'status'],
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
