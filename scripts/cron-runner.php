#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Core\System\CronHealthCheckRunner;
use App\Core\System\HealthRepository;
use App\Core\System\HealthService;
use App\Core\System\LogRepository;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
$bootstrap = $root . '/bootstrap/app.php';

$options = getopt('', ['check', 'run:']);
$checkOnly = isset($options['check']);
$runJob = isset($options['run']) ? trim((string) $options['run']) : null;

if (($checkOnly && $runJob !== null) || (!$checkOnly && $runJob === null)) {
    fwrite(STDERR, "[FAIL] Uso: php scripts/cron-runner.php --check | --run=health-checks\n");
    exit(1);
}

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
    fwrite(STDERR, "[FAIL] Error crítico al cargar bootstrap.\n");
    exit(1);
}

fwrite(STDOUT, "[OK] Bootstrap cargado.\n");

if ($checkOnly) {
    fwrite(STDOUT, "[INFO] Modo check seguro: no se ejecutan jobs ni consultas DB.\n");
    fwrite(STDOUT, "[INFO] Job disponible: health-checks\n");
    fwrite(STDOUT, "[OK] Validación segura completada.\n");
    exit(0);
}

if ($runJob !== 'health-checks') {
    fwrite(STDERR, "[FAIL] Job desconocido. Permitido: health-checks\n");
    exit(1);
}

if (!isset($app['db']) || !is_callable($app['db'])) {
    fwrite(STDERR, "[FAIL] No existe fábrica DB en bootstrap.\n");
    exit(1);
}

try {
    $pdo = $app['db']();
    $runner = new CronHealthCheckRunner(
        new HealthService(
            new HealthRepository($pdo),
            new LogRepository($pdo),
            $pdo
        )
    );
    $summary = $runner->run();
} catch (Throwable $exception) {
    fwrite(STDERR, "[FAIL] Error crítico al ejecutar health-checks. Verifica DB/.env.\n");
    exit(1);
}

fwrite(STDOUT, "[OK] Job ejecutado: {$summary['job']}\n");
fwrite(STDOUT, "[OK] Checks encontrados: {$summary['checks_found']}\n");
fwrite(STDOUT, "[OK] Checks ejecutados: {$summary['checks_executed']}\n");
fwrite(STDOUT, "[OK] Exitosos: {$summary['success']} | Fallidos: {$summary['failed']} | Skipped: {$summary['skipped']}\n");
foreach ($summary['messages'] as $message) {
    fwrite(STDOUT, " - {$message}\n");
}

fwrite(STDOUT, "[OK] Ejecución de health-checks finalizada.\n");
exit(0);
