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
    'resources/views/pages/cloud/drive-root.php',
    'docs/project/S3_DRIVE_SHARED_CONFIGURATION.md',
    'docs/project/ECOSISTEMA_DRIVE_CONFIGURATION.md',
    'config/s3_drive.php',
    'config/ecosistema_drive.php',
    'resources/views/pages/mail/settings.php',
    'resources/views/pages/cloud/settings.php',
    'resources/views/pages/cloud/drive-files.php',
    'resources/views/pages/cloud/drive-file-detail.php',
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

    $requiredEnvKeys = ['APP_DEBUG=', 'SESSION_SECURE=', 'DB_DATABASE=', 'MAIL_HOST=', 'MAIL_SEND_ENABLED=', 'MAIL_ALLOW_TEST_SEND=', 'AWS_BUCKET=', 'CLOUD_S3_ENABLED=', 'CLOUD_ALLOW_DOWNLOADS=', 'CLOUD_ALLOW_UPLOADS=', 'CLOUD_MAX_UPLOAD_MB=', 'CLOUD_ALLOWED_EXTENSIONS=', 'MAIL_MAX_ATTACHMENTS=', 'MAIL_MAX_ATTACHMENT_MB=', 'MAIL_MAX_TOTAL_ATTACHMENT_MB=', 'S3_DRIVE_ENABLED=', 'S3_DRIVE_MODE=', 'S3_DRIVE_BASE_URL=', 'S3_DRIVE_API_TIMEOUT=', 'S3_DRIVE_ALLOW_REMOTE_CALLS=', 'S3_DRIVE_ALLOW_SIGNED_URLS=', 'S3_DRIVE_ALLOW_REMOTE_UPLOADS=', 'S3_DRIVE_ALLOW_REMOTE_DOWNLOADS=', 'ECOSISTEMA_DRIVE_ENABLED=', 'ECOSISTEMA_DRIVE_MODE=', 'ECOSISTEMA_DRIVE_REFERENCE_REPO=', 'ECOSISTEMA_DRIVE_API_TIMEOUT=', 'ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=', 'ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS=', 'ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS=', 'ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS='];
    foreach ($requiredEnvKeys as $requiredEnvKey) {
        if ($envContent !== false && str_contains($envContent, $requiredEnvKey)) {
            ok('.env.example contiene variable clave: ' . rtrim($requiredEnvKey, '='));
        } else {
            fail('.env.example no contiene variable clave: ' . rtrim($requiredEnvKey, '='), $criticalFailures);
        }
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

    if ($routesContent !== false && str_contains($routesContent, "GET /cloud/drive/browse")) {
        ok('routes/web.php contiene ruta GET /cloud/drive/browse para navegación read-only de carpetas/archivos.');
    } else {
        fail('No se encontró ruta GET /cloud/drive/browse en routes/web.php.', $criticalFailures);
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

if ($secretHits === 0) {
    ok('Check de seguridad estática sin exposiciones sensibles en vistas/rutas.');
}

warn('Checks HTTP opcionales (manuales): php -S 127.0.0.1:8000 -t public && curl -I /login /dashboard /health/db', $warnings);

echo PHP_EOL;
echo 'Resumen: ' . PHP_EOL;
echo "- Críticos fallidos: {$criticalFailures}" . PHP_EOL;
echo "- Warnings: {$warnings}" . PHP_EOL;

exit($criticalFailures === 0 ? 0 : 1);


$smtpMailerFile = $root . '/app/Core/Mail/SmtpMailer.php';
if (is_file($smtpMailerFile)) {
    $smtpMailerContent = file_get_contents($smtpMailerFile);
    if ($smtpMailerContent !== false && str_contains($smtpMailerContent, 'buildMimeMessage')) { ok('SmtpMailer contiene soporte de adjuntos MIME.'); }
    else { fail('SmtpMailer no contiene soporte de adjuntos MIME.', $criticalFailures); }
}


$backupCheckPath = $root . '/scripts/backup-check.php';
if (is_file($backupCheckPath)) {
    ok('Existe script no destructivo de backup check: scripts/backup-check.php');
}

$driveAdapterFile = $root . '/app/Core/Cloud/EcosistemaDriveAdapter.php';
if (is_file($driveAdapterFile)) {
    $content = file_get_contents($driveAdapterFile);
    if ($content !== false && str_contains($content, 'read_folders_metadata')) {
        ok('EcosistemaDriveAdapter declara capability read_folders_metadata.');
    } else {
        fail('EcosistemaDriveAdapter no declara capability read_folders_metadata.', $criticalFailures);
    }
}

$driveAdapterFile = $root . '/app/Core/Cloud/EcosistemaDriveAdapter.php';
if (is_file($driveAdapterFile) && str_contains((string)file_get_contents($driveAdapterFile), "'read_user_root'")) {
    ok('EcosistemaDriveAdapter contiene capability read_user_root.');
} else {
    fail('EcosistemaDriveAdapter no contiene capability read_user_root.', $criticalFailures);
}
