#!/usr/bin/env php
<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$envPath = $root . '/.env';
$webGroup = getenv('WEB_GROUP') ?: 'www-data';

$dangerFlags = [
    'MAIL_SEND_ENABLED','MAIL_ALLOW_TEST_SEND','CLOUD_S3_ENABLED','CLOUD_ALLOW_UPLOADS','CLOUD_ALLOW_DOWNLOADS',
    'ECOSISTEMA_DRIVE_AWS_ENABLED','ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS','ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS',
    'ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS','ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS','ECOSISTEMA_AI_ENABLED',
    'ECOSISTEMA_AI_PROVIDER_ENABLED','ECOSISTEMA_AI_WRITE_PROPOSALS','ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED',
    'ECOSISTEMA_REPORT_EXPORT_WRITE','ECOSISTEMA_REPORT_EXPORT_INCLUDE_PII','CORE_REGISTRATION_ENABLED',
];

function parseEnvFile(string $path): array {
    $data = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $trim = trim($line);
        if ($trim === '' || str_starts_with($trim, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $data[trim($k)] = trim($v, " \"'");
    }
    return $data;
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

    $env = parseEnvFile($envPath);
    if (($env['APP_DEBUG'] ?? '') !== 'false') $errors[] = 'APP_DEBUG debe estar en false';
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
