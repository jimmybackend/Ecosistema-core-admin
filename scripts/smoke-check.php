#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$criticalFailures = 0;
$warnings = 0;

function report(string $status, string $message): void
{
    echo sprintf('[%s] %s%s', $status, $message, PHP_EOL);
}

function ok(string $message): void
{
    report('OK', $message);
}

function fail(string $message, int &$criticalFailures): void
{
    $criticalFailures++;
    report('FAIL', $message);
}

function warn(string $message, int &$warnings): void
{
    $warnings++;
    report('WARN', $message);
}

function checkFile(string $root, string $relativePath, int &$criticalFailures): bool
{
    $absolutePath = $root . '/' . $relativePath;
    if (!is_file($absolutePath)) {
        fail("No existe archivo requerido: {$relativePath}", $criticalFailures);
        return false;
    }

    ok("Existe archivo requerido: {$relativePath}");
    return true;
}

$autoloadPath = $root . '/vendor/autoload.php';
if (!is_file($autoloadPath)) {
    fail('No existe vendor/autoload.php. Ejecuta: composer install', $criticalFailures);
} else {
    require_once $autoloadPath;
    ok('Autoload disponible: vendor/autoload.php');
}

$requiredFiles = [
    'bootstrap/app.php',
    'routes/web.php',
    'public/index.php',
    '.env.example',
    'public/assets/css/ecosistema-ui.css',
    'README.md',
    'config/mail.php',
    'config/cloud.php',
    'app/Core/Cloud/S3DriveIntegrationConfig.php',
    'app/Core/Cloud/EcosistemaDriveFileRepository.php',
    'app/Core/Cloud/EcosistemaDriveFileService.php',
    'resources/views/pages/cloud/drive-folders.php',
    'app/Core/Cloud/EcosistemaDriveFolderService.php',
    'app/Core/Cloud/EcosistemaDriveFolderRepository.php',
    'app/Core/Cloud/EcosistemaDriveRootRepository.php',
    'app/Core/Cloud/EcosistemaDriveRootService.php',
    'resources/views/pages/cloud/drive-buckets.php',
    'app/Core/Cloud/EcosistemaDriveBucketService.php',
    'app/Core/Cloud/EcosistemaDriveBucketRepository.php',
    'app/Core/Cloud/EcosistemaDriveSummaryService.php',
    'app/Core/Cloud/EcosistemaDriveAuditLogger.php',
    'docs/project/ECOSISTEMA_DRIVE_ACCESS_POLICY.md',
    'docs/project/ECOSISTEMA_DRIVE_READ_ONLY_AUDIT.md',
    'docs/project/ECOSISTEMA_DRIVE_DOWNLOAD_CONTRACT.md',
    'resources/views/pages/cloud/drive-download-contract.php',
    'app/Core/Cloud/EcosistemaDriveDownloadContract.php',
    'resources/views/pages/cloud/drive-access.php',
    'app/Core/Cloud/EcosistemaDriveAccessPolicy.php',
    'resources/views/pages/cloud/drive-root.php',
    'resources/views/pages/cloud/drive-summary.php',
    'docs/project/S3_DRIVE_SHARED_CONFIGURATION.md',
    'docs/project/ECOSISTEMA_DRIVE_CONFIGURATION.md',
    'config/s3_drive.php',
    'config/ecosistema_drive.php',
    'resources/views/pages/mail/settings.php',
    'resources/views/pages/cloud/settings.php',
    'resources/views/pages/cloud/drive-files.php',
    'resources/views/pages/cloud/drive-file-detail.php',
    'resources/views/pages/cloud/drive-s3-key-validation.php',
    'app/Core/Cloud/EcosistemaDriveS3KeyValidator.php',
    'docs/project/ECOSISTEMA_DRIVE_SIGNED_URL_DRY_RUN.md',
    'resources/views/pages/cloud/drive-signed-url-dry-run.php',
    'app/Core/Cloud/EcosistemaDriveSignedUrlDryRunService.php',
    'app/Core/Cloud/EcosistemaDriveSignedUrlDryRun.php',
    'docs/project/ECOSISTEMA_DRIVE_AWS_S3_CONFIG.md',
    'resources/views/pages/cloud/drive-aws-config.php',
    'app/Core/Cloud/EcosistemaDriveAwsS3Config.php',
    'docs/project/ECOSISTEMA_DRIVE_CONTROLLED_S3_DOWNLOAD.md',
    'docs/project/ECOSISTEMA_DRIVE_S3_UPLOAD_DRY_RUN.md',
    'resources/views/pages/cloud/drive-upload-dry-run.php',
    'app/Core/Cloud/EcosistemaDriveS3UploadDryRunService.php',
    'app/Core/Cloud/EcosistemaDriveS3UploadDryRun.php',
    'app/Core/Cloud/EcosistemaDriveS3UploadService.php',
    'resources/views/pages/cloud/drive-upload.php',
    'resources/views/pages/cloud/drive-upload-result.php',
    'docs/project/ECOSISTEMA_DRIVE_CONTROLLED_S3_UPLOAD.md',
    'resources/views/pages/cloud/drive-download-blocked.php',
    'app/Core/Cloud/EcosistemaDriveShareContract.php',
    'app/Core/Cloud/EcosistemaDriveShareContractService.php',
    'resources/views/pages/cloud/drive-share-contract.php',
    'docs/project/ECOSISTEMA_DRIVE_SHARE_CONTRACT.md',
    'app/Core/Cloud/EcosistemaDriveS3DownloadService.php',
    'docs/project/ECOSISTEMA_DRIVE_S3_KEY_VALIDATION.md',
    'resources/views/pages/cloud/drive-folder-detail.php',
    'resources/views/pages/cloud/drive-browse.php',
    'app/Core/Cloud/CloudStorageService.php',
    'app/Core/Cloud/CloudUploadService.php',
    'resources/views/pages/cloud/upload.php',
    'app/Core/Cloud/CloudStorageConfig.php',
    'resources/views/pages/mail/show.php',
    'resources/views/pages/mail/send-preview.php',
    'resources/views/pages/mail/attachments.php',
    'app/Core/Mail/MailSendService.php',
    'app/Core/Mail/MailSender.php',
    'app/Core/Mail/SmtpMailer.php',
    'app/Core/Mail/MailAttachmentRepository.php',
    'app/Core/Mail/MailAttachmentService.php',
    'app/Core/Mail/MailOutgoingAttachmentService.php',
    'resources/views/pages/auth/login.php',
    'resources/views/pages/auth/register.php',
    'resources/views/pages/dashboard.php',
    'resources/views/pages/users/index.php',
    'resources/views/pages/users/roles.php',
    'resources/views/pages/roles/index.php',
    'resources/views/pages/permissions/index.php',
    'resources/views/pages/modules/index.php',
    'resources/views/pages/system/audit.php',
    'app/Http/Response/ErrorResponder.php',
    'resources/views/pages/errors/403.php',
    'resources/views/pages/errors/404.php',
    'resources/views/pages/errors/419.php',
    'resources/views/pages/errors/500.php',
    'docs/ops/WORKERS_CRON_PLAN.md',
    'docs/ops/BACKUP_RESTORE_PLAN.md',
    'docs/ops/MONITORING_OPERATIONS_PLAN.md',
    'docs/project/CORE_ADMIN_OPERATIONAL_CLOSURE.md',
    'docs/project/CORE_ADMIN_S3_DRIVE_INTEGRATION_CONTRACT.md',
    'docs/project/S3_DRIVE_TECHNICAL_INVENTORY.md',
    'docs/project/CLOUD_S3_DATABASE_MAPPING.md',
    'docs/auth/CONTROLLED_INITIAL_REGISTRATION.md',
    'scripts/backup-check.php',
    'scripts/cron-runner.php',
    'scripts/ops-monitor-check.php',
    'app/Core/Onboarding/OnboardingRunner.php',
    'app/Core/Onboarding/OnboardingStepExecutor.php',
    'resources/views/pages/onboarding/show-run.php',
];

foreach ($requiredFiles as $requiredFile) {
    checkFile($root, $requiredFile, $criticalFailures);
}

