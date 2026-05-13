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
    'docs/project/ECOSISTEMA_DRIVE_PRODUCTION_READINESS_CHECKLIST.md',
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
    'docs/project/ECOSISTEMA_DRIVE_ACCESS_LOGS.md',
    'resources/views/pages/cloud/drive-file-access-logs.php',
    'resources/views/pages/cloud/drive-access-logs.php',
    'app/Core/Cloud/EcosistemaDriveAccessLogService.php',
    'app/Core/Cloud/EcosistemaDriveAccessLogRepository.php',
    'docs/project/ECOSISTEMA_DRIVE_STORAGE_USAGE.md',
    'resources/views/pages/cloud/drive-storage-usage.php',
    'app/Core/Cloud/EcosistemaDriveStorageUsageService.php',
    'app/Core/Cloud/EcosistemaDriveStorageUsageRepository.php',
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
    'config/url_locator.php',
    'docs/project/ECOSISTEMA_URL_LOCATOR_CREATE_EDIT_CONTROLLED.md',
    'resources/views/pages/url-locator/link-form.php',
    'app/Core/UrlLocator/EcosistemaUrlLocatorLinkWriteRepository.php',
    'app/Core/UrlLocator/EcosistemaUrlLocatorLinkWriteService.php',
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

    $requiredEnvKeys = ['APP_DEBUG=', 'SESSION_SECURE=', 'DB_DATABASE=', 'MAIL_HOST=', 'MAIL_SEND_ENABLED=', 'MAIL_ALLOW_TEST_SEND=', 'AWS_BUCKET=', 'CLOUD_S3_ENABLED=', 'CLOUD_ALLOW_DOWNLOADS=', 'CLOUD_ALLOW_UPLOADS=', 'CLOUD_MAX_UPLOAD_MB=', 'CLOUD_ALLOWED_EXTENSIONS=', 'MAIL_MAX_ATTACHMENTS=', 'MAIL_MAX_ATTACHMENT_MB=', 'MAIL_MAX_TOTAL_ATTACHMENT_MB=', 'S3_DRIVE_ENABLED=', 'S3_DRIVE_MODE=', 'S3_DRIVE_BASE_URL=', 'S3_DRIVE_API_TIMEOUT=', 'S3_DRIVE_ALLOW_REMOTE_CALLS=', 'S3_DRIVE_ALLOW_SIGNED_URLS=', 'S3_DRIVE_ALLOW_REMOTE_UPLOADS=', 'S3_DRIVE_ALLOW_REMOTE_DOWNLOADS=', 'ECOSISTEMA_DRIVE_ENABLED=', 'ECOSISTEMA_DRIVE_MODE=', 'ECOSISTEMA_DRIVE_REFERENCE_REPO=', 'ECOSISTEMA_DRIVE_AWS_ENABLED=', 'ECOSISTEMA_DRIVE_AWS_REGION=', 'ECOSISTEMA_DRIVE_AWS_BUCKET=', 'ECOSISTEMA_DRIVE_AWS_ENDPOINT=', 'ECOSISTEMA_DRIVE_AWS_ACCESS_KEY_ID=', 'ECOSISTEMA_DRIVE_AWS_SECRET_ACCESS_KEY=', 'ECOSISTEMA_DRIVE_AWS_SESSION_TOKEN=', 'ECOSISTEMA_DRIVE_API_TIMEOUT=', 'ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=', 'ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS=', 'ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS=', 'ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS=', 'CORE_REGISTRATION_ENABLED=', 'CORE_REGISTRATION_MODE=', 'CORE_REGISTRATION_INVITE_CODE=', 'CORE_REGISTRATION_DEFAULT_TENANT_ID=', 'CORE_REGISTRATION_DEFAULT_ROLE_ID=', 'ECOSISTEMA_URL_LOCATOR_ENABLED=', 'ECOSISTEMA_URL_LOCATOR_ADMIN_WRITE_ENABLED=', 'ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECTS=', 'ECOSISTEMA_URL_LOCATOR_TRACKING_ENABLED='];
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

