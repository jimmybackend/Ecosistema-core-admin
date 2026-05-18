#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$compatibilityScript = $root . '/scripts/schema-compatibility-check.php';

if (!is_file($compatibilityScript)) {
    fwrite(STDERR, "[FAIL] No existe scripts/schema-compatibility-check.php" . PHP_EOL);
    exit(1);
}

require $compatibilityScript;