if (is_file($root . '/bootstrap/app.php')) {
    try {
        $app = require $root . '/bootstrap/app.php';
        if (!is_array($app)) {
            fail('bootstrap/app.php no retornó una estructura válida.', $criticalFailures);
        } else {
            ok('bootstrap/app.php carga correctamente sin error fatal.');
        }
    } catch (Throwable $exception) {
        fail('Error al cargar bootstrap/app.php: ' . $exception->getMessage(), $criticalFailures);
    }
}


$envExample = $root . '/.env.example';
if (is_file($envExample)) {
    $envContent = file_get_contents($envExample);
    if ($envContent !== false && str_contains($envContent, 'SESSION_IDLE_TIMEOUT=')) {
        ok('.env.example contiene SESSION_IDLE_TIMEOUT.');
    } else {
        fail('.env.example no contiene SESSION_IDLE_TIMEOUT.', $criticalFailures);
    }

    $requiredEnvKeys = ['APP_DEBUG=', 'SESSION_SECURE=', 'DB_DATABASE=', 'MAIL_HOST=', 'MAIL_SEND_ENABLED=', 'MAIL_ALLOW_TEST_SEND=', 'AWS_BUCKET=', 'CLOUD_S3_ENABLED=', 'CLOUD_ALLOW_DOWNLOADS=', 'CLOUD_ALLOW_UPLOADS=', 'CLOUD_MAX_UPLOAD_MB=', 'CLOUD_ALLOWED_EXTENSIONS=', 'MAIL_MAX_ATTACHMENTS=', 'MAIL_MAX_ATTACHMENT_MB=', 'MAIL_MAX_TOTAL_ATTACHMENT_MB=', 'S3_DRIVE_ENABLED=', 'S3_DRIVE_MODE=', 'S3_DRIVE_BASE_URL=', 'S3_DRIVE_API_TIMEOUT=', 'S3_DRIVE_ALLOW_REMOTE_CALLS=', 'S3_DRIVE_ALLOW_SIGNED_URLS=', 'S3_DRIVE_ALLOW_REMOTE_UPLOADS=', 'S3_DRIVE_ALLOW_REMOTE_DOWNLOADS=', 'ECOSISTEMA_DRIVE_ENABLED=', 'ECOSISTEMA_DRIVE_MODE=', 'ECOSISTEMA_DRIVE_REFERENCE_REPO=', 'ECOSISTEMA_DRIVE_AWS_ENABLED=', 'ECOSISTEMA_DRIVE_AWS_REGION=', 'ECOSISTEMA_DRIVE_AWS_BUCKET=', 'ECOSISTEMA_DRIVE_AWS_ENDPOINT=', 'ECOSISTEMA_DRIVE_AWS_ACCESS_KEY_ID=', 'ECOSISTEMA_DRIVE_AWS_SECRET_ACCESS_KEY=', 'ECOSISTEMA_DRIVE_AWS_SESSION_TOKEN=', 'ECOSISTEMA_DRIVE_API_TIMEOUT=', 'ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=', 'ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS=', 'ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS=', 'ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS=', 'CORE_REGISTRATION_ENABLED=', 'CORE_REGISTRATION_MODE=', 'CORE_REGISTRATION_INVITE_CODE=', 'CORE_REGISTRATION_DEFAULT_TENANT_ID=', 'CORE_REGISTRATION_DEFAULT_ROLE_ID='];
    foreach ($requiredEnvKeys as $requiredEnvKey) {
        if ($envContent !== false && str_contains($envContent, $requiredEnvKey)) {
            ok('.env.example contiene variable clave: ' . rtrim($requiredEnvKey, '='));
        } else {
            fail('.env.example no contiene variable clave: ' . rtrim($requiredEnvKey, '='), $criticalFailures);
        }
    }
}


    $requiredDisabled = ['ECOSISTEMA_DRIVE_AWS_ENABLED=false', 'ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS=false', 'ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=false'];
    foreach ($requiredDisabled as $disabledFlag) {
        if ($envContent !== false && str_contains($envContent, $disabledFlag)) {
            ok('.env.example mantiene apagado por defecto: ' . $disabledFlag);
        } else {
            fail('.env.example no mantiene apagado por defecto: ' . $disabledFlag, $criticalFailures);
        }
    }


$vmRequiredFiles = [
    '.env.vm.example',
    'scripts/setup-vm-env.sh',
    'docs/deploy/VM_ENV_SETUP.md',
];

foreach ($vmRequiredFiles as $vmRequiredFile) {
    checkFile($root, $vmRequiredFile, $criticalFailures);
}

$gitignorePath = $root . '/.gitignore';
if (is_file($gitignorePath)) {
    $gitignoreContent = file_get_contents($gitignorePath);
    if ($gitignoreContent !== false && preg_match('/^\/\.env$/m', $gitignoreContent)) {
        ok('.gitignore ignora /.env.');
    } else {
        fail('.gitignore no ignora /.env.', $criticalFailures);
    }
}

$vmEnvExamplePath = $root . '/.env.vm.example';
if (is_file($vmEnvExamplePath)) {
    $vmEnvContent = file_get_contents($vmEnvExamplePath);

    if ($vmEnvContent !== false && str_contains($vmEnvContent, 'DB_PASSWORD=CAMBIAR_EN_VM_NO_COMMIT')) {
        ok('.env.vm.example contiene placeholder seguro para DB_PASSWORD.');
    } else {
        fail('.env.vm.example no contiene DB_PASSWORD=CAMBIAR_EN_VM_NO_COMMIT.', $criticalFailures);
    }

    if ($vmEnvContent !== false && str_contains($vmEnvContent, 'CORE_REGISTRATION_INVITE_CODE=CAMBIAR_EN_VM_NO_COMMIT')) {
        ok('.env.vm.example contiene placeholder seguro para CORE_REGISTRATION_INVITE_CODE.');
    } else {
        fail('.env.vm.example no contiene CORE_REGISTRATION_INVITE_CODE=CAMBIAR_EN_VM_NO_COMMIT.', $criticalFailures);
    }

    if ($vmEnvContent !== false && !preg_match('/AWS_ACCESS_KEY_ID=(?!change-me$).+/m', $vmEnvContent)) {
        ok('.env.vm.example no contiene AWS_ACCESS_KEY_ID real.');
    } else {
        fail('.env.vm.example contiene AWS_ACCESS_KEY_ID potencialmente real.', $criticalFailures);
    }

    if ($vmEnvContent !== false && !preg_match('/AWS_SECRET_ACCESS_KEY=(?!change-me$).+/m', $vmEnvContent)) {
        ok('.env.vm.example no contiene AWS_SECRET_ACCESS_KEY real.');
    } else {
        fail('.env.vm.example contiene AWS_SECRET_ACCESS_KEY potencialmente real.', $criticalFailures);
    }
}

$setupScriptPath = $root . '/scripts/setup-vm-env.sh';
if (is_file($setupScriptPath)) {
    $setupScriptContent = file_get_contents($setupScriptPath);

    if ($setupScriptContent !== false && str_contains($setupScriptContent, 'read -r -s -p')) {
        ok('setup-vm-env.sh usa read -s para secretos.');
    } else {
        fail('setup-vm-env.sh no usa read -s para secretos.', $criticalFailures);
    }

    if ($setupScriptContent !== false && str_contains($setupScriptContent, '.bak.')) {
        ok('setup-vm-env.sh crea backup antes de modificar .env existente.');
    } else {
        fail('setup-vm-env.sh no crea backup antes de modificar .env existente.', $criticalFailures);
    }

    if ($setupScriptContent !== false && str_contains($setupScriptContent, 'if grep -Eq "^[[:space:]]*${key}=" "${file}"; then')) {
        ok('setup-vm-env.sh conserva variables desconocidas al actualizar solo claves gestionadas.');
    } else {
        fail('setup-vm-env.sh no demuestra estrategia de preservación de variables desconocidas.', $criticalFailures);
    }

    if ($setupScriptContent !== false && str_contains($setupScriptContent, 'sed -i -E "s|^[[:space:]]*${key}=.*$|${key}=${escaped_value}|" "${file}"')) {
        ok('setup-vm-env.sh actualiza líneas puntuales y no borra comentarios existentes.');
    } else {
        fail('setup-vm-env.sh no demuestra actualización puntual de líneas para preservar comentarios.', $criticalFailures);
    }

    if ($setupScriptContent !== false && !str_contains($setupScriptContent, 'echo "DB_PASSWORD') && !str_contains($setupScriptContent, 'echo "CORE_REGISTRATION_INVITE_CODE')) {
        ok('setup-vm-env.sh no imprime secretos en consola.');
    } else {
        fail('setup-vm-env.sh imprime secretos en consola.', $criticalFailures);
    }
}


