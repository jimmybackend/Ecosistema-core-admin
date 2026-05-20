#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Support\Env;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "vendor/autoload.php faltante. Ejecuta composer install.\n");
    exit(1);
}
require_once $autoload;
require_once $root . '/app/Support/Env.php';
Env::load($root . '/.env');

$envKey = trim((string) (Env::get('APP_KEY', '')));
$app = require $root . '/bootstrap/app.php';
$configKey = trim((string) (($app['config']['app']['key'] ?? '')));
$effectiveKey = $configKey !== '' ? $configKey : $envKey;
$format = 'unknown';
if ($effectiveKey === '') {
    $format = 'empty';
} elseif (str_starts_with($effectiveKey, 'base64:')) {
    $format = 'base64';
} elseif ($effectiveKey !== '') {
    $format = 'plain';
}

$out = [
    'ok' => $configKey !== '' || $envKey !== '',
    'env_app_key_present' => $envKey !== '',
    'env_app_key_length' => strlen($envKey),
    'config_app_key_present' => $configKey !== '',
    'config_app_key_length' => strlen($configKey),
    'app_key_format' => $format,
];

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
