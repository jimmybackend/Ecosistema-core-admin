#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$failures = 0;

function out(string $status, string $message): void
{
    fwrite(STDOUT, sprintf('[%s] %s%s', $status, $message, PHP_EOL));
}

function ok(string $message): void
{
    out('OK', $message);
}

function fail(string $message, int &$failures): void
{
    $failures++;
    out('FAIL', $message);
}

function ensureFile(string $root, string $relativePath, int &$failures): bool
{
    $path = $root . '/' . $relativePath;
    if (!is_file($path)) {
        fail("Falta archivo requerido: {$relativePath}", $failures);
        return false;
    }

    ok("Archivo presente: {$relativePath}");
    return true;
}

$autoloadOk = ensureFile($root, 'vendor/autoload.php', $failures);
if ($autoloadOk) {
    require_once $root . '/vendor/autoload.php';
    ok('Autoload cargado.');
}

$bootstrapOk = ensureFile($root, 'bootstrap/app.php', $failures);
if ($bootstrapOk) {
    $app = require $root . '/bootstrap/app.php';
    if (!is_array($app)) {
        fail('bootstrap/app.php no retornó estructura esperada.', $failures);
    } else {
        ok('bootstrap/app.php cargado correctamente.');
    }
}

ensureFile($root, '.env.example', $failures);
ensureFile($root, 'docs/ops/BACKUP_RESTORE_PLAN.md', $failures);

if (!is_dir($root . '/storage')) {
    fail('No existe directorio storage/.', $failures);
} else {
    ok('Directorio storage/ presente.');
}

$envExamplePath = $root . '/.env.example';
if (is_file($envExamplePath)) {
    $env = file_get_contents($envExamplePath);
    if ($env === false) {
        fail('No se pudo leer .env.example.', $failures);
    } else {
        $required = [
            'DB_HOST=',
            'DB_PORT=',
            'DB_DATABASE=',
            'DB_USERNAME=',
            'DB_PASSWORD=',
            'CLOUD_LOCAL_STORAGE_PATH=',
        ];

        foreach ($required as $key) {
            if (str_contains($env, $key)) {
                ok('Variable documentada en .env.example: ' . rtrim($key, '='));
            } else {
                fail('Falta variable esperada en .env.example: ' . rtrim($key, '='), $failures);
            }
        }
    }
}

if ($failures > 0) {
    out('SUMMARY', "backup-check finalizó con {$failures} falla(s).");
    exit(1);
}

out('SUMMARY', 'backup-check finalizó correctamente (modo no destructivo).');
exit(0);
