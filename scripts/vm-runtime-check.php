#!/usr/bin/env php
<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$envPath = $root . '/.env';
$webGroup = getenv('WEB_GROUP') ?: 'www-data';

$dangerFlags = [
    'ECOSISTEMA_DRIVE_AWS_ENABLED','ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS','ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS',
    'ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS','ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS','ECOSISTEMA_AI_ENABLED',
    'ECOSISTEMA_AI_PROVIDER_ENABLED','ECOSISTEMA_AI_WRITE_PROPOSALS','ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED',
    'ECOSISTEMA_REPORT_EXPORT_WRITE','ECOSISTEMA_REPORT_EXPORT_INCLUDE_PII','CORE_REGISTRATION_ENABLED',
];

$mailDangerFlags = ['MAIL_SEND_ENABLED', 'MAIL_ALLOW_TEST_SEND'];
$cloudDangerFlags = ['CLOUD_S3_ENABLED', 'CLOUD_ALLOW_UPLOADS', 'CLOUD_ALLOW_DOWNLOADS'];

function parseEnvFile(string $path): array {
    $data = [];
    $counts = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $trim = trim($line);
        if ($trim === '' || str_starts_with($trim, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $key = trim($k);
        $counts[$key] = ($counts[$key] ?? 0) + 1;
        $data[$key] = trim($v, " \"'");
    }
    return ['values' => $data, 'counts' => $counts];
}

$errors = [];
$warnings = [];
if (!is_file($envPath)) {
    $errors[] = '.env no existe';
} else {
    $perms = fileperms($envPath) & 0777;
    if (($perms & 0x004) !== 0) $errors[] = '.env es world-readable (others+r)';

    $groupInfo = function_exists('posix_getgrnam') ? posix_getgrnam($webGroup) : false;
    if ($groupInfo === false) {
        $warnings[] = "Grupo WEB_GROUP={$webGroup} no existe o POSIX no disponible";
    } else {
        $st = stat($envPath);
        if (($st['gid'] ?? -1) !== $groupInfo['gid']) $warnings[] = ".env no pertenece al grupo {$webGroup}";
        if (($perms & 0x020) === 0) $warnings[] = '.env sin lectura de grupo (g+r)';
    }

    $parsed = parseEnvFile($envPath);
    $env = $parsed['values'];
    $counts = $parsed['counts'];
    foreach (['CLOUD_DISK', 'CLOUD_S3_ENABLED', 'CLOUD_ALLOW_DOWNLOADS', 'CLOUD_ALLOW_UPLOADS', 'CLOUD_CONTROLLED_LIVE_TESTS'] as $duplicateKey) {
        if (($counts[$duplicateKey] ?? 0) > 1) {
            $warnings[] = "{$duplicateKey} duplicated";
        }
    }
    if (($env['APP_DEBUG'] ?? '') !== 'false') $errors[] = 'APP_DEBUG debe estar en false';

    foreach ($mailDangerFlags as $flag) {
        if (($env[$flag] ?? '') !== 'false') {
            $errors[] = "{$flag} debe estar en false";
        }
    }

    $cloudControlledLiveTests = ($env['CLOUD_CONTROLLED_LIVE_TESTS'] ?? 'false') === 'true'
        || ($env['VM_ALLOW_CONTROLLED_CLOUD'] ?? 'false') === 'true';

    if ($cloudControlledLiveTests) {
        echo "[OK] Cloud controlled live tests enabled by explicit flag\n";
    } else {
        foreach ($cloudDangerFlags as $flag) {
            if (($env[$flag] ?? '') !== 'false') {
                $errors[] = "{$flag} debe estar en false salvo CLOUD_CONTROLLED_LIVE_TESTS=true";
            }
        }
    }

    foreach ($dangerFlags as $flag) if (($env[$flag] ?? '') !== 'false') $errors[] = "{$flag} debe estar en false";
    if (($env['APP_URL'] ?? '') === '') $errors[] = 'APP_URL es obligatorio';
    if (($env['DB_HOST'] ?? '') === '') $errors[] = 'DB_HOST es obligatorio';
    if (($env['DB_DATABASE'] ?? '') === '') $errors[] = 'DB_DATABASE es obligatorio';

    if (trim((string) shell_exec('ps -eo comm | grep -E "^nginx$" | head -n 1')) === '') $warnings[] = 'Nginx no detectado por ps';
    if (trim((string) shell_exec('ps -eo comm | grep -E "php(-fpm|8\\.5-fpm)" | head -n 1')) === '') $warnings[] = 'PHP-FPM no detectado por ps';
}

echo "VM Runtime Check\n";
echo '- Archivo .env: ' . (is_file($envPath) ? 'OK' : 'MISSING') . "\n";
foreach ($warnings as $w) echo "[WARN] {$w}\n";
foreach ($errors as $e) echo "[FAIL] {$e}\n";
if ($errors) exit(1);
echo "[OK] Validaciones completadas sin errores críticos.\n";
