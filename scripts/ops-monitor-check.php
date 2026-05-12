#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$criticalFailures = 0;
$warnings = 0;

function reportStatus(string $status, string $message): void
{
    echo sprintf('[%s] %s%s', $status, $message, PHP_EOL);
}

function okStatus(string $message): void
{
    reportStatus('OK', $message);
}

function warnStatus(string $message, int &$warnings): void
{
    $warnings++;
    reportStatus('WARN', $message);
}

function failStatus(string $message, int &$criticalFailures): void
{
    $criticalFailures++;
    reportStatus('FAIL', $message);
}

$requiredFiles = [
    'vendor/autoload.php',
    'bootstrap/app.php',
    '.env.example',
    'docs/ops/MONITORING_OPERATIONS_PLAN.md',
    'scripts/cron-runner.php',
    'scripts/backup-check.php',
];

foreach ($requiredFiles as $relativePath) {
    $absolutePath = $root . '/' . $relativePath;
    if (is_file($absolutePath)) {
        okStatus('Existe archivo crítico: ' . $relativePath);
        continue;
    }

    failStatus('Falta archivo crítico: ' . $relativePath, $criticalFailures);
}

$storagePath = $root . '/storage';
if (is_dir($storagePath)) {
    okStatus('Existe directorio storage/.');

    if (is_writable($storagePath)) {
        okStatus('storage/ tiene permisos de escritura.');
    } else {
        warnStatus('storage/ existe pero no es escribible por el usuario actual.', $warnings);
    }

    $cloudPath = $storagePath . '/app/cloud';
    if (is_dir($cloudPath)) {
        okStatus('Existe storage/app/cloud.');

        if (is_writable($cloudPath)) {
            okStatus('storage/app/cloud tiene permisos de escritura.');
        } else {
            warnStatus('storage/app/cloud existe pero no es escribible por el usuario actual.', $warnings);
        }
    } else {
        warnStatus('No existe storage/app/cloud (puede ser válido si Cloud local no está habilitado).', $warnings);
    }
} else {
    warnStatus('No existe storage/ en esta instalación.', $warnings);
}

$freeSpace = @disk_free_space($root);
$totalSpace = @disk_total_space($root);
if ($freeSpace === false || $totalSpace === false || $totalSpace <= 0) {
    warnStatus('No fue posible calcular espacio de disco con funciones nativas PHP.', $warnings);
} else {
    $freeGb = round($freeSpace / 1024 / 1024 / 1024, 2);
    $totalGb = round($totalSpace / 1024 / 1024 / 1024, 2);
    $freePercent = round(($freeSpace / $totalSpace) * 100, 2);

    okStatus("Espacio libre aproximado: {$freeGb} GB de {$totalGb} GB ({$freePercent}%).");

    if ($freePercent < 10.0) {
        warnStatus('Espacio libre menor a 10%. Revisar capacidad y rotación de logs/backups.', $warnings);
    }
}

if ($criticalFailures > 0) {
    reportStatus('RESULT', "Falló ops monitor check: {$criticalFailures} crítico(s), {$warnings} advertencia(s).");
    exit(1);
}

reportStatus('RESULT', "Ops monitor check OK: 0 críticos, {$warnings} advertencia(s).");
exit(0);