$routesSmoke = @file_get_contents($root . '/routes/web.php') ?: '';
if (str_contains($routesSmoke, 'GET /cloud/drive/access-logs')) { ok('routes/web.php contiene /cloud/drive/access-logs.'); } else { fail('Falta ruta /cloud/drive/access-logs.', $criticalFailures); }
if (str_contains($routesSmoke, 'GET /cloud/drive/files/{id}/access-logs')) { ok('routes/web.php contiene /cloud/drive/files/{id}/access-logs.'); } else { fail('Falta ruta /cloud/drive/files/{id}/access-logs.', $criticalFailures); }
$adapterSmoke = @file_get_contents($root . '/app/Core/Cloud/EcosistemaDriveAdapter.php') ?: '';
if (str_contains($adapterSmoke, "'access_logs_read'")) { ok('Adapter contiene access_logs_read.'); } else { fail('Adapter no contiene access_logs_read.', $criticalFailures); }
if (str_contains($adapterSmoke, "'access_logs_write' => false")) { ok('Adapter mantiene access_logs_write=false.'); } else { fail('Adapter no mantiene access_logs_write=false.', $criticalFailures); }
$accessView = @file_get_contents($root . '/resources/views/pages/cloud/drive-access-logs.php') ?: '';
$fileAccessView = @file_get_contents($root . '/resources/views/pages/cloud/drive-file-access-logs.php') ?: '';
if (!str_contains($accessView, 's3_key') && !str_contains($fileAccessView, 's3_key')) { ok('Vistas access logs no imprimen s3_key.'); } else { fail('Vistas access logs exponen s3_key.', $criticalFailures); }
if (!str_contains($accessView, 'metadata_json') && !str_contains($fileAccessView, 'metadata_json')) { ok('Vistas access logs no imprimen metadata_json crudo.'); } else { fail('Vistas access logs exponen metadata_json crudo.', $criticalFailures); }
$repoAccessLog = @file_get_contents($root . '/app/Core/Cloud/EcosistemaDriveAccessLogRepository.php') ?: '';
if (!str_contains($repoAccessLog, 'INSERT INTO cloud_file_access_logs') && !str_contains($repoAccessLog, 'UPDATE cloud_file_access_logs') && !str_contains($repoAccessLog, 'DELETE FROM cloud_file_access_logs')) { ok('Repository access logs sin escrituras sobre cloud_file_access_logs.'); } else { fail('Repository access logs contiene escrituras prohibidas.', $criticalFailures); }



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

$repairRepoPath = $root . '/app/Core/Cloud/EcosistemaDriveRepairJobRepository.php';
$repairServicePath = $root . '/app/Core/Cloud/EcosistemaDriveRepairJobService.php';
$repairListViewPath = $root . '/resources/views/pages/cloud/drive-repair-jobs.php';
$repairDetailViewPath = $root . '/resources/views/pages/cloud/drive-repair-job-detail.php';
$repairDocPath = $root . '/docs/project/ECOSISTEMA_DRIVE_REPAIR_JOBS.md';

foreach ([$repairRepoPath, $repairServicePath, $repairListViewPath, $repairDetailViewPath, $repairDocPath] as $requiredPath) {
    if (is_file($requiredPath)) { ok('Existe recurso repair jobs: ' . str_replace($root . '/', '', $requiredPath)); }
    else { fail('No existe recurso repair jobs: ' . str_replace($root . '/', '', $requiredPath), $criticalFailures); }
}

$routesWebContent = @file_get_contents($root . '/routes/web.php') ?: '';
if (str_contains($routesWebContent, 'GET /cloud/drive/repair-jobs')) { ok('routes/web.php contiene /cloud/drive/repair-jobs.'); }
else { fail('routes/web.php no contiene /cloud/drive/repair-jobs.', $criticalFailures); }

$adapterContentRepair = @file_get_contents($root . '/app/Core/Cloud/EcosistemaDriveAdapter.php') ?: '';
if (str_contains($adapterContentRepair, "'repair_jobs_read' => true")) { ok('Adapter contiene repair_jobs_read=true.'); }
else { fail('Adapter no contiene repair_jobs_read=true.', $criticalFailures); }
if (str_contains($adapterContentRepair, "'repair_jobs_execute' => false")) { ok('Adapter mantiene repair_jobs_execute=false.'); }
else { fail('Adapter no mantiene repair_jobs_execute=false.', $criticalFailures); }