$deployChecklistPaths = [
    'docs/deploy/EC2_PRODUCTION_CHECKLIST.md',
    'docs/project/ECOSISTEMA_CORE_ADMIN_DEPLOY_EC2.md',
];
$deployChecklistFound = false;
foreach ($deployChecklistPaths as $deployChecklistPath) {
    if (is_file($root . '/' . $deployChecklistPath)) {
        $deployChecklistFound = true;
        ok('Existe checklist de despliegue: ' . $deployChecklistPath);
        break;
    }
}

if (!$deployChecklistFound) {
    fail('No se encontró checklist de despliegue EC2/producción en docs/deploy o docs/project.', $criticalFailures);
}


$vmRunbookPath = 'docs/deploy/CORE_ADMIN_VM_RUNBOOK.md';
if (is_file($root . '/' . $vmRunbookPath)) {
    ok('Existe runbook VM de Core Admin: ' . $vmRunbookPath);
} else {
    fail('No se encontró runbook VM de Core Admin en docs/deploy/CORE_ADMIN_VM_RUNBOOK.md.', $criticalFailures);
}


$routesFile = $root . '/routes/web.php';
if (is_file($routesFile)) {
    $routesContent = file_get_contents($routesFile);
    $adapterFile = $root . '/app/Core/Cloud/EcosistemaDriveAdapter.php';
    $adapterContent = is_file($adapterFile) ? file_get_contents($adapterFile) : false;
    if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive")) {
        ok('routes/web.php contiene ruta GET /cloud/drive para estado de Ecosistema Drive.');
    } else {
        fail('No se encontró ruta GET /cloud/drive en routes/web.php.', $criticalFailures);
    }
    if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive/folders")) {
        ok('routes/web.php contiene ruta GET /cloud/drive/folders para metadata read-only de carpetas.');
    } else {
        fail('No se encontró ruta GET /cloud/drive/folders en routes/web.php.', $criticalFailures);
    }
    if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive/files")) {
        ok('routes/web.php contiene ruta GET /cloud/drive/files para metadata read-only de Drive.');
    } else {
        fail('No se encontró ruta GET /cloud/drive/files en routes/web.php.', $criticalFailures);
    }


    if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive/files/{id}/share-contract")) {
        ok('routes/web.php contiene ruta GET /cloud/drive/files/{id}/share-contract.');
    } else {
        fail('No se encontró ruta GET /cloud/drive/files/{id}/share-contract en routes/web.php.', $criticalFailures);
    }

    if ($adapterContent !== false && str_contains($adapterContent, "'share_contract' => [")) {
        ok('EcosistemaDriveAdapter contiene capability share_contract.');
    } else {
        fail('EcosistemaDriveAdapter no contiene capability share_contract.', $criticalFailures);
    }

    foreach (["'public_links' => false", "'share_tokens' => false", "'db_writes' => false"] as $expectedDisabled) {
        if ($adapterContent !== false && str_contains($adapterContent, $expectedDisabled)) {
            ok('EcosistemaDriveAdapter mantiene deshabilitado: ' . $expectedDisabled);
        } else {
            fail('EcosistemaDriveAdapter no mantiene deshabilitado: ' . $expectedDisabled, $criticalFailures);
        }
    }

    $shareContractViewPath = $root . '/resources/views/pages/cloud/drive-share-contract.php';
    $shareContractView = is_file($shareContractViewPath) ? file_get_contents($shareContractViewPath) : false;
    foreach (['s3_key', 'token', 'signed URL'] as $forbiddenText) {
        if ($shareContractView !== false && stripos($shareContractView, $forbiddenText) !== false) {
            warn('La vista share-contract contiene referencia textual: ' . $forbiddenText, $warnings);
        } else {
            ok('La vista share-contract no imprime: ' . $forbiddenText);
        }
    }
    if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive/files/{id}/download")) {
        ok('routes/web.php contiene ruta GET /cloud/drive/files/{id}/download para descarga controlada.');
    } else {
        fail('No se encontró ruta GET /cloud/drive/files/{id}/download en routes/web.php.', $criticalFailures);
    }

    if ($adapterContent !== false && str_contains($adapterContent, "controlled_download")) {
        ok('EcosistemaDriveAdapter contiene capability controlled_download.');
    } else {
        fail('EcosistemaDriveAdapter no contiene capability controlled_download.', $criticalFailures);
    }

    if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive/files/{id}")) {
        ok('routes/web.php contiene ruta GET /cloud/drive/files/{id} para detalle read-only de Drive.');
    } else {
        fail('No se encontró ruta GET /cloud/drive/files/{id} en routes/web.php.', $criticalFailures);
    }
    if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive/folders/{id}")) {
        ok('routes/web.php contiene ruta GET /cloud/drive/folders/{id} para detalle read-only de carpetas.');
    } else {
        fail('No se encontró ruta GET /cloud/drive/folders/{id} en routes/web.php.', $criticalFailures);
    }


    if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive/root")) {
        ok('routes/web.php contiene ruta GET /cloud/drive/root para resumen read-only de raíz de usuario.');
    } else {
        fail('No se encontró ruta GET /cloud/drive/root en routes/web.php.', $criticalFailures);
    }


    if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive/buckets")) {
        ok('routes/web.php contiene ruta GET /cloud/drive/buckets para metadata read-only de buckets.');
    } else {
        fail('No se encontró ruta GET /cloud/drive/buckets en routes/web.php.', $criticalFailures);
    }

    if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive/summary")) {
        ok('routes/web.php contiene ruta GET /cloud/drive/summary para resumen operativo read-only.');
    } else {
        fail('No se encontró ruta GET /cloud/drive/summary en routes/web.php.', $criticalFailures);
    }



    if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive/aws-config")) {
        ok('routes/web.php contiene ruta GET /cloud/drive/aws-config informativa.');
    } else {
        fail('No se encontró ruta GET /cloud/drive/aws-config en routes/web.php.', $criticalFailures);
    }

    if ($routesContent !== false && str_contains($routesContent, 'AuthSession::setAuth($auth)')) {
        ok('POST /login mantiene AuthSession::setAuth($auth) antes de redirigir.');
    } else {
        fail('No se encontró AuthSession::setAuth($auth) en POST /login.', $criticalFailures);
    }

    if ($routesContent !== false && str_contains($routesContent, 'if (!AuthSession::enforceIdleTimeout') && str_contains($routesContent, "header('Location: /login');")) {
        ok('startAuthSession redirige a /login cuando expira idle timeout.');
    } else {
        fail('No se encontró redirección a /login al expirar idle timeout.', $criticalFailures);
    }

    $invalidAuthDestroyPattern = "/if\\s*\\(\\s*AuthSession::isAuthenticated\\(\\)\\s*\\)\\s*\\{[\\s\\S]{0,600}?AuthSession::destroy\\(\\);/m";
    if ($routesContent !== false && preg_match($invalidAuthDestroyPattern, $routesContent) === 1) {
        fail('Se detectó destrucción de sesión sólo por AuthSession::isAuthenticated().', $criticalFailures);
    } else {
        ok('No se detecta destrucción de sesión por AuthSession::isAuthenticated().');
    }

    if ($routesContent !== false && str_contains($routesContent, 'AuthSession::start(')) {
        ok('routes/web.php mantiene AuthSession::start().');
    } else {
        fail('routes/web.php no contiene AuthSession::start().', $criticalFailures);
    }

    if ($routesContent !== false && str_contains($routesContent, 'ensureValidCsrfToken')) {
        ok('routes/web.php mantiene validación CSRF.');
    } else {
        fail('routes/web.php no contiene validación CSRF.', $criticalFailures);
    }

    if ($routesContent !== false && str_contains($routesContent, 'new AuthService') && str_contains($routesContent, 'new SessionRepository')) {
        ok('routes/web.php mantiene integración AuthService/SessionRepository.');
    } else {
        fail('routes/web.php no mantiene integración AuthService/SessionRepository.', $criticalFailures);
    }

    if ($adapterContent !== false && str_contains($adapterContent, "'aws_s3_config_prepared' => true")) { ok('EcosistemaDriveAdapter marca aws_s3_config_prepared=true.'); } else { fail('EcosistemaDriveAdapter no marca aws_s3_config_prepared=true.', $criticalFailures); }
    if ($adapterContent !== false && str_contains($adapterContent, "'aws_connection' => false")) { ok('EcosistemaDriveAdapter mantiene aws_connection=false.'); } else { fail('EcosistemaDriveAdapter no mantiene aws_connection=false.', $criticalFailures); }
    if ($adapterContent !== false && str_contains($adapterContent, "'signed_urls' => false")) { ok('EcosistemaDriveAdapter mantiene signed_urls=false.'); } else { fail('EcosistemaDriveAdapter no mantiene signed_urls=false.', $criticalFailures); }
    if ($adapterContent !== false && str_contains($adapterContent, "'remote_downloads' => false")) { ok('EcosistemaDriveAdapter mantiene remote_downloads=false.'); } else { fail('EcosistemaDriveAdapter no mantiene remote_downloads=false.', $criticalFailures); }
    if ($adapterContent !== false && str_contains($adapterContent, "'remote_uploads' => false")) { ok('EcosistemaDriveAdapter mantiene remote_uploads=false.'); } else { fail('EcosistemaDriveAdapter no mantiene remote_uploads=false.', $criticalFailures); }


    if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive/upload-dry-run")) {
        ok('routes/web.php contiene ruta GET /cloud/drive/upload-dry-run informativa.');
    } else {
        fail('No se encontró ruta GET /cloud/drive/upload-dry-run en routes/web.php.', $criticalFailures);
    }

    if ($adapterContent !== false && str_contains($adapterContent, "'upload_dry_run' => true")) { ok('EcosistemaDriveAdapter mantiene upload_dry_run=true.'); } else { fail('EcosistemaDriveAdapter no mantiene upload_dry_run=true.', $criticalFailures); }
    if ($adapterContent !== false && str_contains($adapterContent, "'storage_writes' => false")) { ok('EcosistemaDriveAdapter mantiene storage_writes=false.'); } else { fail('EcosistemaDriveAdapter no mantiene storage_writes=false.', $criticalFailures); }

    if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive/download-contract")) {
        ok('routes/web.php contiene ruta GET /cloud/drive/download-contract para contrato informativo de descarga futura.');
    } else {
        fail('No se encontró ruta GET /cloud/drive/download-contract en routes/web.php.', $criticalFailures);
    }

    if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive/access")) {
        ok('routes/web.php contiene ruta GET /cloud/drive/access para política read-only de acceso Drive.');
    } else {
        fail('No se encontró ruta GET /cloud/drive/access en routes/web.php.', $criticalFailures);
    }


    if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive/files/{id}/signed-url-dry-run")) {
        ok('routes/web.php contiene ruta GET /cloud/drive/files/{id}/signed-url-dry-run.');
    } else {
        fail('No se encontró ruta GET /cloud/drive/files/{id}/signed-url-dry-run en routes/web.php.', $criticalFailures);
    }

    if ($adapterContent !== false && str_contains($adapterContent, "'signed_url_dry_run'")) {
        ok('EcosistemaDriveAdapter contiene capability signed_url_dry_run.');
    } else {
        fail('No se encontró capability signed_url_dry_run en EcosistemaDriveAdapter.', $criticalFailures);
    }

    if ($adapterContent !== false && str_contains($adapterContent, "'signed_urls' => [") && str_contains($adapterContent, "'enabled' => false")) {
        ok('EcosistemaDriveAdapter mantiene signed_urls=false.');
    } else {
        fail('EcosistemaDriveAdapter no mantiene signed_urls=false.', $criticalFailures);
    }

    if ($adapterContent !== false && str_contains($adapterContent, "'aws_connected' => false")) {
        ok('EcosistemaDriveAdapter mantiene aws_connection/aws_connected en false.');
    } else {
        fail('EcosistemaDriveAdapter no mantiene aws_connection/aws_connected en false.', $criticalFailures);
    }

    $signedViewFile = $root . '/resources/views/pages/cloud/drive-signed-url-dry-run.php';
    $signedViewContent = is_file($signedViewFile) ? file_get_contents($signedViewFile) : false;
    if ($signedViewContent !== false && !str_contains($signedViewContent, "s3_key']")) {
        ok('La vista signed-url dry-run no imprime s3_key.');
    } else {
        fail('La vista signed-url dry-run imprime o podría imprimir s3_key.', $criticalFailures);
    }

    if ($signedViewContent !== false && !str_contains(strtolower($signedViewContent), 'http://') && !str_contains(strtolower($signedViewContent), 'https://')) {
        ok('La vista signed-url dry-run no imprime URLs firmadas reales.');
    } else {
        fail('La vista signed-url dry-run parece incluir URLs reales.', $criticalFailures);
    }

    $projectFiles = [
        $root . '/app/Core/Cloud/EcosistemaDriveSignedUrlDryRun.php',
        $root . '/app/Core/Cloud/EcosistemaDriveSignedUrlDryRunService.php',
    ];
    $awsSdkDetected = false;
    foreach ($projectFiles as $projectFile) {
        if (!is_file($projectFile)) { continue; }
        $content = (string)file_get_contents($projectFile);
        if (str_contains($content, 'Aws\S3\S3Client') || str_contains($content, 'aws/aws-sdk-php')) {
            $awsSdkDetected = true;
            break;
        }
    }
    if (!$awsSdkDetected) {
        ok('No aparece AWS SDK nuevo ni llamadas reales a S3 en artefactos signed-url dry-run.');
    } else {
        fail('Se detectó AWS SDK o llamadas reales a S3 en artefactos signed-url dry-run.', $criticalFailures);
    }

    if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive/browse")) {
        ok('routes/web.php contiene ruta GET /cloud/drive/browse para navegación read-only de carpetas/archivos.');
    } else {
        fail('No se encontró ruta GET /cloud/drive/browse en routes/web.php.', $criticalFailures);
    }
    $adapterFile = $root . '/app/Core/Cloud/EcosistemaDriveAdapter.php';
    $adapterContent = is_file($adapterFile) ? file_get_contents($adapterFile) : false;
    if ($adapterContent !== false && str_contains($adapterContent, "'download_contract'")) {
        ok('EcosistemaDriveAdapter declara capability download_contract en capacidades read-only.');
    } else {
        fail('No se encontró capability download_contract en EcosistemaDriveAdapter.', $criticalFailures);
    }

    $downloadContractRefOk = true;
    foreach (['README.md','docs/project/ECOSISTEMA_DRIVE_CONFIGURATION.md','docs/project/ECOSISTEMA_DRIVE_ACCESS_POLICY.md','docs/project/ECOSISTEMA_DRIVE_READ_ONLY_AUDIT.md','docs/project/ECOSISTEMA_DRIVE_DRY_RUN_ADAPTER.md'] as $docFile) {
        $docPath = $root . '/' . $docFile;
        $docContent = is_file($docPath) ? file_get_contents($docPath) : false;
        if ($docContent === false || !str_contains($docContent, 'ECOSISTEMA_DRIVE_DOWNLOAD_CONTRACT.md')) {
            $downloadContractRefOk = false;
            fail('No se encontró referencia al contrato de descarga en ' . $docFile . '.', $criticalFailures);
        }
    }
    if ($downloadContractRefOk) {
        ok('README y documentación Drive referencian el contrato de descarga futura.');
    }

    if ($routesContent !== false && str_contains($routesContent, "GET /register")) {
        ok('routes/web.php contiene ruta GET /register para registro inicial controlado.');
    } else {
        fail('No se encontró ruta GET /register en routes/web.php.', $criticalFailures);
    }
    

