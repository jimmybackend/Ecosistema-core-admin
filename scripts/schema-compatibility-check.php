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
    warn('DB no disponible, se omite verificación de esquema: ' . $exception->getMessage(), $warnings);
    exit(0);
}

$dbName = (string) (($config['connections']['mysql']['database'] ?? ''));
if ($dbName === '') {
    warn('DB_DATABASE no configurado. Se omite verificación de esquema.', $warnings);
    exit(0);
}

$criticalColumns = [
    'core_role_permissions' => ['tenant_id', 'role_id', 'permission_id'],
    'core_users' => ['tenant_id'],
    'core_sessions' => ['session_token_hash'],
    'core_audit' => ['action'],
    'cloud_files' => ['tenant_id'],
    'mail_messages' => ['tenant_id'],
    'crm_marketing_campaigns' => ['tenant_id'],
    'reports_exports' => ['tenant_id'],
];

$statement = $pdo->prepare(
    'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :schema AND TABLE_NAME = :table'
);

if ($statement === false) {
    warn('No fue posible preparar consulta INFORMATION_SCHEMA. Se omite verificación.', $warnings);
    exit(0);
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

out('SUMMARY', 'Compatibilidad de esquema crítica OK. No se realizaron escrituras.');
exit(0);