$repairRepoContent = @file_get_contents($repairRepoPath) ?: '';
foreach (['INSERT INTO cloud_repair_jobs', 'UPDATE cloud_repair_jobs', 'DELETE FROM cloud_repair_jobs', 'INSERT INTO cloud_repair_logs', 'UPDATE cloud_repair_logs', 'DELETE FROM cloud_repair_logs'] as $forbiddenSql) {
    if (stripos($repairRepoContent, $forbiddenSql) === false) { ok('Repair repository no contiene: ' . $forbiddenSql); }
    else { fail('Repair repository contiene SQL prohibido: ' . $forbiddenSql, $criticalFailures); }
}

$repairListView = @file_get_contents($repairListViewPath) ?: '';
$repairDetailView = @file_get_contents($repairDetailViewPath) ?: '';
if (!str_contains($repairListView, "['prefix']") && !str_contains($repairDetailView, "['prefix']")) { ok('Vistas repair no imprimen prefix crudo.'); }
else { fail('Vistas repair podrían imprimir prefix crudo.', $criticalFailures); }
if (!str_contains($repairListView, "old_s3_key") && !str_contains($repairDetailView, "['old_s3_key']") && !str_contains($repairDetailView, "['new_s3_key']")) { ok('Vistas repair no imprimen old/new s3_key crudos.'); }
else { fail('Vistas repair podrían imprimir old/new s3_key crudos.', $criticalFailures); }
if (!str_contains($repairListView, "['s3_key']") && !str_contains($repairDetailView, "['s3_key']")) { ok('Vistas repair no imprimen s3_key.'); }
else { fail('Vistas repair podrían imprimir s3_key.', $criticalFailures); }

$cockpitDoc = $root . '/docs/project/ECOSISTEMA_DRIVE_OPERATIONAL_COCKPIT.md';
if (is_file($cockpitDoc)) { ok('Existe docs/project/ECOSISTEMA_DRIVE_OPERATIONAL_COCKPIT.md.'); }
else { fail('No existe docs/project/ECOSISTEMA_DRIVE_OPERATIONAL_COCKPIT.md.', $criticalFailures); }

$driveView = $root . '/resources/views/pages/cloud/drive.php';
$driveViewContent = @file_get_contents($driveView) ?: '';
foreach (['/cloud/drive/files','/cloud/drive/folders','/cloud/drive/buckets','/cloud/drive/summary','/cloud/drive/upload','/cloud/drive/access-logs','/cloud/drive/storage-usage','/cloud/drive/repair-jobs'] as $requiredLink) {
    if (str_contains($driveViewContent, $requiredLink)) { ok('drive.php contiene enlace: ' . $requiredLink); }
    else { fail('drive.php no contiene enlace: ' . $requiredLink, $criticalFailures); }
}

foreach (['read_metadata','read_drive_summary','read_access_policy','safe_s3_key_validation','controlled_download','controlled_upload','repair_jobs_read','repair_logs_read'] as $capability) {
    if (str_contains($adapterContent, $capability)) { ok('Adapter contiene capability principal: ' . $capability); }
    else { fail('Adapter no contiene capability principal: ' . $capability, $criticalFailures); }
}

foreach (['s3_key','stored_name','prefix','config_json','metadata_json'] as $forbiddenPrint) {
    if (!str_contains($driveViewContent, $forbiddenPrint)) { ok('Panel Drive no imprime campo sensible: ' . $forbiddenPrint); }
    else { fail('Panel Drive contiene referencia sensible: ' . $forbiddenPrint, $criticalFailures); }
}

$cloudCoreDir = $root . '/app/Core/Cloud';
$cloudPhpFiles = glob($cloudCoreDir . '/*.php') ?: [];
foreach ($cloudPhpFiles as $phpFile) {
    $relative = str_replace($root . '/', '', $phpFile);
    $content = (string) file_get_contents($phpFile);
    if ($relative !== 'app/Core/Cloud/EcosistemaDriveS3UploadService.php' && str_contains($content, 'putObject')) {
        fail('putObject detectado fuera de EcosistemaDriveS3UploadService.php en ' . $relative, $criticalFailures);
    }
}