$loginViewFile = $root . '/resources/views/pages/auth/login.php';
if (is_file($loginViewFile)) {
    $loginViewContent = file_get_contents($loginViewFile);
    if ($loginViewContent !== false && str_contains($loginViewContent, '/register') && str_contains($loginViewContent, 'Crear cuenta inicial')) {
        ok('login.php contiene acceso visible a /register para onboarding inicial controlado.');
    } else {
        fail('login.php no contiene acceso visible a /register para onboarding inicial controlado.', $criticalFailures);
    }
}

$registerViewFile = $root . '/resources/views/pages/auth/register.php';
if (is_file($registerViewFile)) {
    $registerViewContent = file_get_contents($registerViewFile);
    if ($registerViewContent !== false && str_contains($registerViewContent, 'El registro inicial está deshabilitado por configuración.')) {
        ok('register.php contiene mensaje seguro para registro deshabilitado.');
    } else {
        fail('register.php no contiene mensaje seguro para registro deshabilitado.', $criticalFailures);
    }
}

if (is_file($envExample)) {
    $envContent = file_get_contents($envExample);
    if ($envContent !== false && str_contains($envContent, 'CORE_REGISTRATION_ENABLED=false')) {
        ok('.env.example mantiene CORE_REGISTRATION_ENABLED=false por defecto.');
    } else {
        fail('.env.example no mantiene CORE_REGISTRATION_ENABLED=false por defecto.', $criticalFailures);
    }
}

