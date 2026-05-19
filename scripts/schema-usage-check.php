#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$compatibilityScript = $root . '/scripts/schema-compatibility-check.php';

if (!is_file($compatibilityScript)) {
    fwrite(STDERR, "[FAIL] No existe scripts/schema-compatibility-check.php" . PHP_EOL);
    exit(1);
}

$targets = [
    'app/Core/Mail',
    'routes/web.php',
    'resources/views/pages/mail',
    'docs',
    'scripts',
];
$forbiddenPatterns = [
    '/\bm\.address\b/',
    '/\bmail_mailboxes\.address\b/',
];

$errors = 0;
foreach ($targets as $target) {
    $path = $root . '/' . $target;
    if (!file_exists($path)) {
        continue;
    }

    if (is_file($path)) {
        $files = [$path];
    } else {
        $files = [];
        $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
        foreach ($iter as $fileInfo) {
            if ($fileInfo->isFile()) {
                $files[] = $fileInfo->getPathname();
            }
        }
    }

    foreach ($files as $file) {
        $content = @file_get_contents($file);
        if (!is_string($content) || $content === '') {
            continue;
        }
        foreach ($forbiddenPatterns as $pattern) {
            if (preg_match($pattern, $content) === 1) {
                $relative = ltrim(str_replace($root, '', $file), '/');
                fwrite(STDERR, "[FAIL] Referencia de columna no permitida detectada ({$pattern}) en {$relative}" . PHP_EOL);
                $errors++;
            }
        }
    }
}

if ($errors > 0) {
    exit(1);
}

echo "[OK] Uso de esquema mail_mailboxes validado (sin address, usando full_address)." . PHP_EOL;

require $compatibilityScript;