$urlLocatorInventoryPath = $root . '/docs/project/ECOSISTEMA_URL_LOCATOR_SCHEMA_INVENTORY.md';
if (is_file($urlLocatorInventoryPath)) {
    ok('Existe inventario URL Locator canónico.');
} else {
    fail('No existe docs/project/ECOSISTEMA_URL_LOCATOR_SCHEMA_INVENTORY.md.', $criticalFailures);
}

$readmeContent = is_file($root . '/README.md') ? file_get_contents($root . '/README.md') : false;
if ($readmeContent !== false && str_contains($readmeContent, 'ECOSISTEMA_URL_LOCATOR_SCHEMA_INVENTORY.md')) {
    ok('README.md referencia inventario URL Locator.');
} else {
    fail('README.md no referencia inventario URL Locator.', $criticalFailures);
}

$urlLocatorContent = is_file($urlLocatorInventoryPath) ? file_get_contents($urlLocatorInventoryPath) : false;
foreach (['url_short_links', 'url_clicks', 'mailit-click', 'adbbmis1_eco'] as $requiredMention) {
    if ($urlLocatorContent !== false && str_contains($urlLocatorContent, $requiredMention)) {
        ok('Inventario URL Locator menciona: ' . $requiredMention);
    } else {
        fail('Inventario URL Locator no menciona: ' . $requiredMention, $criticalFailures);
    }
}

$gitDiff = shell_exec('git diff -- .');
if (is_string($gitDiff) && preg_match('#^\+.*(/u/\{slug\}|/url/locator)#mi', $gitDiff) === 1) {
    fail('Se detectó posible ruta funcional URL Locator en cambios del PR.', $criticalFailures);
} else {
    ok('No se detectaron rutas funcionales URL Locator en cambios del PR.');
}

if (is_string($gitDiff) && preg_match('/^\+.*(INSERT|UPDATE|DELETE).*(url_short_links|url_clicks)/mi', $gitDiff) === 1) {
    fail('Se detectó escritura SQL sobre url_short_links/url_clicks en cambios del PR.', $criticalFailures);
} else {
    ok('Sin escrituras SQL sobre url_short_links/url_clicks en cambios del PR.');
}

if (is_string($gitDiff) && preg_match('/^\+\+\+ b\/.*(migrations?|seeds?)\//mi', $gitDiff) === 1) {
    fail('Se detectó creación/modificación en rutas de migraciones o seeds.', $criticalFailures);
} else {
    ok('No se detectaron cambios en migraciones o seeds.');
}

if ($criticalFailures > 0) {
    report('RESULT', 'SMOKE CHECK FAILURES=' . $criticalFailures . ' WARNINGS=' . $warnings);
    exit(1);
}

report('RESULT', 'SMOKE CHECK OK WARNINGS=' . $warnings);
exit(0);


$driveChecklistPath = $root . '/docs/project/ECOSISTEMA_DRIVE_PRODUCTION_READINESS_CHECKLIST.md';
if (is_file($driveChecklistPath)) {
    $checklistContent = file_get_contents($driveChecklistPath);
    $readmeContent = is_file($root . '/README.md') ? file_get_contents($root . '/README.md') : false;

    if ($readmeContent !== false && str_contains($readmeContent, 'ECOSISTEMA_DRIVE_PRODUCTION_READINESS_CHECKLIST.md')) {
        ok('README.md referencia checklist de producción de Drive.');
    } else {
        fail('README.md no referencia checklist de producción de Drive.', $criticalFailures);
    }

    $criticalFlags = [
        'ECOSISTEMA_DRIVE_ENABLED',
        'ECOSISTEMA_DRIVE_AWS_ENABLED',
        'ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS',
        'ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS',
        'ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS',
        'ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS',
        'CLOUD_S3_ENABLED',
        'CLOUD_ALLOW_UPLOADS',
        'CLOUD_ALLOW_DOWNLOADS',
    ];
    foreach ($criticalFlags as $criticalFlag) {
        if ($checklistContent !== false && str_contains($checklistContent, $criticalFlag)) {
            ok('Checklist menciona flag crítica: ' . $criticalFlag);
        } else {
            fail('Checklist no menciona flag crítica: ' . $criticalFlag, $criticalFailures);
        }
    }

    $secretLeakPatterns = ['AKIA', 'aws_secret_access_key=', 'db_password=', 'smtp password'];
    foreach ($secretLeakPatterns as $pattern) {
        if ($checklistContent !== false && preg_match('/' . preg_quote($pattern, '/') . '/i', $checklistContent) === 1) {
            fail('Checklist contiene posible patrón sensible: ' . $pattern, $criticalFailures);
        } else {
            ok('Checklist sin patrón sensible: ' . $pattern);
        }
    }
}