$registrationDoc = $root . '/docs/auth/CONTROLLED_INITIAL_REGISTRATION.md';
if (is_file($registrationDoc)) {
    $registrationDocContent = file_get_contents($registrationDoc);
    if ($registrationDocContent !== false && str_contains($registrationDocContent, 'Después apagar registro') && str_contains($registrationDocContent, 'CORE_REGISTRATION_ENABLED=false')) {
        ok('La documentación de registro inicial indica apagar el registro después de crear usuario.');
    } else {
        fail('La documentación de registro inicial no indica claramente apagar el registro después de crear usuario.', $criticalFailures);
    }
}
if ($routesContent !== false && str_contains($routesContent, "POST /register")) {
        ok('routes/web.php contiene ruta POST /register para registro inicial controlado.');
    } else {
        fail('No se encontró ruta POST /register en routes/web.php.', $criticalFailures);
    }

    if ($routesContent !== false && str_contains($routesContent, "GET /cloud/files/{id}/download")) {
        ok('routes/web.php contiene ruta de descarga cloud controlada.');
    } else {
        fail('No se encontró ruta GET /cloud/files/{id}/download en routes/web.php.', $criticalFailures);
    }
}


    if ($routesContent !== false && str_contains($routesContent, "GET /mail/messages/{id}/send-preview")) {
        ok('routes/web.php contiene ruta GET /mail/messages/{id}/send-preview.');
    } else {
        fail('No se encontró ruta GET /mail/messages/{id}/send-preview en routes/web.php.', $criticalFailures);
    }

    if ($routesContent !== false && str_contains($routesContent, "GET /mail/messages/{id}/attachments")) {
        ok('routes/web.php contiene ruta GET /mail/messages/{id}/attachments.');
    } else {
        fail('No se encontró ruta GET /mail/messages/{id}/attachments en routes/web.php.', $criticalFailures);
    }

    if ($routesContent !== false && str_contains($routesContent, "POST /mail/messages/{id}/attachments")) {
        ok('routes/web.php contiene ruta POST /mail/messages/{id}/attachments.');
    } else {
        fail('No se encontró ruta POST /mail/messages/{id}/attachments en routes/web.php.', $criticalFailures);
    }

    if ($routesContent !== false && str_contains($routesContent, "POST /mail/messages/{id}/prepare-send")) {
        ok('routes/web.php contiene ruta POST /mail/messages/{id}/prepare-send.');
    } else {
        fail('No se encontró ruta POST /mail/messages/{id}/prepare-send en routes/web.php.', $criticalFailures);
    }


$adapterFile = $root . '/app/Core/Cloud/EcosistemaDriveAdapter.php';
if (is_file($adapterFile) && str_contains((string) file_get_contents($adapterFile), 'read_buckets_metadata')) {
    ok('EcosistemaDriveAdapter declara capability read_buckets_metadata.');
} else {
    fail('EcosistemaDriveAdapter no declara capability read_buckets_metadata.', $criticalFailures);
}

$folderRepositoryFile = $root . '/app/Core/Cloud/EcosistemaDriveFolderRepository.php';
if (is_file($folderRepositoryFile) && str_contains((string)file_get_contents($folderRepositoryFile), 'function listChildren(')) {
    ok('EcosistemaDriveFolderRepository contiene listChildren.');
} else {
    fail('EcosistemaDriveFolderRepository no contiene listChildren.', $criticalFailures);
}

$fileRepositoryFile = $root . '/app/Core/Cloud/EcosistemaDriveFileRepository.php';
if (is_file($fileRepositoryFile) && str_contains((string)file_get_contents($fileRepositoryFile), 'function listByFolder(')) {
    ok('EcosistemaDriveFileRepository contiene listByFolder.');
} else {
    fail('EcosistemaDriveFileRepository no contiene listByFolder.', $criticalFailures);
}

$folderServiceFile = $root . '/app/Core/Cloud/EcosistemaDriveFolderService.php';
if (is_file($folderServiceFile) && str_contains((string)file_get_contents($folderServiceFile), 'function getFolderBrowser(')) {
    ok('EcosistemaDriveFolderService contiene getFolderBrowser.');
} else {
    fail('EcosistemaDriveFolderService no contiene getFolderBrowser.', $criticalFailures);
}

$adapterFile = $root . '/app/Core/Cloud/EcosistemaDriveAdapter.php';
if (is_file($adapterFile) && str_contains((string)file_get_contents($adapterFile), 'read_folder_navigation')) {
    ok('EcosistemaDriveAdapter contiene capability read_folder_navigation.');
} else {
    fail('EcosistemaDriveAdapter no contiene capability read_folder_navigation.', $criticalFailures);
}

$requiredClasses = [
    'App\\Core\\Auth\\AuthorizationRepository',
    'App\\Core\\Auth\\AuthorizationService',
    'App\\Core\\System\\AuditLogger',
    'App\\Core\\Users\\UserRoleRepository',
    'App\\Core\\Users\\UserRoleService',
    'App\\Http\\Response\\ErrorResponder',
    'App\\Core\\Auth\\AuthSession',
    'App\\Core\\Mail\\MailConfig',
    'App\Core\Cloud\CloudStorageConfig',
    'App\Core\Cloud\CloudDownloadService',
    'App\Core\Onboarding\OnboardingRunner',
    'App\Core\Onboarding\OnboardingStepExecutor',
    'App\Core\Cloud\S3DriveIntegrationConfig',
    'App\Core\Cloud\EcosistemaDriveConfig',
    'App\Core\Cloud\EcosistemaDriveAdapter',
    'App\Core\Cloud\EcosistemaDriveFileRepository',
    'App\Core\Cloud\EcosistemaDriveFileService',
    'App\Core\Cloud\EcosistemaDriveFolderService',
    'App\Core\Cloud\EcosistemaDriveFolderRepository',
    'App\Core\System\CronHealthCheckRunner',
    'App\Core\Auth\CronSessionCleanupRunner',
];

foreach ($requiredClasses as $className) {
    if (class_exists($className)) {
        ok("Clase crítica disponible: {$className}");
        continue;
    }

    fail("No se pudo cargar clase crítica: {$className}", $criticalFailures);
}

