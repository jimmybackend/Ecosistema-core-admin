#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$checkOnly = in_array('--check', $argv, true);

if (!$checkOnly) {
    fwrite(STDOUT, "[WARN] Modo informativo: usa --check para validación segura.\n");
}

$autoload = $root . '/vendor/autoload.php';
$bootstrap = $root . '/bootstrap/app.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "[FAIL] Falta vendor/autoload.php. Ejecuta composer install.\n");
    exit(1);
}

require_once $autoload;
fwrite(STDOUT, "[OK] Autoload cargado.\n");

if (!is_file($bootstrap)) {
    fwrite(STDERR, "[FAIL] Falta bootstrap/app.php.\n");
    exit(1);
}

try {
    $app = require $bootstrap;
    if (!is_array($app)) {
        fwrite(STDERR, "[FAIL] bootstrap/app.php no devolvió estructura válida.\n");
        exit(1);
    }
} catch (Throwable $exception) {
    fwrite(STDERR, '[FAIL] Error crítico al cargar bootstrap: ' . $exception->getMessage() . "\n");
    exit(1);
}

fwrite(STDOUT, "[OK] Bootstrap cargado.\n");
fwrite(STDOUT, "[INFO] Jobs futuros (pendiente/no implementado):\n");
$plannedJobs = [
    'system.health.checks',
    'sessions.cleanup.expired',
    'files.temp.cleanup.local',
    'mail.outgoing.process',
    'cloud.s3.sync',
    'system.logs.audit.maintenance',
    'onboarding.provisioning.run',
    'backups.verify',
];

foreach ($plannedJobs as $job) {
    fwrite(STDOUT, " - {$job}: pendiente/no implementado\n");
}

fwrite(STDOUT, "[OK] Validación segura completada. No se ejecutaron jobs reales.\n");
exit(0);