if (is_file($envExample)) {
    $mustBeFalse = [
        'ECOSISTEMA_DRIVE_ENABLED=false',
        'ECOSISTEMA_DRIVE_AWS_ENABLED=false',
        'ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=false',
        'ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS=false',
        'ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS=false',
        'ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS=false',
        'CLOUD_S3_ENABLED=false',
        'CLOUD_ALLOW_UPLOADS=false',
        'CLOUD_ALLOW_DOWNLOADS=false',
    ];

    foreach ($mustBeFalse as $expectedDefault) {
        if ($envContent !== false && str_contains($envContent, $expectedDefault)) {
            ok('.env.example mantiene default seguro: ' . $expectedDefault);
        } else {
            fail('.env.example no mantiene default seguro: ' . $expectedDefault, $criticalFailures);
        }
    }
}


$baseBranch = trim((string) shell_exec('git rev-parse --abbrev-ref HEAD 2>/dev/null'));
if ($baseBranch !== '') {
    $diffRoutes = shell_exec('git diff -- routes/web.php');
    if (is_string($diffRoutes) && preg_match('/^\+\s*\$router->post\(/mi', $diffRoutes) === 1) {
        fail('Se detectaron rutas POST nuevas en routes/web.php.', $criticalFailures);
    } else {
        ok('No se detectaron rutas POST nuevas en routes/web.php.');
    }

    $diffAll = shell_exec('git diff');
    if (is_string($diffAll) && preg_match('/^\+.*new\\S3\\S3Client/mi', $diffAll) === 1) {
        fail('Se detectó nuevo Aws\S3\S3Client fuera de flujo esperado.', $criticalFailures);
    } else {
        ok('Sin nuevo Aws\S3\S3Client en cambios del PR.');
    }

    if (is_string($diffAll) && preg_match('/^\+.*->putObject\(/mi', $diffAll) === 1) {
        $uploadServiceDiff = shell_exec('git diff -- app/Core/Cloud/EcosistemaDriveS3UploadService.php');
        $otherPutObject = is_string($diffAll) ? preg_replace('/^\+.*EcosistemaDriveS3UploadService\.php.*$/mi', '', $diffAll) : '';
        if (is_string($otherPutObject) && preg_match('/^\+.*->putObject\(/mi', $otherPutObject) === 1) {
            fail('Se detectó putObject fuera de EcosistemaDriveS3UploadService.php.', $criticalFailures);
        } else {
            ok('No se detectó putObject nuevo fuera de servicio controlado.');
        }
    } else {
        ok('No se detectó putObject nuevo en cambios del PR.');
    }

    if (is_string($diffAll) && preg_match('/^\+.*(INSERT|UPDATE|DELETE).*cloud_/mi', $diffAll) === 1) {
        fail('Se detectó nueva escritura SQL sobre tablas cloud_* en este PR.', $criticalFailures);
    } else {
        ok('Sin nuevas escrituras SQL sobre cloud_* en este PR.');
    }
}

$urlLocatorFiles = [
    'app/Core/UrlLocator/EcosistemaUrlLocatorAdapter.php',
    'app/Core/UrlLocator/EcosistemaUrlLocatorLinkRepository.php',
    'app/Core/UrlLocator/EcosistemaUrlLocatorLinkService.php',
    'app/Core/UrlLocator/EcosistemaUrlLocatorClickRepository.php',
    'app/Core/UrlLocator/EcosistemaUrlLocatorClickService.php',
    'resources/views/pages/url-locator/index.php',
    'resources/views/pages/url-locator/links.php',
    'resources/views/pages/url-locator/clicks.php',
    'resources/views/pages/url-locator/link-clicks.php',
    'docs/project/ECOSISTEMA_URL_LOCATOR_READ_ONLY_LINKS.md',
];
foreach ($urlLocatorFiles as $file) {
    checkFile($root, $file, $criticalFailures);
}