$driveRepositoryFile = $root . '/app/Core/Cloud/EcosistemaDriveFileRepository.php';
if (is_file($driveRepositoryFile)) {
    $content = file_get_contents($driveRepositoryFile);
    if ($content !== false && str_contains($content, 'function findVisibleById(')) {
        ok('EcosistemaDriveFileRepository contiene findVisibleById para detalle seguro.');
    } else {
        fail('EcosistemaDriveFileRepository no contiene findVisibleById.', $criticalFailures);
    }
}

$driveServiceFile = $root . '/app/Core/Cloud/EcosistemaDriveFileService.php';
if (is_file($driveServiceFile)) {
    $content = file_get_contents($driveServiceFile);
    if ($content !== false && str_contains($content, 'function getFileDetail(')) {
        ok('EcosistemaDriveFileService contiene getFileDetail para DTO de detalle.');
    } else {
        fail('EcosistemaDriveFileService no contiene getFileDetail.', $criticalFailures);
    }
}


$adapterFile = $root . '/app/Core/Cloud/EcosistemaDriveAdapter.php';
if (is_file($adapterFile) && str_contains((string) file_get_contents($adapterFile), 'read_buckets_metadata')) {
    ok('EcosistemaDriveAdapter declara capability read_buckets_metadata.');
} else {
    fail('EcosistemaDriveAdapter no declara capability read_buckets_metadata.', $criticalFailures);
}

$folderRepositoryFile = $root . '/app/Core/Cloud/EcosistemaDriveFolderRepository.php';
if (is_file($folderRepositoryFile)) {
    $content = file_get_contents($folderRepositoryFile);
    if ($content !== false && str_contains($content, 'function findVisibleById(')) {
        ok('EcosistemaDriveFolderRepository contiene findVisibleById para detalle seguro de carpetas.');
    } else {
        fail('EcosistemaDriveFolderRepository no contiene findVisibleById.', $criticalFailures);
    }
}

$folderServiceFile = $root . '/app/Core/Cloud/EcosistemaDriveFolderService.php';
if (is_file($folderServiceFile)) {
    $content = file_get_contents($folderServiceFile);
    if ($content !== false && str_contains($content, 'function getFolderDetail(')) {
        ok('EcosistemaDriveFolderService contiene getFolderDetail para DTO de detalle de carpetas.');
    } else {
        fail('EcosistemaDriveFolderService no contiene getFolderDetail.', $criticalFailures);
    }
}

$driveAdapterFile = $root . '/app/Core/Cloud/EcosistemaDriveAdapter.php';
if (is_file($driveAdapterFile)) {
    $content = file_get_contents($driveAdapterFile);
    if ($content !== false && str_contains($content, "'read_folder_detail'")) {
        ok('EcosistemaDriveAdapter declara capability read_folder_detail.');
    } else {
        warn('EcosistemaDriveAdapter no declara capability read_folder_detail (opcional).', $warnings);
    }
}


$cronRunnerFile = $root . '/scripts/cron-runner.php';
if (is_file($cronRunnerFile)) {
    $cronRunnerContent = file_get_contents($cronRunnerFile);
    if ($cronRunnerContent !== false && str_contains($cronRunnerContent, '--run=health-checks')) {
        ok('scripts/cron-runner.php soporta --run=health-checks.');
    } else {
        fail('scripts/cron-runner.php no declara --run=health-checks.', $criticalFailures);
    }

    if ($cronRunnerContent !== false && str_contains($cronRunnerContent, '--run=session-cleanup')) {
        ok('scripts/cron-runner.php soporta --run=session-cleanup.');
    } else {
        fail('scripts/cron-runner.php no declara --run=session-cleanup.', $criticalFailures);
    }
}

$lintDirs = ['app', 'bootstrap', 'config', 'public', 'routes', 'resources/views'];
$phpFiles = [];
foreach ($lintDirs as $dir) {
    $absoluteDir = $root . '/' . $dir;
    if (!is_dir($absoluteDir)) {
        warn("Directorio no encontrado para lint PHP: {$dir}", $warnings);
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($absoluteDir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $fileInfo) {
        if ($fileInfo->isFile() && strtolower($fileInfo->getExtension()) === 'php') {
            $phpFiles[] = $fileInfo->getPathname();
        }
    }
}

sort($phpFiles);
$lintFailures = 0;
foreach ($phpFiles as $phpFile) {
    $command = 'php -l ' . escapeshellarg($phpFile) . ' 2>&1';
    $output = [];
    exec($command, $output, $code);

    if ($code !== 0) {
        $lintFailures++;
        fail('Lint PHP falló: ' . str_replace($root . '/', '', $phpFile), $criticalFailures);
        report('DETAIL', implode(PHP_EOL, $output));
    }
}

if ($lintFailures === 0) {
    ok('Lint PHP completado sin errores en directorios controlados.');
}

$sensitivePatterns = [
    'password_hash',
    'session_token_hash',
    'refresh_token_hash',
    'DB_PASSWORD',
    'AWS_SECRET',
    'SECRET',
];

$securityTargets = [$root . '/routes/web.php'];
$viewsRoot = $root . '/resources/views';
if (is_dir($viewsRoot)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($viewsRoot, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $fileInfo) {
        if ($fileInfo->isFile()) {
            $securityTargets[] = $fileInfo->getPathname();
        }
    }
}

$secretHits = 0;
foreach ($securityTargets as $targetFile) {
    if (!is_file($targetFile)) {
        continue;
    }

    $content = file_get_contents($targetFile);
    if ($content === false) {
        warn('No se pudo leer archivo para check de secretos: ' . str_replace($root . '/', '', $targetFile), $warnings);
        continue;
    }

    foreach ($sensitivePatterns as $pattern) {
        if (preg_match('/' . preg_quote($pattern, '/') . '/i', $content) === 1) {
            $secretHits++;
            fail('Posible exposición sensible en ' . str_replace($root . '/', '', $targetFile) . " (patrón: {$pattern})", $criticalFailures);
        }
    }
}


$adapterPath = $root . '/app/Core/Cloud/EcosistemaDriveAdapter.php';
if (is_file($adapterPath)) {
    $adapterContent = file_get_contents($adapterPath);
    if ($adapterContent !== false && str_contains($adapterContent, 'read_only_audit')) {
        ok('EcosistemaDriveAdapter declara capability read_only_audit.');
    } else {
        fail('EcosistemaDriveAdapter no contiene capability read_only_audit.', $criticalFailures);
    }
}

$routesPath = $root . '/routes/web.php';
if (is_file($routesPath)) {
    $routesContent = file_get_contents($routesPath);
    if ($routesContent !== false && str_contains($routesContent, 'driveAuditLog(')) {
        ok('routes/web.php contiene llamadas de auditoría read-only para Drive.');
    } else {
        fail('routes/web.php no contiene llamadas de auditoría Drive esperadas.', $criticalFailures);
    }
}

if ($secretHits === 0) {
    ok('Check de seguridad estática sin exposiciones sensibles en vistas/rutas.');
}



$versionRepoPath = $root . '/app/Core/Cloud/EcosistemaDriveFileVersionRepository.php';
$versionServicePath = $root . '/app/Core/Cloud/EcosistemaDriveFileVersionService.php';
$versionsViewPath = $root . '/resources/views/pages/cloud/drive-file-versions.php';
$versionsDocPath = $root . '/docs/project/ECOSISTEMA_DRIVE_FILE_VERSIONS.md';

if (is_file($versionRepoPath)) { ok('Existe EcosistemaDriveFileVersionRepository.'); } else { fail('No existe app/Core/Cloud/EcosistemaDriveFileVersionRepository.php.', $criticalFailures); }
if (is_file($versionServicePath)) { ok('Existe EcosistemaDriveFileVersionService.'); } else { fail('No existe app/Core/Cloud/EcosistemaDriveFileVersionService.php.', $criticalFailures); }
if (is_file($versionsViewPath)) { ok('Existe vista drive-file-versions.'); } else { fail('No existe resources/views/pages/cloud/drive-file-versions.php.', $criticalFailures); }
if (is_file($versionsDocPath)) { ok('Existe documentación ECOSISTEMA_DRIVE_FILE_VERSIONS.md.'); } else { fail('No existe docs/project/ECOSISTEMA_DRIVE_FILE_VERSIONS.md.', $criticalFailures); }


if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive/files/{id}/versions")) {
    ok('routes/web.php contiene ruta GET /cloud/drive/files/{id}/versions.');
} else {
    fail('No se encontró ruta GET /cloud/drive/files/{id}/versions en routes/web.php.', $criticalFailures);
}

if ($adapterContent !== false && str_contains($adapterContent, "read_file_versions")) {
    ok('EcosistemaDriveAdapter contiene capability read_file_versions.');
} else {
    fail('EcosistemaDriveAdapter no contiene capability read_file_versions.', $criticalFailures);
}

if ($adapterContent !== false && str_contains($adapterContent, "'version_restore' => false")) {
    ok('EcosistemaDriveAdapter mantiene version_restore=false.');
} else {
    fail('EcosistemaDriveAdapter no mantiene version_restore=false.', $criticalFailures);
}

if ($adapterContent !== false && str_contains($adapterContent, "'version_download' => false")) {
    ok('EcosistemaDriveAdapter mantiene version_download=false.');
} else {
    fail('EcosistemaDriveAdapter no mantiene version_download=false.', $criticalFailures);
}

$versionsViewContent = @file_get_contents($versionsViewPath);
if ($versionsViewContent !== false && !str_contains($versionsViewContent, "['s3_key']")) {
    ok('La vista de versiones no imprime s3_key.');
} else {
    fail('La vista de versiones imprime o podría imprimir s3_key.', $criticalFailures);
}

if ($versionsViewContent !== false && !str_contains($versionsViewContent, "['s3_version_id']")) {
    ok('La vista de versiones no imprime s3_version_id crudo.');
} else {
    fail('La vista de versiones imprime o podría imprimir s3_version_id crudo.', $criticalFailures);
}


$authorizationRepositoryPath = $root . '/app/Core/Auth/AuthorizationRepository.php';
$authorizationRepositoryContent = is_file($authorizationRepositoryPath) ? file_get_contents($authorizationRepositoryPath) : false;
if ($authorizationRepositoryContent !== false && str_contains($authorizationRepositoryContent, 'core_user_roles') && str_contains($authorizationRepositoryContent, 'core_role_permissions') && str_contains($authorizationRepositoryContent, 'p.code = :permission_code')) { ok('requirePermission valida permisos con core_user_roles/core_role_permissions/core_permissions.code.'); } else { fail('requirePermission no valida permisos contra las tablas/código esperados.', $criticalFailures); }
foreach (['core_roles.status', 'core_permissions.status'] as $forbiddenDependency) { if ($authorizationRepositoryContent !== false && str_contains($authorizationRepositoryContent, $forbiddenDependency)) { fail('AuthorizationRepository depende de columna no canónica: ' . $forbiddenDependency, $criticalFailures); } else { ok('AuthorizationRepository no depende de ' . $forbiddenDependency . '.'); } }

$userRoleRepositoryPath = $root . '/app/Core/Users/UserRoleRepository.php';
$userRoleRepositoryContent = is_file($userRoleRepositoryPath) ? file_get_contents($userRoleRepositoryPath) : false;
if ($userRoleRepositoryContent !== false && str_contains($userRoleRepositoryContent, 'slug') && str_contains($userRoleRepositoryContent, "\$row['code'] =")) { ok('UserRoleRepository usa slug y mapea code defensivamente.'); } else { fail('UserRoleRepository no aplica fallback code=>slug.', $criticalFailures); }
if ($userRoleRepositoryContent !== false && str_contains($userRoleRepositoryContent, "\$row['status'] = 'active'")) { ok('UserRoleRepository mapea status=active defensivamente.'); } else { fail('UserRoleRepository no mapea status defensivo.', $criticalFailures); }

$permissionRepositoryPath = $root . '/app/Core/Permissions/PermissionRepository.php';
$permissionRepositoryContent = is_file($permissionRepositoryPath) ? file_get_contents($permissionRepositoryPath) : false;
if ($permissionRepositoryContent !== false && str_contains($permissionRepositoryContent, "\$row['status'] = 'active';") && str_contains($permissionRepositoryContent, "\$row['action'] = '';") && str_contains($permissionRepositoryContent, "\$row['resource'] = '';")) { ok('PermissionRepository aplica fallback status/action/resource.'); } else { fail('PermissionRepository no aplica fallback status/action/resource.', $criticalFailures); }
foreach (['user_id = 1', 'role_id = 1', 'jimmybackend@gmail.com'] as $forbiddenBypass) { if (($authorizationRepositoryContent !== false && str_contains($authorizationRepositoryContent, $forbiddenBypass)) || ($permissionRepositoryContent !== false && str_contains($permissionRepositoryContent, $forbiddenBypass))) { fail('Patrón de bypass hardcodeado detectado: ' . $forbiddenBypass, $criticalFailures); } else { ok('Sin bypass hardcodeado: ' . $forbiddenBypass); } }

warn('Checks HTTP opcionales (manuales): php -S 127.0.0.1:8000 -t public && curl -I /login /dashboard /health/db', $warnings);

echo PHP_EOL;
echo 'Resumen: ' . PHP_EOL;
echo "- Críticos fallidos: {$criticalFailures}" . PHP_EOL;
echo "- Warnings: {$warnings}" . PHP_EOL;

exit($criticalFailures === 0 ? 0 : 1);


$adapterPath = $root . '/app/Core/Cloud/EcosistemaDriveAdapter.php';
if (is_file($adapterPath)) {
    $adapterContent = file_get_contents($adapterPath);
    if ($adapterContent !== false && str_contains($adapterContent, "read_access_policy")) {
        ok('EcosistemaDriveAdapter expone capability read_access_policy.');
    } else {
        fail('EcosistemaDriveAdapter no contiene capability read_access_policy.', $criticalFailures);
    }
}

$readmePath = $root . '/README.md';
if (is_file($readmePath)) {
    $readmeContent = file_get_contents($readmePath);
    if ($readmeContent !== false && str_contains($readmeContent, "ECOSISTEMA_DRIVE_ACCESS_POLICY.md")) {
        ok('README referencia la política de acceso Drive.');
    } else {
        fail('README no referencia la política de acceso Drive.', $criticalFailures);
    }
}

$driveAdapterFile = $root . '/app/Core/Cloud/EcosistemaDriveAdapter.php';
$driveAdapterContent = is_file($driveAdapterFile) ? file_get_contents($driveAdapterFile) : false;
if ($driveAdapterContent !== false && str_contains($driveAdapterContent, "'safe_s3_key_validation'") && str_contains($driveAdapterContent, "'signed_urls' => [\n                'enabled' => false") && str_contains($driveAdapterContent, "'remote_downloads' => [\n                'enabled' => false") && str_contains($driveAdapterContent, "'remote_uploads' => [\n                'enabled' => false")) {
    ok('EcosistemaDriveAdapter declara safe_s3_key_validation y mantiene signed_urls/remote_downloads/remote_uploads deshabilitados.');
} else {
    fail('EcosistemaDriveAdapter no cumple capability safe_s3_key_validation o activó capacidades remotas.', $criticalFailures);
}

$validationViewPath = $root . '/resources/views/pages/cloud/drive-s3-key-validation.php';
$validationView = is_file($validationViewPath) ? file_get_contents($validationViewPath) : false;
if ($validationView !== false && !str_contains($validationView, "\$validation['s3_key']") && !str_contains($validationView, "\$file['s3_key']")) {
    ok('La vista de validación no imprime s3_key cruda.');
} else {
    fail('La vista de validación parece exponer s3_key cruda.', $criticalFailures);
}