$routesContent = @file_get_contents($root . '/routes/web.php');
if ($routesContent !== false && str_contains($routesContent, '/url/locator')) { ok('routes/web.php contiene /url/locator'); } else { fail('routes/web.php no contiene /url/locator', $criticalFailures); }
$routesNormalized = $routesContent !== false ? str_replace(' ', '', $routesContent) : '';
if ($routesContent !== false && str_contains($routesContent, '/url/locator/links')) { ok('routes/web.php contiene /url/locator/links'); } else { fail('routes/web.php no contiene /url/locator/links', $criticalFailures); }
if ($routesContent !== false && str_contains($routesContent, '/url/locator/clicks')) { ok('routes/web.php contiene /url/locator/clicks'); } else { fail('routes/web.php no contiene /url/locator/clicks', $criticalFailures); }
if ($routesContent !== false && str_contains($routesContent, '/url/locator/links/{id}/clicks')) { ok('routes/web.php contiene /url/locator/links/{id}/clicks'); } else { fail('routes/web.php no contiene /url/locator/links/{id}/clicks', $criticalFailures); }

$adapterContent = @file_get_contents($root . '/app/Core/UrlLocator/EcosistemaUrlLocatorAdapter.php');
if ($adapterContent !== false && str_contains($adapterContent, "'links_read'=>true")) { ok('Adapter define links_read true'); } else { fail('Adapter no define links_read true', $criticalFailures); }
if ($adapterContent !== false && str_contains($adapterContent, "'links_write'=>false")) { ok('Adapter mantiene links_write false'); } else { fail('Adapter no mantiene links_write false', $criticalFailures); }
if ($adapterContent !== false && str_contains($adapterContent, "'public_redirects' => false")) { ok('Adapter mantiene public_redirects false'); } else { fail('Adapter no mantiene public_redirects false', $criticalFailures); }
if ($adapterContent !== false && str_contains($adapterContent, "'clicks_read' => true")) { ok('Adapter define clicks_read true'); } else { fail('Adapter no define clicks_read true', $criticalFailures); }
if ($adapterContent !== false && str_contains($adapterContent, "'click_tracking_write' => false")) { ok('Adapter mantiene click_tracking_write false'); } else { fail('Adapter no mantiene click_tracking_write false', $criticalFailures); }

$repoContent = @file_get_contents($root . '/app/Core/UrlLocator/EcosistemaUrlLocatorLinkRepository.php');
if ($repoContent !== false && preg_match('/\b(INSERT|UPDATE|DELETE)\b\s+.*url_short_links/i', $repoContent) === 1) { fail('Repository contiene escrituras SQL sobre url_short_links', $criticalFailures); } else { ok('Repository sin INSERT/UPDATE/DELETE sobre url_short_links'); }

$viewLinks = @file_get_contents($root . '/resources/views/pages/url-locator/links.php');
if ($viewLinks !== false && str_contains($viewLinks, 'access_token_hash')) { warn('Vista links contiene texto access_token_hash (validar que no expone valores).', $warnings); } else { ok('Vista links no imprime access_token_hash'); }
if ($viewLinks !== false && str_contains($viewLinks, "target_url_preview")) { ok('Vista links usa preview seguro para target_url'); } else { warn('No se detectó target_url_preview en vista links.', $warnings); }