$forbiddenPatterns = ['Aws\\S3\\S3Client', 'createPresignedRequest', '->getObject(', '->putObject('];
$scanFiles = ['routes/web.php', 'app/Core/Cloud/EcosistemaDriveAwsS3Config.php', 'app/Core/Cloud/EcosistemaDriveSignedUrlDryRun.php'];
foreach ($scanFiles as $scanFile) { $content = is_file($root . '/' . $scanFile) ? file_get_contents($root . '/' . $scanFile) : ''; foreach ($forbiddenPatterns as $pattern) { if ($content !== false && str_contains((string)$content, $pattern)) { fail('Patrón prohibido detectado: ' . $pattern . ' en ' . $scanFile, $criticalFailures); } } }
$viewPath = $root . '/resources/views/pages/cloud/drive-aws-config.php';
$viewContent = is_file($viewPath) ? file_get_contents($viewPath) : '';
foreach (['ACCESS_KEY','AWS_SECRET_ACCESS_KEY','AWS_SESSION_TOKEN','s3_key'] as $forbiddenViewToken) { if ($viewContent !== false && str_contains((string)$viewContent, $forbiddenViewToken)) { fail('Vista AWS config contiene token sensible: ' . $forbiddenViewToken, $criticalFailures); } }


$uploadDryRunService = $root . '/app/Core/Cloud/EcosistemaDriveS3UploadDryRunService.php';
if (is_file($uploadDryRunService)) {
    $content = (string) file_get_contents($uploadDryRunService);
    foreach (['putObject', 'Aws\\S3\\S3Client', 'move_uploaded_file'] as $forbidden) {
        if (str_contains($content, $forbidden)) { fail('Servicio upload dry-run contiene operación prohibida: ' . $forbidden, $criticalFailures); }
        else { ok('Servicio upload dry-run no contiene: ' . $forbidden); }
    }
}
$uploadDryRunView = $root . '/resources/views/pages/cloud/drive-upload-dry-run.php';
if (is_file($uploadDryRunView)) {
    $content = (string) file_get_contents($uploadDryRunView);
    foreach (['AWS_SECRET_ACCESS_KEY', 'AWS_ACCESS_KEY_ID', 'AWS_SESSION_TOKEN', 's3_key', 'stored_name', 'root_prefix', 'config_json'] as $forbidden) {
        if (str_contains($content, $forbidden)) { fail('Vista upload dry-run expone contenido sensible: ' . $forbidden, $criticalFailures); }
        else { ok('Vista upload dry-run no expone: ' . $forbidden); }
    }
}


$versionRepoPath = $root . '/app/Core/Cloud/EcosistemaDriveFileVersionRepository.php';
$versionServicePath = $root . '/app/Core/Cloud/EcosistemaDriveFileVersionService.php';
$versionsViewPath = $root . '/resources/views/pages/cloud/drive-file-versions.php';
$versionsDocPath = $root . '/docs/project/ECOSISTEMA_DRIVE_FILE_VERSIONS.md';

if (is_file($versionRepoPath)) { ok('Existe EcosistemaDriveFileVersionRepository.'); } else { fail('No existe app/Core/Cloud/EcosistemaDriveFileVersionRepository.php.', $criticalFailures); }
if (is_file($versionServicePath)) { ok('Existe EcosistemaDriveFileVersionService.'); } else { fail('No existe app/Core/Cloud/EcosistemaDriveFileVersionService.php.', $criticalFailures); }
if (is_file($versionsViewPath)) { ok('Existe vista drive-file-versions.'); } else { fail('No existe resources/views/pages/cloud/drive-file-versions.php.', $criticalFailures); }
if (is_file($versionsDocPath)) { ok('Existe documentación ECOSISTEMA_DRIVE_FILE_VERSIONS.md.'); } else { fail('No existe docs/project/ECOSISTEMA_DRIVE_FILE_VERSIONS.md.', $criticalFailures); }


if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive/files/{id}/versions")) {
    ok('routes/web.php contiene ruta GET /cloud/drive/files/{id}/versions.');
} else {
    fail('No se encontró ruta GET /cloud/drive/files/{id}/versions en routes/web.php.', $criticalFailures);
}

if ($adapterContent !== false && str_contains($adapterContent, "read_file_versions")) {
    ok('EcosistemaDriveAdapter contiene capability read_file_versions.');
} else {
    fail('EcosistemaDriveAdapter no contiene capability read_file_versions.', $criticalFailures);
}

if ($adapterContent !== false && str_contains($adapterContent, "'version_restore' => false")) {
    ok('EcosistemaDriveAdapter mantiene version_restore=false.');
} else {
    fail('EcosistemaDriveAdapter no mantiene version_restore=false.', $criticalFailures);
}

if ($adapterContent !== false && str_contains($adapterContent, "'version_download' => false")) {
    ok('EcosistemaDriveAdapter mantiene version_download=false.');
} else {
    fail('EcosistemaDriveAdapter no mantiene version_download=false.', $criticalFailures);
}

$versionsViewContent = @file_get_contents($versionsViewPath);
if ($versionsViewContent !== false && !str_contains($versionsViewContent, "['s3_key']")) {
    ok('La vista de versiones no imprime s3_key.');
} else {
    fail('La vista de versiones imprime o podría imprimir s3_key.', $criticalFailures);
}

if ($versionsViewContent !== false && !str_contains($versionsViewContent, "['s3_version_id']")) {
    ok('La vista de versiones no imprime s3_version_id crudo.');
} else {
    fail('La vista de versiones imprime o podría imprimir s3_version_id crudo.', $criticalFailures);
}


$webRoutesContent = @file_get_contents($root . '/routes/web.php') ?: '';
if (str_contains($webRoutesContent, "GET /cloud/drive/upload")) { ok('routes/web.php contiene GET /cloud/drive/upload'); } else { fail('Falta GET /cloud/drive/upload en routes/web.php', $criticalFailures); }
if (str_contains($webRoutesContent, "POST /cloud/drive/upload")) { ok('routes/web.php contiene POST /cloud/drive/upload'); } else { fail('Falta POST /cloud/drive/upload en routes/web.php', $criticalFailures); }

$adapterContent = @file_get_contents($root . '/app/Core/Cloud/EcosistemaDriveAdapter.php') ?: '';
if (str_contains($adapterContent, "controlled_upload")) { ok('Adapter contiene capability controlled_upload'); } else { fail('Adapter no contiene controlled_upload', $criticalFailures); }

if ($envContent !== false && str_contains($envContent, 'CLOUD_ALLOW_UPLOADS=false') && str_contains($envContent, 'CLOUD_S3_ENABLED=false') && str_contains($envContent, 'ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS=false')) {
    ok('.env.example mantiene flags de upload apagadas por defecto.');
} else {
    fail('.env.example no mantiene flags de upload apagadas por defecto.', $criticalFailures);
}

$uploadServiceContent = @file_get_contents($root . '/app/Core/Cloud/EcosistemaDriveS3UploadService.php') ?: '';
if (str_contains($uploadServiceContent, 'putObject')) { ok('putObject aparece en servicio controlado.'); } else { warn('No se detectó putObject en servicio controlado.', $warnings); }
if (!str_contains($uploadServiceContent, 'SignatureV4') && !str_contains($uploadServiceContent, 'curl_exec')) { ok('No hay firma manual AWS/curl hack en servicio controlado.'); } else { fail('Se detectó posible firma manual AWS/curl hack.', $criticalFailures); }
if (!str_contains($uploadServiceContent, "\$_POST['s3_key']") && !str_contains($uploadServiceContent, "\$_POST['bucket']") && !str_contains($uploadServiceContent, "\$_POST['path']")) { ok('No se acepta s3_key/bucket/path desde request.'); } else { fail('Se detectó aceptación de s3_key/bucket/path desde request.', $criticalFailures); }

$viewUploadContent = @file_get_contents($root . '/resources/views/pages/cloud/drive-upload.php') ?: '';
$viewResultContent = @file_get_contents($root . '/resources/views/pages/cloud/drive-upload-result.php') ?: '';
if (!str_contains($viewUploadContent, 's3_key') && !str_contains($viewUploadContent, 'stored_name') && !str_contains($viewResultContent, 's3_key') && !str_contains($viewResultContent, 'stored_name')) {
    ok('Vistas de upload no imprimen s3_key/stored_name.');
} else {
    fail('Vistas de upload exponen s3_key o stored_name.', $criticalFailures);
}