$viewDetail = @file_get_contents($root . '/resources/views/pages/url-locator/link-detail.php');
if ($routesContent !== false && str_contains($routesContent, '/url/locator/links/{id}')) { ok('routes/web.php contiene /url/locator/links/{id}'); } else { fail('routes/web.php no contiene /url/locator/links/{id}', $criticalFailures); }
if ($adapterContent !== false && str_contains($adapterContent, "'link_detail_read' => true") || ($adapterContent !== false && str_contains($adapterContent, "'link_detail_read'=>true"))) { ok('Adapter define link_detail_read true'); } else { fail('Adapter no define link_detail_read true', $criticalFailures); }
if ($repoContent !== false && preg_match('/\b(INSERT|UPDATE|DELETE)\b\s+.*url_clicks/i', $repoContent) === 1) { fail('Repository contiene escrituras SQL sobre url_clicks', $criticalFailures); } else { ok('Repository sin INSERT/UPDATE/DELETE sobre url_clicks'); }
if ($viewDetail !== false && str_contains($viewDetail, 'access_token_hash')) { warn('Vista detalle contiene texto access_token_hash (validar que no expone valor).', $warnings); } else { ok('Vista detalle no imprime access_token_hash'); }
if ($viewDetail !== false && str_contains($viewDetail, 'media_s3_key')) { warn('Vista detalle contiene texto media_s3_key (validar que no expone valor).', $warnings); } else { ok('Vista detalle no imprime media_s3_key'); }
if ($viewDetail !== false && str_contains($viewDetail, "['body_html']")) { fail('Vista detalle imprime body_html crudo', $criticalFailures); } else { ok('Vista detalle no imprime body_html crudo'); }
if ($viewDetail !== false && str_contains($viewDetail, "['ad_html']")) { fail('Vista detalle imprime ad_html crudo', $criticalFailures); } else { ok('Vista detalle no imprime ad_html crudo'); }

if ($criticalFailures > 0) {
    report('RESULT', "Smoke check finalizó con {$criticalFailures} fallos críticos y {$warnings} advertencias.");
    exit(1);
}

report('RESULT', "Smoke check OK con {$warnings} advertencias.");
exit(0);

$clickRepoContent = @file_get_contents($root . '/app/Core/UrlLocator/EcosistemaUrlLocatorClickRepository.php');
if ($clickRepoContent !== false && preg_match('/\b(INSERT|UPDATE|DELETE)\b\s+.*url_clicks/i', $clickRepoContent) === 1) { fail('Click repository contiene escrituras SQL sobre url_clicks', $criticalFailures); } else { ok('Click repository sin INSERT/UPDATE/DELETE sobre url_clicks'); }
$viewClicks = @file_get_contents($root . '/resources/views/pages/url-locator/clicks.php');
$viewLinkClicks = @file_get_contents($root . '/resources/views/pages/url-locator/link-clicks.php');
if ($viewClicks !== false && !str_contains($viewClicks, "['ip_address']")) { ok('Vista clicks no imprime ip_address crudo'); } else { fail('Vista clicks podría imprimir ip_address crudo', $criticalFailures); }
if ($viewClicks !== false && !str_contains($viewClicks, "['visitor_uuid']")) { ok('Vista clicks no imprime visitor_uuid crudo'); } else { fail('Vista clicks podría imprimir visitor_uuid crudo', $criticalFailures); }
if ($viewLinkClicks !== false && !str_contains($viewLinkClicks, "['ip_address']")) { ok('Vista link-clicks no imprime ip_address crudo'); } else { fail('Vista link-clicks podría imprimir ip_address crudo', $criticalFailures); }
if ($viewLinkClicks !== false && !str_contains($viewLinkClicks, "['visitor_uuid']")) { ok('Vista link-clicks no imprime visitor_uuid crudo'); } else { fail('Vista link-clicks podría imprimir visitor_uuid crudo', $criticalFailures); }


$routesContent = is_file($root . '/routes/web.php') ? (string) file_get_contents($root . '/routes/web.php') : '';
foreach (['GET /url/locator/links/new', 'POST /url/locator/links', 'GET /url/locator/links/{id}/edit', 'POST /url/locator/links/{id}/edit'] as $routeNeedle) {
    if (str_contains($routesContent, $routeNeedle)) { ok('Ruta URL Locator presente: ' . $routeNeedle); } else { fail('Falta ruta URL Locator: ' . $routeNeedle, $criticalFailures); }
}
if (!str_contains($routesContent, "tenant_id'] ??") && !str_contains($routesContent, 'auth_tenant_id')) { fail('No se detecta tenant de sesión en rutas URL Locator.', $criticalFailures); }
