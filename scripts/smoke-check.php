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
    'docs/project/ECOSISTEMA_URL_LOCATOR_REDIRECT_DRY_RUN.md',
    'resources/views/pages/url-locator/redirect-dry-run.php',
    'app/Core/UrlLocator/EcosistemaUrlLocatorRedirectDryRunService.php',
    'docs/project/ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECT.md',
    'resources/views/pages/url-locator/public-redirect-blocked.php',
    'app/Core/UrlLocator/EcosistemaUrlLocatorPublicRedirectService.php',

    'docs/project/ECOSISTEMA_LANDING_SCHEMA_INVENTORY.md',
    'docs/project/ECOSISTEMA_LANDING_PAGES_READ_ONLY.md',
    'app/Core/Landing/EcosistemaLandingAdapter.php',
    'app/Core/Landing/EcosistemaLandingPageRepository.php',
    'app/Core/Landing/EcosistemaLandingPageService.php',
    'app/Core/Landing/EcosistemaLandingVisitRepository.php',
    'app/Core/Landing/EcosistemaLandingVisitService.php',
    'docs/project/ECOSISTEMA_LANDING_FORMS_READ_ONLY.md',
    'resources/views/pages/landing/form-detail.php',
    'resources/views/pages/landing/page-forms.php',
    'resources/views/pages/landing/forms.php',
    'app/Core/Landing/EcosistemaLandingFormService.php',
    'app/Core/Landing/EcosistemaLandingFormRepository.php',
    'app/Core/Landing/EcosistemaLandingFormSubmitDryRunService.php',
    'app/Core/Landing/EcosistemaLandingFormSubmitRepository.php',
    'app/Core/Landing/EcosistemaLandingFormSubmitService.php',
    'resources/views/pages/landing/form-submit-dry-run.php',
    'docs/project/ECOSISTEMA_LANDING_FORM_SUBMIT_DRY_RUN.md',
    'resources/views/pages/landing/visits.php',
    'resources/views/pages/landing/page-visits.php',
    'docs/project/ECOSISTEMA_LANDING_VISITS_READ_ONLY.md',
    'docs/project/ECOSISTEMA_MAIL_NOTIFICATIONS_SCHEMA_INVENTORY.md',
    'docs/project/ECOSISTEMA_WORKFLOW_SCHEMA_INVENTORY.md',
    'docs/project/ECOSISTEMA_NOTIFICATION_TEMPLATES_READ_ONLY.md',
    'app/Core/MailNotifications/EcosistemaMailNotificationsAdapter.php',
    'app/Core/MailNotifications/EcosistemaNotificationTemplateRepository.php',
    'app/Core/MailNotifications/EcosistemaNotificationTemplateService.php',
    'app/Core/MailNotifications/EcosistemaUrlMessageTemplateRepository.php',
    'app/Core/MailNotifications/EcosistemaUrlMessageTemplateService.php',
    'docs/project/ECOSISTEMA_NOTIFICATIONS_QUEUE_READ_ONLY.md',
    'resources/views/pages/mail-notifications/queue-detail.php',
    'resources/views/pages/mail-notifications/queue.php',
    'app/Core/MailNotifications/EcosistemaNotificationQueueService.php',
    'app/Core/MailNotifications/EcosistemaNotificationQueueRepository.php',
    'resources/views/pages/mail-notifications/index.php',
    'resources/views/pages/mail-notifications/templates.php',
    'resources/views/pages/mail-notifications/template-detail.php',
    'resources/views/pages/mail-notifications/url-message-templates.php',
    'resources/views/pages/mail-notifications/url-message-template-detail.php',
    'docs/project/ECOSISTEMA_URL_MESSAGE_TEMPLATES_READ_ONLY.md',

    'app/Core/BrowserAnalytics/EcosistemaBrowserAnalyticsAdapter.php',
    'app/Core/BrowserAnalytics/EcosistemaBrowserAnalyticsDashboardRepository.php',
    'app/Core/BrowserAnalytics/EcosistemaBrowserAnalyticsDashboardService.php',
    'resources/views/pages/browser-analytics/dashboard.php',
    'docs/project/ECOSISTEMA_BROWSER_ANALYTICS_DASHBOARD_READ_ONLY.md',
    'docs/project/ECOSISTEMA_BROWSER_ANALYTICS_PAGEVIEWS_READ_ONLY.md',
    'resources/views/pages/browser-analytics/session-pageviews.php',
    'resources/views/pages/browser-analytics/pageviews.php',
    'app/Core/BrowserAnalytics/EcosistemaBrowserAnalyticsPageviewService.php',
    'app/Core/BrowserAnalytics/EcosistemaBrowserAnalyticsPageviewRepository.php',
    'app/Core/BrowserAnalytics/EcosistemaBrowserAnalyticsEventRepository.php',
    'app/Core/BrowserAnalytics/EcosistemaBrowserAnalyticsEventService.php',
    'resources/views/pages/browser-analytics/events.php',
    'resources/views/pages/browser-analytics/pageview-events.php',
    'docs/project/ECOSISTEMA_BROWSER_ANALYTICS_EVENTS_READ_ONLY.md',
    'docs/project/ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_DRY_RUN.md',
    'resources/views/pages/browser-analytics/collector-dry-run.php',
    'app/Core/BrowserAnalytics/EcosistemaBrowserAnalyticsCollectorDryRunService.php',
    'resources/views/pages/landing/index.php',
    'resources/views/pages/landing/pages.php',
    'resources/views/pages/landing/page-detail.php',
    'app/Core/Crm/EcosistemaCrmLeadRepository.php',
    'app/Core/Crm/EcosistemaCrmLeadService.php',
    'resources/views/pages/crm/leads.php',
    'resources/views/pages/crm/lead-detail.php',
    'docs/project/ECOSISTEMA_CRM_LEAD_DETAIL.md',
    'docs/project/ECOSISTEMA_CRM_LEADS_READ_ONLY.md',
];

foreach ($requiredFiles as $requiredFile) {
    checkFile($root, $requiredFile, $criticalFailures);
}

$leadRepositoryPath = $root . '/app/Core/Crm/EcosistemaCrmLeadRepository.php';
if (is_file($leadRepositoryPath)) {
    $leadRepositoryContent = (string) file_get_contents($leadRepositoryPath);
    if (preg_match('/\b(INSERT|UPDATE|DELETE)\b/i', $leadRepositoryContent) === 1) {
        fail('Repositorio CRM leads contiene escritura no permitida.', $criticalFailures);
    } else {
        ok('Repositorio CRM leads no contiene INSERT/UPDATE/DELETE.');
    }
}

$leadViewPath = $root . '/resources/views/pages/crm/leads.php';
if (is_file($leadViewPath)) {
    $leadViewContent = (string) file_get_contents($leadViewPath);
    if (str_contains($leadViewContent, "['email']") || str_contains($leadViewContent, "['phone']") || str_contains($leadViewContent, "['contact_name']")) {
        fail('Vista CRM leads imprime campos sensibles crudos.', $criticalFailures);
    } else {
        ok('Vista CRM leads usa previews para campos sensibles.');
    }
}



$leadDetailViewPath = $root . '/resources/views/pages/crm/lead-detail.php';
if (is_file($leadDetailViewPath)) {
    $leadDetailContent = (string) file_get_contents($leadDetailViewPath);
    if (str_contains($leadDetailContent, "['email']") || str_contains($leadDetailContent, "['phone']") || str_contains($leadDetailContent, "['contact_name']") || str_contains($leadDetailContent, 'raw_data_json') || str_contains($leadDetailContent, 'value_json') || str_contains($leadDetailContent, 'metadata_json')) {
        fail('Vista CRM lead detail imprime PII completa o JSON crudo.', $criticalFailures);
    } else {
        ok('Vista CRM lead detail evita PII completa y JSON crudo.');
    }
}

$notificationTemplateRepositoryPath = $root . '/app/Core/MailNotifications/EcosistemaNotificationTemplateRepository.php';
if (is_file($notificationTemplateRepositoryPath)) {
    $notificationTemplateRepositoryContent = (string) file_get_contents($notificationTemplateRepositoryPath);
    if (preg_match('/\b(INSERT|UPDATE|DELETE)\b/i', $notificationTemplateRepositoryContent) === 1) {
        fail('Repositorio de notification templates contiene escritura no permitida.', $criticalFailures);
    } else {
        ok('Repositorio de notification templates no contiene INSERT/UPDATE/DELETE.');
    }
}

$notificationTemplateDetailViewPath = $root . '/resources/views/pages/mail-notifications/template-detail.php';
if (is_file($notificationTemplateDetailViewPath)) {
    $notificationTemplateDetailContent = (string) file_get_contents($notificationTemplateDetailViewPath);
    if (str_contains($notificationTemplateDetailContent, "['variables_json']")) {
        fail('Vista template-detail imprime variables_json crudo.', $criticalFailures);
    } else {
        ok('Vista template-detail no imprime variables_json crudo.');
    }
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

if ($envContent !== false && str_contains($envContent, 'ECOSISTEMA_BROWSER_ANALYTICS_ENABLED=false') && str_contains($envContent, 'ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_DRY_RUN=false') && str_contains($envContent, 'ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_WRITE=false') && str_contains($envContent, 'ECOSISTEMA_BROWSER_ANALYTICS_COLLECT_IP=false')) {
    ok('.env.example mantiene flags Browser Analytics collector en false.');
} else {
    fail('.env.example no mantiene flags Browser Analytics collector esperados en false.', $criticalFailures);
}
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


$workflowInventoryPath = $root . '/docs/project/ECOSISTEMA_WORKFLOW_SCHEMA_INVENTORY.md';
if (is_file($workflowInventoryPath)) {
    ok('Existe inventario Workflow canónico.');
} else {
    fail('No existe docs/project/ECOSISTEMA_WORKFLOW_SCHEMA_INVENTORY.md', $criticalFailures);
}

$readmeContent = is_file($root . '/README.md') ? file_get_contents($root . '/README.md') : false;
if ($readmeContent !== false && str_contains($readmeContent, 'ECOSISTEMA_WORKFLOW_SCHEMA_INVENTORY.md')) {
    ok('README.md referencia inventario Workflow.');
} else {
    fail('README.md no referencia ECOSISTEMA_WORKFLOW_SCHEMA_INVENTORY.md', $criticalFailures);
}

$workflowInventoryContent = is_file($workflowInventoryPath) ? file_get_contents($workflowInventoryPath) : false;
foreach (['workflow_rules', 'workflow_actions', 'workflow_runs', 'workflow_run_logs', 'module_workflow_links', 'adbbmis1_eco'] as $requiredMention) {
    if ($workflowInventoryContent !== false && str_contains($workflowInventoryContent, $requiredMention)) {
        ok('Inventario Workflow menciona: ' . $requiredMention);
    } else {
        fail('Inventario Workflow no menciona: ' . $requiredMention, $criticalFailures);
    }
}

$workflowFiles = [
    'app/Core/Workflow/EcosistemaWorkflowAdapter.php',
    'app/Core/Workflow/EcosistemaWorkflowRuleRepository.php',
    'app/Core/Workflow/EcosistemaWorkflowRuleService.php',
    'app/Core/Workflow/EcosistemaWorkflowRunRepository.php',
    'app/Core/Workflow/EcosistemaWorkflowRunService.php',
    'resources/views/pages/workflow/index.php',
    'resources/views/pages/workflow/rules.php',
    'resources/views/pages/workflow/rule-detail.php',
    'resources/views/pages/workflow/runs.php',
    'resources/views/pages/workflow/run-detail.php',
    'resources/views/pages/workflow/rule-runs.php',
    'docs/project/ECOSISTEMA_WORKFLOW_RULES_READ_ONLY.md',
    'docs/project/ECOSISTEMA_WORKFLOW_RUNS_READ_ONLY.md',
];
foreach ($workflowFiles as $workflowFile) {
    if (is_file($root . '/' . $workflowFile)) { ok('Existe archivo Workflow: ' . $workflowFile); } else { fail('No existe archivo Workflow: ' . $workflowFile, $criticalFailures); }
}

foreach (['GET /workflow', 'GET /workflow/rules', 'GET /workflow/rules/{id}', 'GET /workflow/runs', 'GET /workflow/runs/{id}', 'GET /workflow/rules/{id}/runs'] as $requiredRoute) {
    if ($routesContent !== false && str_contains($routesContent, $requiredRoute)) { ok('routes/web.php contiene ruta Workflow: ' . $requiredRoute); } else { fail('Falta ruta Workflow: ' . $requiredRoute, $criticalFailures); }
}

$workflowAdapterContent = is_file($root . '/app/Core/Workflow/EcosistemaWorkflowAdapter.php') ? file_get_contents($root . '/app/Core/Workflow/EcosistemaWorkflowAdapter.php') : false;
foreach (["'rules_read' => true", "'runs_read' => true", "'run_logs_read' => true", "'execution_write' => false", "'retry_enabled' => false", "'action_execution' => false"] as $requiredFlag) {
    if ($workflowAdapterContent !== false && str_contains($workflowAdapterContent, $requiredFlag)) { ok('Adapter Workflow contiene ' . $requiredFlag . '.'); } else { fail('Adapter Workflow no contiene ' . $requiredFlag . '.', $criticalFailures); }
}

if (is_string($gitDiff) && preg_match('/^\+.*(INSERT|UPDATE|DELETE).*workflow_(rules|actions|runs|run_logs)/mi', $gitDiff) === 1) {
    fail('Se detectó escritura SQL sobre tablas workflow_* en cambios del PR.', $criticalFailures);
} else {
    ok('Sin escrituras SQL sobre tablas workflow_* en cambios del PR.');
}


$workflowViews = [
    $root . '/resources/views/pages/workflow/index.php',
    $root . '/resources/views/pages/workflow/rules.php',
    $root . '/resources/views/pages/workflow/rule-detail.php',
    $root . '/resources/views/pages/workflow/runs.php',
    $root . '/resources/views/pages/workflow/run-detail.php',
    $root . '/resources/views/pages/workflow/rule-runs.php',
];
foreach ($workflowViews as $workflowView) {
    $content = is_file($workflowView) ? file_get_contents($workflowView) : false;
    if ($content !== false && (str_contains($content, 'conditions_json') || str_contains($content, 'config_json') || str_contains($content, 'input_json') || str_contains($content, 'output_json') || str_contains($content, 'context_json') || str_contains($content, 'metadata_json')) && !str_contains($content, '_present') && !str_contains($content, '_exposed')) {
        fail('Vista Workflow parece exponer JSON crudo: ' . basename($workflowView), $criticalFailures);
    } else {
        ok('Vista Workflow sin exposición de JSON crudo: ' . basename($workflowView));
    }
}
$landingInventoryPath = $root . '/docs/project/ECOSISTEMA_LANDING_SCHEMA_INVENTORY.md';
if (is_file($landingInventoryPath)) {
    ok('Existe inventario Landing Pages.');
} else {
    fail('No existe docs/project/ECOSISTEMA_LANDING_SCHEMA_INVENTORY.md', $criticalFailures);
}

$readmeContent = is_file($root . '/README.md') ? file_get_contents($root . '/README.md') : false;
if ($readmeContent !== false && str_contains($readmeContent, 'ECOSISTEMA_LANDING_SCHEMA_INVENTORY.md')) {
    ok('README.md referencia inventario Landing Pages.');
} else {
    fail('README.md no referencia ECOSISTEMA_LANDING_SCHEMA_INVENTORY.md', $criticalFailures);
}

$landingInventoryContent = is_file($landingInventoryPath) ? file_get_contents($landingInventoryPath) : false;
foreach (['landing_pages', 'landing_visits', 'landing_forms', 'landing_form_submissions', 'adbbmis1_eco', 'mailit-click'] as $requiredMention) {
    if ($landingInventoryContent !== false && str_contains($landingInventoryContent, $requiredMention)) {
        ok('Inventario Landing menciona: ' . $requiredMention);
    } else {
        fail('Inventario Landing no menciona: ' . $requiredMention, $criticalFailures);
    }
}

$routesContent = is_file($root . '/routes/web.php') ? file_get_contents($root . '/routes/web.php') : false;
foreach (['GET /landing/visits', 'GET /landing/pages/{id}/visits'] as $requiredRoute) {
    if ($routesContent !== false && str_contains($routesContent, $requiredRoute)) {
        ok('routes/web.php contiene ruta: ' . $requiredRoute);
    } else {
        fail('routes/web.php no contiene ruta: ' . $requiredRoute, $criticalFailures);
    }
}

$adapterContent = is_file($root . '/app/Core/Landing/EcosistemaLandingAdapter.php') ? file_get_contents($root . '/app/Core/Landing/EcosistemaLandingAdapter.php') : false;
if ($adapterContent !== false && str_contains($adapterContent, "'visits_read' => true")) {
    ok('Adapter Landing habilita visits_read=true.');
} else {
    fail('Adapter Landing no habilita visits_read=true.', $criticalFailures);
}
if ($adapterContent !== false && str_contains($adapterContent, "'visit_tracking_write' => false")) {
    ok('Adapter Landing mantiene visit_tracking_write=false.');
} else {
    fail('Adapter Landing no mantiene visit_tracking_write=false.', $criticalFailures);
}
if (is_string($gitDiff) && preg_match('/^\+.*\b(INSERT|UPDATE|DELETE)\b.*\blanding_/mi', $gitDiff) === 1) {
    fail('Se detectó escritura SQL sobre tablas landing_* en cambios del PR.', $criticalFailures);
} else {
    ok('Sin escrituras SQL sobre tablas landing_* en cambios del PR.');
}


$landingVisitsView = is_file($root . '/resources/views/pages/landing/visits.php') ? file_get_contents($root . '/resources/views/pages/landing/visits.php') : false;
$pageVisitsView = is_file($root . '/resources/views/pages/landing/page-visits.php') ? file_get_contents($root . '/resources/views/pages/landing/page-visits.php') : false;
$combinedVisitsViews = ($landingVisitsView ?: '') . "
" . ($pageVisitsView ?: '');
if (str_contains($combinedVisitsViews, "['ip_address']") || str_contains($combinedVisitsViews, "['visitor_uuid']") || str_contains($combinedVisitsViews, "['session_uuid']")) {
    fail('Vistas Landing Visits exponen campos sensibles crudos (ip/uuid).', $criticalFailures);
} else {
    ok('Vistas Landing Visits no exponen ip/visitor_uuid/session_uuid crudos.');
}


$browserInventoryPath = $root . '/docs/project/ECOSISTEMA_BROWSER_ANALYTICS_SCHEMA_INVENTORY.md';
if (is_file($browserInventoryPath)) {
    ok('Existe inventario Browser Analytics.');
} else {
    fail('No existe docs/project/ECOSISTEMA_BROWSER_ANALYTICS_SCHEMA_INVENTORY.md', $criticalFailures);
}

$readmeContent = is_file($root . '/README.md') ? file_get_contents($root . '/README.md') : false;
if ($readmeContent !== false && str_contains($readmeContent, 'ECOSISTEMA_BROWSER_ANALYTICS_SCHEMA_INVENTORY.md')) {
    ok('README.md referencia inventario Browser Analytics.');
} else {
    fail('README.md no referencia ECOSISTEMA_BROWSER_ANALYTICS_SCHEMA_INVENTORY.md', $criticalFailures);
}

$browserInventoryContent = is_file($browserInventoryPath) ? file_get_contents($browserInventoryPath) : false;
foreach (['browser_analytics_sessions', 'browser_analytics_pageviews', 'browser_analytics_events', 'browser_analytics_attribution'] as $requiredMention) {
    if ($browserInventoryContent !== false && str_contains($browserInventoryContent, $requiredMention)) {
        ok('Inventario Browser Analytics menciona: ' . $requiredMention);
    } else {
        fail('Inventario Browser Analytics no menciona: ' . $requiredMention, $criticalFailures);
    }
}

if (is_string($gitDiff) && preg_match('#^\+.*(/browser/analytics|GET /browser/analytics|POST /browser/analytics)#mi', $gitDiff) === 1) {
    fail('Se detectó posible ruta funcional /browser/analytics en cambios del PR.', $criticalFailures);
} else {
    ok('No se detectaron rutas funcionales /browser/analytics en cambios del PR.');
}

if (is_string($gitDiff) && preg_match('/^\+.*(INSERT|UPDATE|DELETE).*browser_analytics_/mi', $gitDiff) === 1) {
    fail('Se detectó escritura SQL sobre browser_analytics_* en cambios del PR.', $criticalFailures);
} else {
    ok('Sin escrituras SQL sobre browser_analytics_* en cambios del PR.');
}


$queueRepositoryContent = is_file($root . '/app/Core/MailNotifications/EcosistemaNotificationQueueRepository.php') ? (string) file_get_contents($root . '/app/Core/MailNotifications/EcosistemaNotificationQueueRepository.php') : '';
if (preg_match('/\b(INSERT|UPDATE|DELETE)\b/i', $queueRepositoryContent) === 1) { fail('Repositorio notifications_queue contiene escritura no permitida.', $criticalFailures); } else { ok('Repositorio notifications_queue no contiene INSERT/UPDATE/DELETE.'); }

$queueViews = (is_file($root . '/resources/views/pages/mail-notifications/queue.php') ? (string) file_get_contents($root . '/resources/views/pages/mail-notifications/queue.php') : '') . "
" . (is_file($root . '/resources/views/pages/mail-notifications/queue-detail.php') ? (string) file_get_contents($root . '/resources/views/pages/mail-notifications/queue-detail.php') : '');
if (str_contains($queueViews, "['payload_json']")) { fail('Vistas de notifications queue imprimen payload_json crudo.', $criticalFailures); } else { ok('Vistas de notifications queue no imprimen payload_json crudo.'); }

if (str_contains($routesContent, 'GET /mail-notifications/queue')) { ok('Ruta notifications queue detectada: GET /mail-notifications/queue'); } else { fail('Falta ruta notifications queue: GET /mail-notifications/queue', $criticalFailures); }
if (str_contains($routesContent, 'GET /mail-notifications/queue/{id}')) { ok('Ruta notifications queue detectada: GET /mail-notifications/queue/{id}'); } else { fail('Falta ruta notifications queue: GET /mail-notifications/queue/{id}', $criticalFailures); }

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

if ($routesContent !== false && str_contains($routesContent, '/url/locator/links/{id}/redirect-dry-run')) { ok('routes/web.php contiene /url/locator/links/{id}/redirect-dry-run'); } else { fail('routes/web.php no contiene /url/locator/links/{id}/redirect-dry-run', $criticalFailures); }
if ($adapterContent !== false && str_contains($adapterContent, "'redirects_dry_run' => true")) { ok('Adapter define redirects_dry_run true'); } else { fail('Adapter no define redirects_dry_run true', $criticalFailures); }
if ($adapterContent !== false && str_contains($adapterContent, "'public_redirects' => false")) { ok('Adapter mantiene public_redirects false'); } else { fail('Adapter no mantiene public_redirects false', $criticalFailures); }
if ($adapterContent !== false && str_contains($adapterContent, "'click_tracking_write' => false")) { ok('Adapter mantiene click_tracking_write false'); } else { fail('Adapter no mantiene click_tracking_write false', $criticalFailures); }
$dryRunServiceContent = @file_get_contents($root . '/app/Core/UrlLocator/EcosistemaUrlLocatorRedirectDryRunService.php');
if ($dryRunServiceContent !== false && preg_match('/\bINSERT\s+INTO\s+url_clicks\b/i', $dryRunServiceContent) !== 1) { ok('Dry-run service sin INSERT INTO url_clicks'); } else { fail('Dry-run service no debe insertar en url_clicks', $criticalFailures); }
if ($dryRunServiceContent !== false && preg_match('/\bUPDATE\s+url_short_links\s+SET\s+click_count\b/i', $dryRunServiceContent) !== 1) { ok('Dry-run service sin UPDATE click_count'); } else { fail('Dry-run service no debe actualizar click_count', $criticalFailures); }
if ($dryRunServiceContent !== false && !str_contains($dryRunServiceContent, "header('Location')")) { ok('Dry-run service sin redirección real'); } else { fail('Dry-run service contiene redirección real', $criticalFailures); }
$dryRunViewContent = @file_get_contents($root . '/resources/views/pages/url-locator/redirect-dry-run.php');
if ($dryRunViewContent !== false && !str_contains($dryRunViewContent, 'access_token_hash')) { ok('Vista dry-run no imprime access_token_hash'); } else { fail('Vista dry-run no debe imprimir access_token_hash', $criticalFailures); }
if ($dryRunViewContent !== false && !str_contains($dryRunViewContent, "['target_url']")) { ok('Vista dry-run no imprime target_url crudo'); } else { fail('Vista dry-run no debe imprimir target_url crudo', $criticalFailures); }
if ($adapterContent !== false && str_contains($adapterContent, "'link_detail_read' => true") || ($adapterContent !== false && str_contains($adapterContent, "'link_detail_read'=>true"))) { ok('Adapter define link_detail_read true'); } else { fail('Adapter no define link_detail_read true', $criticalFailures); }
if ($repoContent !== false && preg_match('/\b(INSERT|UPDATE|DELETE)\b\s+.*url_clicks/i', $repoContent) === 1) { fail('Repository contiene escrituras SQL sobre url_clicks', $criticalFailures); } else { ok('Repository sin INSERT/UPDATE/DELETE sobre url_clicks'); }
if ($viewDetail !== false && str_contains($viewDetail, 'access_token_hash')) { warn('Vista detalle contiene texto access_token_hash (validar que no expone valor).', $warnings); } else { ok('Vista detalle no imprime access_token_hash'); }
if ($viewDetail !== false && str_contains($viewDetail, 'media_s3_key')) { warn('Vista detalle contiene texto media_s3_key (validar que no expone valor).', $warnings); } else { ok('Vista detalle no imprime media_s3_key'); }
if ($viewDetail !== false && str_contains($viewDetail, "['body_html']")) { fail('Vista detalle imprime body_html crudo', $criticalFailures); } else { ok('Vista detalle no imprime body_html crudo'); }
if ($viewDetail !== false && str_contains($viewDetail, "['ad_html']")) { fail('Vista detalle imprime ad_html crudo', $criticalFailures); } else { ok('Vista detalle no imprime ad_html crudo'); }


$landingVisitsView = is_file($root . '/resources/views/pages/landing/visits.php') ? file_get_contents($root . '/resources/views/pages/landing/visits.php') : false;
$pageVisitsView = is_file($root . '/resources/views/pages/landing/page-visits.php') ? file_get_contents($root . '/resources/views/pages/landing/page-visits.php') : false;
$combinedVisitsViews = ($landingVisitsView ?: '') . "
" . ($pageVisitsView ?: '');
if (str_contains($combinedVisitsViews, "['ip_address']") || str_contains($combinedVisitsViews, "['visitor_uuid']") || str_contains($combinedVisitsViews, "['session_uuid']")) {
    fail('Vistas Landing Visits exponen campos sensibles crudos (ip/uuid).', $criticalFailures);
} else {
    ok('Vistas Landing Visits no exponen ip/visitor_uuid/session_uuid crudos.');
}



$mailNotificationsPaths = [
    'app/Core/Mail',
    'app/Core/UrlLocator',
    'routes/web.php',
];

$writePatterns = [
    '/\b(INSERT\s+INTO|UPDATE|DELETE\s+FROM)\s+(mail_|notifications_|url_message_)/i',
    '/\b(INSERT\s+INTO|UPDATE|DELETE\s+FROM)\s+url_attachment_access_logs\b/i',
];

foreach ($mailNotificationsPaths as $relativePath) {
    $absolutePath = $root . '/' . $relativePath;
    if (!file_exists($absolutePath)) {
        continue;
    }

    $iterator = is_dir($absolutePath)
        ? new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absolutePath, FilesystemIterator::SKIP_DOTS))
        : new ArrayIterator([new SplFileInfo($absolutePath)]);

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        if (pathinfo($file->getFilename(), PATHINFO_EXTENSION) !== 'php') {
            continue;
        }

        $content = (string) file_get_contents($file->getPathname());
        foreach ($writePatterns as $pattern) {
            if (preg_match($pattern, $content) === 1) {
                $relative = str_replace($root . '/', '', $file->getPathname());
                fail("Se detectó escritura SQL no permitida para Mail/Notifications/URL Message en {$relative}.", $criticalFailures);
                continue 3;
            }
        }
    }
}

ok('No se detectaron escrituras SQL sobre mail_/notifications_/url_message_* en rutas verificadas.');

$mailAdapterContent = is_file($root . '/app/Core/MailNotifications/EcosistemaMailNotificationsAdapter.php') ? (string) file_get_contents($root . '/app/Core/MailNotifications/EcosistemaMailNotificationsAdapter.php') : '';
if (str_contains($mailAdapterContent, "'url_message_templates_read' => true")) { ok('Mail Notifications adapter habilita url_message_templates_read=true.'); } else { fail('Mail Notifications adapter no habilita url_message_templates_read=true.', $criticalFailures); }
if (str_contains($mailAdapterContent, "'preview_dry_run' => true")) { ok('Mail Notifications adapter habilita preview_dry_run=true.'); } else { fail('Mail Notifications adapter no habilita preview_dry_run=true.', $criticalFailures); }
if (str_contains($mailAdapterContent, "'send_write' => false")) { ok('Mail Notifications adapter mantiene send_write=false.'); } else { fail('Mail Notifications adapter no mantiene send_write=false.', $criticalFailures); }
if (str_contains($mailAdapterContent, "'queue_read' => true")) { ok('Mail Notifications adapter habilita queue_read=true.'); } else { fail('Mail Notifications adapter no habilita queue_read=true.', $criticalFailures); }
if (str_contains($mailAdapterContent, "'smtp_connection' => false")) { ok('Mail Notifications adapter mantiene smtp_connection=false.'); } else { fail('Mail Notifications adapter no mantiene smtp_connection=false.', $criticalFailures); }

foreach ([
    'app/Core/MailNotifications/EcosistemaMessagePreviewDryRunService.php',
    'resources/views/pages/mail-notifications/message-preview-dry-run.php',
    'docs/project/ECOSISTEMA_MESSAGE_PREVIEW_DRY_RUN.md',
] as $requiredPath) {
    if (is_file($root . '/' . $requiredPath)) { ok('Existe artefacto dry-run mail notifications: ' . $requiredPath); }
    else { fail('Falta artefacto dry-run mail notifications: ' . $requiredPath, $criticalFailures); }
}

$routesContent = is_file($root . '/routes/web.php') ? (string) file_get_contents($root . '/routes/web.php') : '';
foreach ([
    'GET /mail-notifications/templates/{id}/preview-dry-run',
    'POST /mail-notifications/templates/{id}/preview-dry-run',
    'GET /mail-notifications/url-message-templates/{id}/preview-dry-run',
    'POST /mail-notifications/url-message-templates/{id}/preview-dry-run',
] as $routeNeedle) {
    if (str_contains($routesContent, $routeNeedle)) { ok('Ruta preview dry-run detectada: ' . $routeNeedle); }
    else { fail('Falta ruta preview dry-run: ' . $routeNeedle, $criticalFailures); }
}

$urlTemplatesView = is_file($root . '/resources/views/pages/mail-notifications/url-message-templates.php') ? (string) file_get_contents($root . '/resources/views/pages/mail-notifications/url-message-templates.php') : '';
$urlTemplateDetailView = is_file($root . '/resources/views/pages/mail-notifications/url-message-template-detail.php') ? (string) file_get_contents($root . '/resources/views/pages/mail-notifications/url-message-template-detail.php') : '';
$combinedUrlTemplateViews = $urlTemplatesView . "\n" . $urlTemplateDetailView;
if (str_contains($combinedUrlTemplateViews, "['s3_key']")) { fail('Vistas URL message templates exponen s3_key crudo.', $criticalFailures); } else { ok('Vistas URL message templates no exponen s3_key crudo.'); }
if (str_contains($combinedUrlTemplateViews, "['file_path']")) { fail('Vistas URL message templates exponen file_path crudo.', $criticalFailures); } else { ok('Vistas URL message templates no exponen file_path crudo.'); }
if (str_contains($combinedUrlTemplateViews, "['body_html']")) { fail('Vistas URL message templates exponen body_html crudo.', $criticalFailures); } else { ok('Vistas URL message templates no exponen body_html crudo.'); }


$queueRepositoryContent = is_file($root . '/app/Core/MailNotifications/EcosistemaNotificationQueueRepository.php') ? (string) file_get_contents($root . '/app/Core/MailNotifications/EcosistemaNotificationQueueRepository.php') : '';
if (preg_match('/\b(INSERT|UPDATE|DELETE)\b/i', $queueRepositoryContent) === 1) { fail('Repositorio notifications_queue contiene escritura no permitida.', $criticalFailures); } else { ok('Repositorio notifications_queue no contiene INSERT/UPDATE/DELETE.'); }

$queueViews = (is_file($root . '/resources/views/pages/mail-notifications/queue.php') ? (string) file_get_contents($root . '/resources/views/pages/mail-notifications/queue.php') : '') . "
" . (is_file($root . '/resources/views/pages/mail-notifications/queue-detail.php') ? (string) file_get_contents($root . '/resources/views/pages/mail-notifications/queue-detail.php') : '');
if (str_contains($queueViews, "['payload_json']")) { fail('Vistas de notifications queue imprimen payload_json crudo.', $criticalFailures); } else { ok('Vistas de notifications queue no imprimen payload_json crudo.'); }

if (str_contains($routesContent, 'GET /mail-notifications/queue')) { ok('Ruta notifications queue detectada: GET /mail-notifications/queue'); } else { fail('Falta ruta notifications queue: GET /mail-notifications/queue', $criticalFailures); }
if (str_contains($routesContent, 'GET /mail-notifications/queue/{id}')) { ok('Ruta notifications queue detectada: GET /mail-notifications/queue/{id}'); } else { fail('Falta ruta notifications queue: GET /mail-notifications/queue/{id}', $criticalFailures); }

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


$routesContent = @file_get_contents($root . '/routes/web.php') ?: '';
if (str_contains($routesContent, "GET /u/{slug}")) { ok('routes/web.php contiene ruta pública /u/{slug}.'); } else { fail('routes/web.php no contiene ruta pública /u/{slug}.', $criticalFailures); }

$publicService = @file_get_contents($root . '/app/Core/UrlLocator/EcosistemaUrlLocatorPublicRedirectService.php') ?: '';
if (!str_contains($publicService, "target_url'] ??") && !str_contains($publicService, '$_GET[\'target_url\']')) { ok('PublicRedirectService no acepta target_url desde request.'); } else { fail('PublicRedirectService acepta target_url desde request.', $criticalFailures); }
if (!str_contains($publicService, "tenant_id'] ??") && !str_contains($publicService, '$_GET[\'tenant_id\']')) { ok('PublicRedirectService no acepta tenant_id desde request.'); } else { fail('PublicRedirectService acepta tenant_id desde request.', $criticalFailures); }


$routesFile = $root . '/routes/web.php';
if (is_file($routesFile)) {
    $routesContent = (string) file_get_contents($routesFile);
    foreach (['GET /landing/forms', 'GET /landing/pages/{id}/forms', 'GET /landing/forms/{id}'] as $routeNeedle) {
        if (str_contains($routesContent, $routeNeedle)) { ok('Ruta landing forms detectada: ' . $routeNeedle); } else { fail('Falta ruta landing forms: ' . $routeNeedle, $criticalFailures); }
    }
}
$adapterFile = $root . '/app/Core/Landing/EcosistemaLandingAdapter.php';
if (is_file($adapterFile)) {
    $adapterContent = (string) file_get_contents($adapterFile);
    if (str_contains($adapterContent, "'forms_read' => true")) { ok('Adapter habilita forms_read=true.'); } else { fail('Adapter no tiene forms_read=true.', $criticalFailures); }
    if (str_contains($adapterContent, "'form_submit_write' => false")) { ok('Adapter mantiene form_submit_write=false.'); } else { fail('Adapter no mantiene form_submit_write=false.', $criticalFailures); }
}
foreach (['app/Core/Landing/EcosistemaLandingFormRepository.php','app/Core/Landing/EcosistemaLandingFormService.php'] as $landingFile) {
    $full = $root . '/' . $landingFile;
    if (is_file($full)) {
        $content = (string) file_get_contents($full);
        if (preg_match('/\b(INSERT|UPDATE|DELETE)\b/i', $content) === 1) { fail('Se detectó SQL de escritura en ' . $landingFile, $criticalFailures); } else { ok('Sin SQL de escritura en ' . $landingFile); }
    }
}
foreach (['resources/views/pages/landing/forms.php','resources/views/pages/landing/page-forms.php','resources/views/pages/landing/form-detail.php'] as $viewFile) {
    $full = $root . '/' . $viewFile;
    if (is_file($full)) {
        $content = (string) file_get_contents($full);
        if (str_contains($content, 'options_json') || str_contains($content, 'validation_json')) { warn('Verifica exposición controlada en ' . $viewFile, $warnings); } else { ok('Vista sin impresión directa options_json/validation_json: ' . $viewFile); }
    }
}

// PR #92 landing submissions read-only checks
$landingSubFiles = [
    'app/Core/Landing/EcosistemaLandingSubmissionRepository.php',
    'app/Core/Landing/EcosistemaLandingSubmissionService.php',
    'resources/views/pages/landing/submissions.php',
    'resources/views/pages/landing/form-submissions.php',
    'resources/views/pages/landing/page-submissions.php',
    'resources/views/pages/landing/submission-detail.php',
    'docs/project/ECOSISTEMA_LANDING_SUBMISSIONS_READ_ONLY.md',
];
foreach ($landingSubFiles as $file) { checkFile($root, $file, $criticalFailures); }

$routesContent = @file_get_contents($root . '/routes/web.php') ?: '';
foreach (['/landing/submissions', '/landing/forms/{id}/submissions', '/landing/pages/{id}/submissions', '/landing/submissions/{id}'] as $requiredRoute) {
    if (str_contains($routesContent, $requiredRoute)) { ok('Ruta landing submissions presente: ' . $requiredRoute); }
    else { fail('Ruta landing submissions faltante: ' . $requiredRoute, $criticalFailures); }
}
if (str_contains($routesContent, "'submissions_read' => true")) { ok('Adapter habilita submissions_read=true.'); }
else { fail('Adapter no habilita submissions_read=true.', $criticalFailures); }
foreach (["'form_submit_write' => false", "'crm_lead_write' => false"] as $flag) {
    if (str_contains($routesContent . (@file_get_contents($root . '/app/Core/Landing/EcosistemaLandingAdapter.php') ?: ''), $flag)) { ok('Bandera preservada: ' . $flag); }
    else { fail('Bandera faltante: ' . $flag, $criticalFailures); }
}

$submissionRepoContent = @file_get_contents($root . '/app/Core/Landing/EcosistemaLandingSubmissionRepository.php') ?: '';
foreach (['INSERT INTO landing_form_submissions', 'UPDATE landing_form_submissions', 'DELETE FROM landing_form_submissions', 'INSERT INTO landing_form_submission_values', 'UPDATE landing_form_submission_values', 'DELETE FROM landing_form_submission_values'] as $forbiddenSql) {
    if (stripos($submissionRepoContent, $forbiddenSql) === false) { ok('Sin escritura SQL prohibida: ' . $forbiddenSql); }
    else { fail('Se encontró SQL prohibido: ' . $forbiddenSql, $criticalFailures); }
}

$viewsSensitive = [
    'resources/views/pages/landing/submissions.php',
    'resources/views/pages/landing/form-submissions.php',
    'resources/views/pages/landing/page-submissions.php',
    'resources/views/pages/landing/submission-detail.php',
];
foreach ($viewsSensitive as $viewFile) {
    $content = @file_get_contents($root . '/' . $viewFile) ?: '';
    foreach (['raw_data_json', 'value_json', 's3_key', 'file_path'] as $sensitive) {
        if (str_contains($content, $sensitive) && !str_contains($content, 'hidden')) {
            warn('Revisar uso de campo sensible en vista: ' . $viewFile . ' -> ' . $sensitive, $warnings);
        }
    }
}


$routesContent = @file_get_contents($root . '/routes/web.php');
if ($routesContent === false || !str_contains($routesContent, "'GET /browser/analytics'")) {
    fail("No existe ruta GET /browser/analytics en routes/web.php.", $criticalFailures);
} else {
    ok("Existe ruta GET /browser/analytics en routes/web.php.");
}

foreach (["'GET /browser/analytics/events'", "'GET /browser/analytics/pageviews/{id}/events'"] as $route) {
    if ($routesContent === false || !str_contains($routesContent, $route)) {
        fail("No existe ruta {$route} en routes/web.php.", $criticalFailures);
    } else {
        ok("Existe ruta {$route} en routes/web.php.");
    }
}

$adapterContent = @file_get_contents($root . '/app/Core/BrowserAnalytics/EcosistemaBrowserAnalyticsAdapter.php');
if ($adapterContent === false || !str_contains($adapterContent, "'dashboard_read'=>true") || !str_contains($adapterContent, "'events_read'=>true") || !str_contains($adapterContent, "'collector_write'=>($enabled&&$write)") || !str_contains($adapterContent, "'privacy_controls'=>true")) {
    fail('Adapter Browser Analytics no declara capacidades read-only esperadas.', $criticalFailures);
} else {
    ok('Adapter Browser Analytics declara capacidades controladas y privacy_controls=true.');
}

$allPhpFiles = @shell_exec('find ' . escapeshellarg($root) . ' -type f -name "*.php"');
if (is_string($allPhpFiles) && $allPhpFiles !== '') {
    $paths = array_filter(array_map('trim', explode("
", $allPhpFiles)));
    $writeViolations = [];
    foreach ($paths as $absPath) {
        $content = @file_get_contents($absPath);
        if (!is_string($content)) { continue; }
        if (preg_match('/\b(INSERT|UPDATE|DELETE)\b[^;]*\bbrowser_analytics_/i', $content) === 1) {
            $rel = str_replace($root . '/', '', $absPath);
            if ($rel !== 'app/Core/BrowserAnalytics/EcosistemaBrowserAnalyticsCollectorRepository.php') {
                $writeViolations[] = $rel;
            }
        }
    }
    if ($writeViolations !== []) {
        fail('Se detectaron escrituras SQL sobre browser_analytics_*: ' . implode(', ', $writeViolations), $criticalFailures);
    } else {
        ok('No se detectaron INSERT/UPDATE/DELETE sobre browser_analytics_* en archivos PHP.');
    }
}


$pageviewRepoPath = $root . '/app/Core/BrowserAnalytics/EcosistemaBrowserAnalyticsPageviewRepository.php';
if (is_file($pageviewRepoPath)) {
    $repoContent = (string) file_get_contents($pageviewRepoPath);
    if (preg_match('/\b(INSERT|UPDATE|DELETE)\b/i', $repoContent) === 1) {
        fail('Repository de pageviews contiene sentencia de escritura SQL.', $criticalFailures);
    } else {
        ok('Repository de pageviews no contiene INSERT/UPDATE/DELETE.');
    }
}

$viewPageviewsPath = $root . '/resources/views/pages/browser-analytics/pageviews.php';
if (is_file($viewPageviewsPath)) {
    $viewContent = (string) file_get_contents($viewPageviewsPath);
    if (str_contains($viewContent, "query_string") || str_contains($viewContent, "meta_json")) {
        fail('Vista pageviews expone campos sensibles crudos.', $criticalFailures);
    } else {
        ok('Vista pageviews no expone query_string/meta_json crudos.');
    }
}


$eventRepoPath = $root . '/app/Core/BrowserAnalytics/EcosistemaBrowserAnalyticsEventRepository.php';
if (is_file($eventRepoPath)) {
    $repoContent = (string) file_get_contents($eventRepoPath);
    if (preg_match('/\b(INSERT|UPDATE|DELETE)\b/i', $repoContent) === 1) {
        fail('Repository de events contiene sentencia de escritura SQL.', $criticalFailures);
    } else {
        ok('Repository de events no contiene INSERT/UPDATE/DELETE.');
    }
}

$eventViews = [
    'resources/views/pages/browser-analytics/events.php',
    'resources/views/pages/browser-analytics/pageview-events.php',
];
foreach ($eventViews as $eventViewFile) {
    $viewContent = (string) (@file_get_contents($root . '/' . $eventViewFile) ?: '');
    if (str_contains($viewContent, "metadata_json")) {
        fail('Vista de events parece exponer metadata_json crudo: ' . $eventViewFile, $criticalFailures);
    } else {
        ok('Vista de events no expone metadata_json crudo: ' . $eventViewFile);
    }
}


$collectorServicePath = $root . '/app/Core/BrowserAnalytics/EcosistemaBrowserAnalyticsCollectorService.php';
$collectorRepositoryPath = $root . '/app/Core/BrowserAnalytics/EcosistemaBrowserAnalyticsCollectorRepository.php';
checkFile($root, 'app/Core/BrowserAnalytics/EcosistemaBrowserAnalyticsCollectorService.php', $criticalFailures);
checkFile($root, 'app/Core/BrowserAnalytics/EcosistemaBrowserAnalyticsCollectorRepository.php', $criticalFailures);
if ($routesContent !== false && str_contains($routesContent, "'POST /browser/analytics/collect'")) { ok('Existe ruta POST /browser/analytics/collect en routes/web.php.'); } else { fail('No existe ruta POST /browser/analytics/collect en routes/web.php.', $criticalFailures); }
if (is_file($collectorServicePath)) {
    $collectorServiceContent = (string) file_get_contents($collectorServicePath);
    if (str_contains($collectorServiceContent, "'ip_address' => $collectIp ?") && str_contains($collectorServiceContent, "'user_agent' => $collectUa ?")) { ok('CollectorService respeta flags de IP y User-Agent.'); } else { fail('CollectorService no respeta flags de IP/User-Agent.', $criticalFailures); }
    if (str_contains($collectorServiceContent, "tenant_id") && str_contains($collectorServiceContent, "request")) { fail('CollectorService parece aceptar tenant_id desde request.', $criticalFailures); } else { ok('CollectorService no acepta tenant_id desde request.'); }
}

$crmRequired = [
    'app/Core/Crm/EcosistemaCrmAdapter.php',
    'app/Core/Crm/EcosistemaCrmCampaignRepository.php',
    'app/Core/Crm/EcosistemaCrmCampaignService.php',
    'resources/views/pages/crm/index.php',
    'resources/views/pages/crm/campaigns.php',
    'resources/views/pages/crm/campaign-detail.php',
    'docs/project/ECOSISTEMA_CRM_CAMPAIGNS_READ_ONLY.md',
];
foreach ($crmRequired as $crmFile) { checkFile($root, $crmFile, $criticalFailures); }

$crmAdapterContent = is_file($root . '/app/Core/Crm/EcosistemaCrmAdapter.php') ? file_get_contents($root . '/app/Core/Crm/EcosistemaCrmAdapter.php') : false;
if ($crmAdapterContent !== false && str_contains($crmAdapterContent, "'campaign_write' => false")) { ok('CRM adapter mantiene campaign_write=false.'); }
else { fail('CRM adapter no mantiene campaign_write=false.', $criticalFailures); }

$crmRepoContent = is_file($root . '/app/Core/Crm/EcosistemaCrmCampaignRepository.php') ? file_get_contents($root . '/app/Core/Crm/EcosistemaCrmCampaignRepository.php') : false;
if ($crmRepoContent !== false && preg_match('/\b(INSERT|UPDATE|DELETE)\b\s+.*crm_marketing_campaigns/i', $crmRepoContent) !== 1) { ok('CRM repository sin escrituras sobre crm_marketing_campaigns.'); }
else { fail('CRM repository contiene escrituras sobre crm_marketing_campaigns.', $criticalFailures); }

$routesContent = is_file($root . '/routes/web.php') ? file_get_contents($root . '/routes/web.php') : false;
foreach (['GET /crm', 'GET /crm/campaigns', 'GET /crm/campaigns/{id}'] as $crmRoute) {
    if ($routesContent !== false && str_contains($routesContent, $crmRoute)) { ok('Ruta CRM detectada: ' . $crmRoute); }
    else { fail('Falta ruta CRM: ' . $crmRoute, $criticalFailures); }
}

$crmDryRunRequired = [
    'app/Core/Crm/EcosistemaCrmSubmissionToLeadDryRunService.php',
    'resources/views/pages/crm/submission-to-lead-dry-run.php',
    'docs/project/ECOSISTEMA_CRM_SUBMISSION_TO_LEAD_DRY_RUN.md',
    'docs/project/ECOSISTEMA_ATTRIBUTION_ROLLUP_CONTROLLED.md',
];
foreach ($crmDryRunRequired as $file) { checkFile($root, $file, $criticalFailures); }

$envExample = is_file($root . '/.env.example') ? (string) file_get_contents($root . '/.env.example') : '';
foreach (['ECOSISTEMA_CRM_ENABLED=false', 'ECOSISTEMA_CRM_SUBMISSION_TO_LEAD_DRY_RUN=false', 'ECOSISTEMA_CRM_SUBMISSION_TO_LEAD_WRITE=false'] as $flag) {
    if (str_contains($envExample, $flag)) { ok('.env.example mantiene flag segura: ' . $flag); }
    else { fail('Falta flag segura en .env.example: ' . $flag, $criticalFailures); }
}

$crmDryRunServiceContent = is_file($root . '/app/Core/Crm/EcosistemaCrmSubmissionToLeadDryRunService.php') ? (string) file_get_contents($root . '/app/Core/Crm/EcosistemaCrmSubmissionToLeadDryRunService.php') : '';
foreach (['INSERT INTO crm_leads', 'UPDATE crm_leads', 'INSERT INTO crm_campaign_leads', 'UPDATE crm_campaign_leads', 'UPDATE landing_form_submissions', 'INSERT INTO landing_form_submissions'] as $forbiddenSql) {
    if (stripos($crmDryRunServiceContent, $forbiddenSql) === false) { ok('Dry-run service sin escritura prohibida: ' . $forbiddenSql); }
    else { fail('Dry-run service contiene escritura prohibida: ' . $forbiddenSql, $criticalFailures); }
}

$dryRunViewContent = is_file($root . '/resources/views/pages/crm/submission-to-lead-dry-run.php') ? (string) file_get_contents($root . '/resources/views/pages/crm/submission-to-lead-dry-run.php') : '';
foreach (['raw_data_json', "['value_json']"] as $sensitive) {
    if (!str_contains($dryRunViewContent, $sensitive)) { ok('Vista dry-run no expone campo sensible: ' . $sensitive); }
    else { fail('Vista dry-run no debe exponer campo sensible: ' . $sensitive, $criticalFailures); }
}

if ($routesContent !== false && str_contains((string) $routesContent, 'GET /crm/submission-to-lead/{id}/dry-run')) {
    ok('Ruta CRM dry-run detectada: GET /crm/submission-to-lead/{id}/dry-run');
} else {
    fail('Falta ruta CRM dry-run: GET /crm/submission-to-lead/{id}/dry-run', $criticalFailures);
}

$crmWriteRequired = [
    'app/Core/Crm/EcosistemaCrmLeadWriteRepository.php',
    'app/Core/Crm/EcosistemaCrmSubmissionToLeadService.php',
    'resources/views/pages/crm/submission-to-lead-result.php',
    'docs/project/ECOSISTEMA_CRM_SUBMISSION_TO_LEAD_CONTROLLED.md',
];
foreach ($crmWriteRequired as $file) { checkFile($root, $file, $criticalFailures); }

$crmWriteRepo = is_file($root . '/app/Core/Crm/EcosistemaCrmLeadWriteRepository.php') ? (string) file_get_contents($root . '/app/Core/Crm/EcosistemaCrmLeadWriteRepository.php') : '';
$writeInsertAllowed = str_contains($crmWriteRepo, 'INSERT INTO crm_leads');
$dryRunHasInsert = stripos((string) $crmDryRunServiceContent, 'INSERT INTO crm_leads') !== false;
if ($writeInsertAllowed && !$dryRunHasInsert) { ok('INSERT INTO crm_leads aparece solo en flujo de write controlado.'); } else { fail('INSERT INTO crm_leads no cumple restricción de write controlado.', $criticalFailures); }

if ($routesContent !== false && str_contains((string) $routesContent, 'POST /crm/submission-to-lead/{id}')) { ok('Ruta CRM write detectada: POST /crm/submission-to-lead/{id}'); } else { fail('Falta ruta CRM write: POST /crm/submission-to-lead/{id}', $criticalFailures); }

$crmFollowupsRequired = [
    'app/Core/Crm/EcosistemaCrmFollowupRepository.php',
    'app/Core/Crm/EcosistemaCrmFollowupService.php',
    'resources/views/pages/crm/followups.php',
    'resources/views/pages/crm/lead-followups.php',
    'docs/project/ECOSISTEMA_CRM_FOLLOWUPS_READ_ONLY.md',
];
foreach ($crmFollowupsRequired as $file) { checkFile($root, $file, $criticalFailures); }

$followupRepo = is_file($root . '/app/Core/Crm/EcosistemaCrmFollowupRepository.php') ? (string) file_get_contents($root . '/app/Core/Crm/EcosistemaCrmFollowupRepository.php') : '';
if (preg_match('/(INSERT|UPDATE|DELETE)\s+.*(crm_tasks|crm_customer_followups|agenda_events)/i', $followupRepo) !== 1) { ok('Followup repository en modo read-only.'); } else { fail('Followup repository contiene escrituras no permitidas.', $criticalFailures); }

foreach (['GET /crm/followups', 'GET /crm/leads/{id}/followups'] as $crmRoute) {
    if ($routesContent !== false && str_contains((string) $routesContent, $crmRoute)) { ok('Ruta CRM followups detectada: ' . $crmRoute); }
    else { fail('Falta ruta CRM followups: ' . $crmRoute, $criticalFailures); }
}

if ($routesContent !== false && str_contains((string) $routesContent, 'ensureValidCsrfToken')) { ok('Ruta write usa CSRF.'); } else { fail('Ruta write debe validar CSRF.', $criticalFailures); }
$sendDryRunView = is_file($root . '/resources/views/pages/mail-notifications/send-dry-run.php') ? (string) file_get_contents($root . '/resources/views/pages/mail-notifications/send-dry-run.php') : '';
if (str_contains($sendDryRunView, "name=\"_csrf\"")) { ok('Vista send-dry-run usa CSRF.'); } else { fail('Vista send-dry-run no usa CSRF.', $criticalFailures); }

$sendDryRunService = is_file($root . '/app/Core/MailNotifications/EcosistemaSendNotificationDryRunService.php') ? (string) file_get_contents($root . '/app/Core/MailNotifications/EcosistemaSendNotificationDryRunService.php') : '';
if (preg_match('/\bINSERT\s+INTO\s+(notifications_queue|mail_messages)\b/i', $sendDryRunService) === 1) { fail('Servicio send dry-run contiene INSERT no permitido.', $criticalFailures); } else { ok('Servicio send dry-run sin INSERT en notifications_queue/mail_messages.'); }
if (preg_match('/\b(mail\s*\(|smtp|curl_init)\b/i', $sendDryRunService) === 1) { fail('Servicio send dry-run detecta uso de mail()/SMTP/curl.', $criticalFailures); } else { ok('Servicio send dry-run no usa mail()/SMTP/curl.'); }


checkFile($root, 'app/Core/MailNotifications/EcosistemaSendNotificationRepository.php', $criticalFailures);
checkFile($root, 'app/Core/MailNotifications/EcosistemaSendNotificationService.php', $criticalFailures);
checkFile($root, 'resources/views/pages/mail-notifications/send-result.php', $criticalFailures);
checkFile($root, 'docs/project/ECOSISTEMA_SEND_NOTIFICATION_CONTROLLED.md', $criticalFailures);

$envExample = is_file($root . '/.env.example') ? (string) file_get_contents($root . '/.env.example') : '';
if (str_contains($envExample, 'ECOSISTEMA_MAIL_NOTIFICATIONS_ENABLED=false') && str_contains($envExample, 'ECOSISTEMA_MAIL_SEND_ENABLED=false') && str_contains($envExample, 'ECOSISTEMA_SMTP_ENABLED=false')) { ok('.env.example mantiene flags en false.'); } else { fail('.env.example no mantiene flags de mail notifications en false.', $criticalFailures); }

$sendRepo = is_file($root . '/app/Core/MailNotifications/EcosistemaSendNotificationRepository.php') ? (string) file_get_contents($root . '/app/Core/MailNotifications/EcosistemaSendNotificationRepository.php') : '';
$allCore = (string) shell_exec('cat ' . escapeshellarg($root . '/app/Core/MailNotifications') . '/*.php 2>/dev/null');
if (substr_count($allCore, 'INSERT INTO notifications_queue') === substr_count($sendRepo, 'INSERT INTO notifications_queue') && substr_count($allCore, 'INSERT INTO mail_messages') === substr_count($sendRepo, 'INSERT INTO mail_messages')) { ok('INSERT notifications_queue/mail_messages localizado sólo en SendNotificationRepository.'); } else { fail('INSERT notifications_queue/mail_messages aparece fuera de SendNotificationRepository.', $criticalFailures); }

$sendService = is_file($root . '/app/Core/MailNotifications/EcosistemaSendNotificationService.php') ? (string) file_get_contents($root . '/app/Core/MailNotifications/EcosistemaSendNotificationService.php') : '';
if (!str_contains($sendService, "['tenant_id']") && !str_contains($sendService, "\$_POST['tenant_id']")) { ok('SendNotificationService no acepta tenant_id desde request.'); } else { fail('SendNotificationService acepta tenant_id desde request.', $criticalFailures); }
if (!str_contains($sendService, 'password_encrypted') || !str_contains($sendService, 'result')) { ok('SendNotificationService no imprime password_encrypted.'); }

$dryRunFiles = [
    'app/Core/Workflow/EcosistemaWorkflowDryRunService.php',
    'resources/views/pages/workflow/dry-run.php',
    'docs/project/ECOSISTEMA_WORKFLOW_DRY_RUN.md',
];
foreach ($dryRunFiles as $dryRunFile) {
    checkFile($root, $dryRunFile, $criticalFailures);
}

$routesContent = is_file($root . '/routes/web.php') ? (string) file_get_contents($root . '/routes/web.php') : '';
foreach (['GET /workflow/rules/{id}/dry-run', 'POST /workflow/rules/{id}/dry-run', 'GET /workflow/dry-run', 'POST /workflow/dry-run'] as $route) {
    if (str_contains($routesContent, $route)) { ok('Ruta dry-run presente: ' . $route); } else { fail('Ruta dry-run ausente: ' . $route, $criticalFailures); }
}

$envExample = is_file($root . '/.env.example') ? (string) file_get_contents($root . '/.env.example') : '';
foreach (['ECOSISTEMA_WORKFLOW_ENABLED=false', 'ECOSISTEMA_WORKFLOW_DRY_RUN_ENABLED=false', 'ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED=false'] as $envFlag) {
    if (str_contains($envExample, $envFlag)) { ok('.env.example mantiene flag segura: ' . $envFlag); } else { fail('.env.example no contiene: ' . $envFlag, $criticalFailures); }
}

$dryRunService = is_file($root . '/app/Core/Workflow/EcosistemaWorkflowDryRunService.php') ? (string) file_get_contents($root . '/app/Core/Workflow/EcosistemaWorkflowDryRunService.php') : '';
if (preg_match('/\b(eval|mail\s*\(|curl_|webhook_called\s*=\s*true)\b/i', $dryRunService) === 1) {
    fail('Dry-run contiene ejecución no permitida.', $criticalFailures);
} else {
    ok('Dry-run sin ejecución prohibida (eval/mail/curl/webhook real).');
}

$executionFiles = [
    'app/Core/Workflow/EcosistemaWorkflowExecutionRepository.php',
    'app/Core/Workflow/EcosistemaWorkflowExecutionService.php',
    'resources/views/pages/workflow/execution-result.php',
    'docs/project/ECOSISTEMA_WORKFLOW_EXECUTION_CONTROLLED.md',
];
foreach ($executionFiles as $file) { checkFile($root, $file, $criticalFailures); }

$routesContent = (string) file_get_contents($root . '/routes/web.php');
if (!str_contains($routesContent, "POST /workflow/rules/{id}/execute") || !str_contains($routesContent, "POST /workflow/events/execute")) {
    fail('No están las rutas POST de workflow execution.', $criticalFailures);
} else { ok('Rutas POST de workflow execution presentes.'); }

$envContent = (string) file_get_contents($root . '/.env.example');
foreach (['ECOSISTEMA_WORKFLOW_ACTION_CREATE_NOTIFICATION=false','ECOSISTEMA_WORKFLOW_ACTION_SEND_EMAIL=false','ECOSISTEMA_WORKFLOW_ACTION_WEBHOOK=false','ECOSISTEMA_WORKFLOW_ACTION_UPDATE_RECORD=false','ECOSISTEMA_WORKFLOW_ACTION_CREATE_TASK=false','ECOSISTEMA_WORKFLOW_ACTION_CREATE_TICKET=false','ECOSISTEMA_WORKFLOW_ACTION_CREATE_AGENDA_EVENT=false','ECOSISTEMA_WORKFLOW_ACTION_CUSTOM=false'] as $flagLine) {
    if (!str_contains($envContent, $flagLine)) { fail('Falta flag default false: ' . $flagLine, $criticalFailures); }
}

$repoContent = (string) file_get_contents($root . '/app/Core/Workflow/EcosistemaWorkflowExecutionRepository.php');
if (substr_count($repoContent, 'INSERT INTO workflow_runs') !== 1 || substr_count($repoContent, 'INSERT INTO workflow_run_logs') !== 1 || substr_count($repoContent, 'UPDATE workflow_runs') !== 1) {
    fail('Queries workflow_runs/logs deben existir sólo en ExecutionRepository.', $criticalFailures);
} else { ok('Queries de escritura workflow centralizadas en ExecutionRepository.'); }

$platformRequiredFiles = [
    'app/Core/Platform/EcosistemaPlatformCockpitRepository.php',
    'app/Core/Platform/EcosistemaPlatformCockpitService.php',
    'app/Core/Platform/EcosistemaPlatformAdapter.php',
    'resources/views/pages/platform/cockpit.php',
    'docs/project/ECOSISTEMA_PLATFORM_COCKPIT.md',
    'app/Core/Platform/EcosistemaPlatformHealthRepository.php',
    'app/Core/Platform/EcosistemaPlatformHealthService.php',
    'resources/views/pages/platform/health.php',
    'resources/views/pages/platform/module-health.php',
    'docs/project/ECOSISTEMA_PLATFORM_MODULE_HEALTH.md',
];
foreach ($platformRequiredFiles as $platformFile) {
    checkFile($root, $platformFile, $criticalFailures);
}

$platformRepositoryPath = $root . '/app/Core/Platform/EcosistemaPlatformCockpitRepository.php';
if (is_file($platformRepositoryPath)) {
    $platformRepositoryContent = (string) file_get_contents($platformRepositoryPath);
    if (preg_match('/\b(INSERT|UPDATE|DELETE)\b/i', $platformRepositoryContent) === 1) {
        fail('Repositorio Platform cockpit contiene escritura no permitida.', $criticalFailures);
    } else {
        ok('Repositorio Platform cockpit sólo contiene lecturas.');
    }
}

if (is_file($routesFile)) {
    $routesContent = (string) file_get_contents($routesFile);
    if (str_contains($routesContent, "GET /platform")) { ok('routes/web.php contiene GET /platform.'); } else { fail('No se encontró GET /platform.', $criticalFailures); }
    if (str_contains($routesContent, "GET /platform/cockpit")) { ok('routes/web.php contiene GET /platform/cockpit.'); } else { fail('No se encontró GET /platform/cockpit.', $criticalFailures); }
    if (str_contains($routesContent, "GET /platform/health")) { ok('routes/web.php contiene GET /platform/health.'); } else { fail('No se encontró GET /platform/health.', $criticalFailures); }
    if (str_contains($routesContent, "GET /platform/health/modules/{code}")) { ok('routes/web.php contiene GET /platform/health/modules/{code}.'); } else { fail('No se encontró GET /platform/health/modules/{code}.', $criticalFailures); }
    if (str_contains($routesContent, "\$_GET['tenant_id']") || str_contains($routesContent, "\$_POST['tenant_id']")) {
        warn('Revisar manualmente posible uso tenant_id desde request en routes/web.php.', $warnings);
    } else {
        ok('No hay uso directo de tenant_id desde request en routes/web.php.');
    }
}

$healthViewPath = $root . '/resources/views/pages/platform/health.php';
if (is_file($healthViewPath)) {
    $healthViewContent = (string) file_get_contents($healthViewPath);
    if (str_contains($healthViewContent, 'details_json') || str_contains($healthViewContent, 'payload_json') || str_contains($healthViewContent, 'metadata_json')) {
        fail('Vista platform/health expone campos JSON sensibles.', $criticalFailures);
    } else {
        ok('Vista platform/health sin exposición de JSON sensible.');
    }
}

$permissionsAuditRequiredFiles = [
    'app/Core/Security/EcosistemaPermissionAuditRepository.php',
    'app/Core/Security/EcosistemaPermissionAuditService.php',
    'resources/views/pages/security/permissions-audit.php',
    'resources/views/pages/security/module-permissions-audit.php',
    'docs/project/ECOSISTEMA_PERMISSIONS_AUDIT.md',
];
foreach ($permissionsAuditRequiredFiles as $permissionsFile) { checkFile($root, $permissionsFile, $criticalFailures); }

$securityRepoPath = $root . '/app/Core/Security/EcosistemaPermissionAuditRepository.php';
if (is_file($securityRepoPath)) {
    $securityRepoContent = (string) file_get_contents($securityRepoPath);
    if (preg_match('/\b(INSERT|UPDATE|DELETE)\b/i', $securityRepoContent) === 1) {
        fail('EcosistemaPermissionAuditRepository contiene escritura no permitida.', $criticalFailures);
    } else {
        ok('EcosistemaPermissionAuditRepository sólo contiene lecturas.');
    }
}

$securityServicePath = $root . '/app/Core/Security/EcosistemaPermissionAuditService.php';
if (is_file($securityServicePath)) {
    $securityServiceContent = (string) file_get_contents($securityServicePath);
    if (str_contains($securityServiceContent, "['tenant_id']") || str_contains($securityServiceContent, '$_POST[\'tenant_id\']') || str_contains($securityServiceContent, '$_GET[\'tenant_id\']')) {
        fail('EcosistemaPermissionAuditService acepta tenant_id desde request.', $criticalFailures);
    } else {
        ok('EcosistemaPermissionAuditService no acepta tenant_id desde request.');
    }
}

if (is_file($routesFile)) {
    $routesContent = (string) file_get_contents($routesFile);
    if (str_contains($routesContent, 'GET /security/permissions-audit')) { ok('routes/web.php contiene GET /security/permissions-audit.'); } else { fail('No se encontró GET /security/permissions-audit.', $criticalFailures); }
    if (str_contains($routesContent, 'GET /security/permissions-audit/modules/{code}')) { ok('routes/web.php contiene GET /security/permissions-audit/modules/{code}.'); } else { fail('No se encontró GET /security/permissions-audit/modules/{code}.', $criticalFailures); }
}

$permissionsViewPath = $root . '/resources/views/pages/security/module-permissions-audit.php';
if (is_file($permissionsViewPath)) {
    $permissionsViewContent = (string) file_get_contents($permissionsViewPath);
    if (str_contains($permissionsViewContent, 'details_json') || str_contains($permissionsViewContent, 'metadata_json') || str_contains($permissionsViewContent, 'payload_json') || str_contains($permissionsViewContent, 'password_hash')) {
        fail('Vista de auditoría de permisos expone campos sensibles.', $criticalFailures);
    } else {
        ok('Vista de auditoría de permisos sin exposición de campos sensibles.');
    }
}

$landingDryRunFiles = [
    'app/Core/Landing/EcosistemaLandingPublicRenderDryRunService.php',
    'resources/views/pages/landing/public-render-dry-run.php',
    'docs/project/ECOSISTEMA_LANDING_PUBLIC_RENDER_DRY_RUN.md',
];
foreach ($landingDryRunFiles as $file) { checkFile($root, $file, $criticalFailures); }

if (is_file($routesFile)) {
    $routesContent = (string) file_get_contents($routesFile);
    if (str_contains($routesContent, 'GET /landing/pages/{id}/public-render-dry-run')) { ok('routes/web.php contiene GET /landing/pages/{id}/public-render-dry-run.'); } else { fail('No se encontró GET /landing/pages/{id}/public-render-dry-run.', $criticalFailures); }
    if (str_contains($routesContent, "\$_REQUEST['tenant_id']") || str_contains($routesContent, "\$_GET['tenant_id']") || str_contains($routesContent, "\$_POST['tenant_id']")) {
        warn('Revisar manualmente uso de tenant_id desde request en routes/web.php.', $warnings);
    } else {
        ok('Ruta dry-run no acepta tenant_id desde request.');
    }
}

if (!str_contains($envContent, 'ECOSISTEMA_LANDING_PUBLIC_RENDER_DRY_RUN=false')) {
    fail('Falta flag ECOSISTEMA_LANDING_PUBLIC_RENDER_DRY_RUN=false en .env.example.', $criticalFailures);
} else {
    ok('Flag ECOSISTEMA_LANDING_PUBLIC_RENDER_DRY_RUN=false presente.');
}



// PR #121 landing form submit dry-run checks
if (is_file($routesFile)) {
    $routesContent = (string) file_get_contents($routesFile);
    foreach (['GET /landing/forms/{id}/submit-dry-run', 'POST /landing/forms/{id}/submit-dry-run'] as $routeNeedle) {
        if (str_contains($routesContent, $routeNeedle)) { ok('Ruta landing form submit dry-run detectada: ' . $routeNeedle); } else { fail('Falta ruta landing form submit dry-run: ' . $routeNeedle, $criticalFailures); }
    }
}
$landingDryRunService = @file_get_contents($root . '/app/Core/Landing/EcosistemaLandingFormSubmitDryRunService.php') ?: '';
if (preg_match('/\b(INSERT|UPDATE|DELETE)\b/i', $landingDryRunService) === 1) { fail('Servicio landing form submit dry-run contiene escritura SQL.', $criticalFailures); } else { ok('Servicio landing form submit dry-run sin escritura SQL.'); }
if (str_contains($landingDryRunService, "tenant_id'])") || str_contains($landingDryRunService, "\$_POST['tenant_id']") || str_contains($landingDryRunService, "\$_GET['tenant_id']")) { fail('Dry-run acepta tenant_id desde request.', $criticalFailures); } else { ok('Dry-run no acepta tenant_id desde request.'); }
if (!str_contains($envContent, 'ECOSISTEMA_LANDING_FORM_SUBMIT_DRY_RUN=false')) { fail('Falta flag ECOSISTEMA_LANDING_FORM_SUBMIT_DRY_RUN=false en .env.example.', $criticalFailures); } else { ok('Flag ECOSISTEMA_LANDING_FORM_SUBMIT_DRY_RUN=false presente.'); }

// PR #120 landing public render controlled checks
$landingPublicRenderFiles = [
    'app/Core/Landing/EcosistemaLandingPublicRenderService.php',
    'resources/views/pages/landing/public-page.php',
    'resources/views/pages/landing/public-page-blocked.php',
    'docs/project/ECOSISTEMA_LANDING_PUBLIC_RENDER_CONTROLLED.md',
];
foreach ($landingPublicRenderFiles as $file) { checkFile($root, $file, $criticalFailures); }

if (is_string($routesContent) && str_contains($routesContent, 'GET /l/{slug}')) { ok('routes/web.php contiene GET /l/{slug}.'); }
else { fail('No se encontró GET /l/{slug}.', $criticalFailures); }

if (!str_contains($envContent, 'ECOSISTEMA_LANDING_PUBLIC_RENDER_ENABLED=false')) {
    fail('Falta flag ECOSISTEMA_LANDING_PUBLIC_RENDER_ENABLED=false en .env.example.', $criticalFailures);
} else {
    ok('Flag ECOSISTEMA_LANDING_PUBLIC_RENDER_ENABLED=false presente.');
}

$publicRenderServiceContent = @file_get_contents($root . '/app/Core/Landing/EcosistemaLandingPublicRenderService.php') ?: '';
foreach (['tenant_id', 'INSERT INTO landing_visits', 'UPDATE landing_visits', 'DELETE FROM landing_visits'] as $forbiddenNeedle) {
    if (str_contains($publicRenderServiceContent, $forbiddenNeedle)) {
        fail('Servicio public render contiene patrón no permitido: ' . $forbiddenNeedle, $criticalFailures);
    } else {
        ok('Servicio public render no contiene patrón prohibido: ' . $forbiddenNeedle);
    }
}

$publicView = @file_get_contents($root . '/resources/views/pages/landing/public-page.php') ?: '';
if (preg_match('/(layout_json|custom_css|custom_js|settings_json|content_json|raw_data_json|ip_address|user_agent|visitor_uuid|session_uuid)/i', $publicView) === 1) {
    fail('Vista pública landing expone o referencia campos sensibles crudos.', $criticalFailures);
} else {
    ok('Vista pública landing sin referencias sensibles crudas.');
}



if (is_string($routesContent) && str_contains($routesContent, 'POST /l/{slug}/forms/{id}/submit')) { ok('routes/web.php contiene POST /l/{slug}/forms/{id}/submit.'); }
else { fail('No se encontró POST /l/{slug}/forms/{id}/submit.', $criticalFailures); }

$envExampleContent = @file_get_contents($root . '/.env.example') ?: '';
foreach (['ECOSISTEMA_LANDING_FORM_SUBMIT_ENABLED=false','ECOSISTEMA_LANDING_FORM_FILE_UPLOADS=false'] as $flagLine) {
    if (str_contains($envExampleContent, $flagLine)) { ok('.env.example mantiene ' . $flagLine . '.'); }
    else { fail('.env.example no contiene ' . $flagLine . '.', $criticalFailures); }
}

// PR #123 attribution url-landing dry-run checks
foreach (['app/Core/Attribution/EcosistemaUrlLandingAttributionRepository.php','app/Core/Attribution/EcosistemaUrlLandingAttributionService.php','resources/views/pages/attribution/url-landing-dry-run.php','docs/project/ECOSISTEMA_URL_LANDING_ATTRIBUTION.md'] as $requiredPath) {
    if (is_file($root . '/' . $requiredPath)) { ok('Existe artefacto attribution URL→Landing: ' . $requiredPath); }
    else { fail('Falta artefacto attribution URL→Landing: ' . $requiredPath, $criticalFailures); }
}
if ($routesContent !== false) {
    foreach (['GET /attribution/url-landing/dry-run','POST /attribution/url-landing/dry-run'] as $routeNeedle) {
        if (str_contains($routesContent, $routeNeedle)) { ok('Ruta attribution dry-run detectada: ' . $routeNeedle); } else { fail('Falta ruta attribution dry-run: ' . $routeNeedle, $criticalFailures); }
    }
    if (!str_contains((string)$routesContent, "\$_POST['tenant_id']") && !str_contains((string)$routesContent, "\$_GET['tenant_id']")) { ok('Ruta attribution dry-run no acepta tenant_id desde request.'); }
    else { fail('Ruta attribution dry-run no debe aceptar tenant_id desde request.', $criticalFailures); }
}
$envExample = is_file($root . '/.env.example') ? (string) file_get_contents($root . '/.env.example') : '';
foreach (['ECOSISTEMA_ATTRIBUTION_ENABLED=false','ECOSISTEMA_ATTRIBUTION_WRITE=false'] as $flagNeedle) {
    if (str_contains($envExample, $flagNeedle)) { ok('.env.example contiene flag segura: ' . $flagNeedle); } else { fail('.env.example no contiene flag segura: ' . $flagNeedle, $criticalFailures); }
}
$attrView = is_file($root . '/resources/views/pages/attribution/url-landing-dry-run.php') ? (string) file_get_contents($root . '/resources/views/pages/attribution/url-landing-dry-run.php') : '';
foreach (["['clicked_url']","['full_url']",'metadata_json','raw_data_json','access_token_hash'] as $forbiddenNeedle) {
    if (!str_contains($attrView, $forbiddenNeedle)) { ok('Vista attribution dry-run no expone: ' . $forbiddenNeedle); } else { fail('Vista attribution dry-run no debe exponer: ' . $forbiddenNeedle, $criticalFailures); }
}

// PR #124 attribution campaigns read-only checks
foreach ([
    'app/Core/Attribution/EcosistemaCampaignAttributionRepository.php',
    'app/Core/Attribution/EcosistemaCampaignAttributionService.php',
    'resources/views/pages/attribution/campaigns.php',
    'resources/views/pages/attribution/campaign-detail.php',
    'docs/project/ECOSISTEMA_CAMPAIGN_ATTRIBUTION_READ_ONLY.md'
] as $requiredPath) {
    if (is_file($root . '/' . $requiredPath)) { ok('Existe artefacto attribution campaigns: ' . $requiredPath); }
    else { fail('Falta artefacto attribution campaigns: ' . $requiredPath, $criticalFailures); }
}
if ($routesContent !== false) {
    foreach (['GET /attribution/campaigns', 'GET /attribution/campaigns/{id}'] as $routeNeedle) {
        if (str_contains($routesContent, $routeNeedle)) { ok('Ruta attribution campaigns detectada: ' . $routeNeedle); }
        else { fail('Falta ruta attribution campaigns: ' . $routeNeedle, $criticalFailures); }
    }
    if (!str_contains((string) $routesContent, "\$_GET['tenant_id']") && !str_contains((string) $routesContent, "\$_POST['tenant_id']")) { ok('Attribution campaigns no acepta tenant_id desde request.'); }
    else { fail('Attribution campaigns no debe aceptar tenant_id desde request.', $criticalFailures); }
}
$campaignView = is_file($root . '/resources/views/pages/attribution/campaign-detail.php') ? (string) file_get_contents($root . '/resources/views/pages/attribution/campaign-detail.php') : '';
foreach (['raw_data_json', 'metadata_json', 'access_token_hash', 'target_url', 'original_url_after_ads', 'ip_address', 'user_agent', 'email', 'phone'] as $forbiddenNeedle) {
    if (!str_contains($campaignView, $forbiddenNeedle)) { ok('Vista campaign detail no referencia sensible: ' . $forbiddenNeedle); }
    else { fail('Vista campaign detail no debe referenciar sensible: ' . $forbiddenNeedle, $criticalFailures); }
}

// PR #125 attribution rollup dry-run checks
foreach (['app/Core/Attribution/EcosistemaAttributionRollupDryRunRepository.php','app/Core/Attribution/EcosistemaAttributionRollupDryRunService.php','resources/views/pages/attribution/rollup-dry-run.php','docs/project/ECOSISTEMA_ATTRIBUTION_ROLLUP_DRY_RUN.md'] as $requiredPath) {
    if (is_file($root . '/' . $requiredPath)) { ok('Existe artefacto attribution rollup dry-run: ' . $requiredPath); }
    else { fail('Falta artefacto attribution rollup dry-run: ' . $requiredPath, $criticalFailures); }
}
if ($routesContent !== false) {
    foreach (['GET /attribution/rollups/dry-run','POST /attribution/rollups/dry-run'] as $routeNeedle) {
        if (str_contains($routesContent, $routeNeedle)) { ok('Ruta attribution rollup dry-run detectada: ' . $routeNeedle); }
        else { fail('Falta ruta attribution rollup dry-run: ' . $routeNeedle, $criticalFailures); }
    }
}
if ($envExampleContent !== false && str_contains((string)$envExampleContent, 'ECOSISTEMA_ATTRIBUTION_ROLLUP_DRY_RUN=false')) { ok('Flag attribution rollup dry-run en .env.example con default false.'); }
else { fail('Falta ECOSISTEMA_ATTRIBUTION_ROLLUP_DRY_RUN=false en .env.example.', $criticalFailures); }
$rollupService = is_file($root . '/app/Core/Attribution/EcosistemaAttributionRollupDryRunService.php') ? (string) file_get_contents($root . '/app/Core/Attribution/EcosistemaAttributionRollupDryRunService.php') : '';
if (preg_match('/\b(INSERT|UPDATE|DELETE)\b/i', $rollupService) === 1) { fail('Servicio attribution rollup dry-run contiene escritura SQL.', $criticalFailures); }
else { ok('Servicio attribution rollup dry-run sin escritura SQL.'); }
$rollupView = is_file($root . '/resources/views/pages/attribution/rollup-dry-run.php') ? (string) file_get_contents($root . '/resources/views/pages/attribution/rollup-dry-run.php') : '';
foreach (["['ip_address']","['user_agent']","['target_url']","['access_token_hash']","['metadata_json']","['raw_data_json']"] as $forbiddenNeedle) {
    if (!str_contains($rollupView, $forbiddenNeedle)) { ok('Vista attribution rollup dry-run no expone: ' . $forbiddenNeedle); }
    else { fail('Vista attribution rollup dry-run no debe exponer: ' . $forbiddenNeedle, $criticalFailures); }
}

$routeContent = @file_get_contents($root . '/routes/web.php');
if ($routeContent !== false && str_contains($routeContent, "'POST /attribution/rollups/generate'")) { ok('Existe ruta POST /attribution/rollups/generate.'); } else { fail('No existe ruta POST /attribution/rollups/generate.', $criticalFailures); }

if ($envContent !== false && str_contains($envContent, 'ECOSISTEMA_ATTRIBUTION_ROLLUP_WRITE=false')) { ok('Flag ECOSISTEMA_ATTRIBUTION_ROLLUP_WRITE=false presente en .env.example.'); } else { fail('Falta ECOSISTEMA_ATTRIBUTION_ROLLUP_WRITE=false en .env.example.', $criticalFailures); }


foreach (['app/Core/Crm/EcosistemaCrmFollowupTaskDryRunRepository.php','app/Core/Crm/EcosistemaCrmFollowupTaskDryRunService.php','resources/views/pages/crm/followup-task-dry-run.php','docs/project/ECOSISTEMA_CRM_FOLLOWUP_TASK_DRY_RUN.md'] as $requiredPath) {
    if (is_file($root . '/' . $requiredPath)) { ok('Existe artefacto CRM followup-task dry-run: ' . $requiredPath); }
    else { fail('Falta artefacto CRM followup-task dry-run: ' . $requiredPath, $criticalFailures); }
}
if ($routesContent !== false) {
    foreach (['GET /crm/leads/{id}/followup-task-dry-run','POST /crm/leads/{id}/followup-task-dry-run'] as $routeNeedle) {
        if (str_contains($routesContent, $routeNeedle)) { ok('Ruta CRM followup-task dry-run detectada: ' . $routeNeedle); }
        else { fail('Falta ruta CRM followup-task dry-run: ' . $routeNeedle, $criticalFailures); }
    }
}
if ($envExampleContent !== false && str_contains((string)$envExampleContent, 'ECOSISTEMA_CRM_FOLLOWUP_TASK_DRY_RUN=false')) { ok('Flag CRM followup-task dry-run en .env.example con default false.'); } else { fail('Falta flag ECOSISTEMA_CRM_FOLLOWUP_TASK_DRY_RUN=false en .env.example.', $criticalFailures); }
$followupTaskService = is_file($root . '/app/Core/Crm/EcosistemaCrmFollowupTaskDryRunService.php') ? (string) file_get_contents($root . '/app/Core/Crm/EcosistemaCrmFollowupTaskDryRunService.php') : '';
if (preg_match('/(INSERT|UPDATE|DELETE)/i', $followupTaskService) === 1) { fail('Servicio followup-task dry-run contiene escritura SQL.', $criticalFailures); } else { ok('Servicio followup-task dry-run sin escritura SQL.'); }

foreach (['app/Core/Crm/EcosistemaCrmFollowupTaskRepository.php','app/Core/Crm/EcosistemaCrmFollowupTaskService.php','resources/views/pages/crm/followup-task-result.php','docs/project/ECOSISTEMA_CRM_FOLLOWUP_TASK_CONTROLLED.md'] as $requiredPath) {
    if (is_file($root . '/' . $requiredPath)) { ok('Existe artefacto CRM followup-task controlled: ' . $requiredPath); }
    else { fail('Falta artefacto CRM followup-task controlled: ' . $requiredPath, $criticalFailures); }
}
if ($routesContent !== false && str_contains($routesContent, 'POST /crm/leads/{id}/followup-tasks')) { ok('Ruta CRM followup-task write detectada: POST /crm/leads/{id}/followup-tasks'); }
else { fail('Falta ruta CRM followup-task write: POST /crm/leads/{id}/followup-tasks', $criticalFailures); }
if ($envExampleContent !== false && str_contains((string)$envExampleContent, 'ECOSISTEMA_CRM_FOLLOWUP_TASK_WRITE=false')) { ok('Flag CRM followup-task write en .env.example con default false.'); }
else { fail('Falta flag ECOSISTEMA_CRM_FOLLOWUP_TASK_WRITE=false en .env.example.', $criticalFailures); }
$followupTaskWriteRepo = is_file($root . '/app/Core/Crm/EcosistemaCrmFollowupTaskRepository.php') ? (string) file_get_contents($root . '/app/Core/Crm/EcosistemaCrmFollowupTaskRepository.php') : '';
if (str_contains($followupTaskWriteRepo, 'INSERT INTO crm_tasks')) { ok('Repository followup-task write limita INSERT a crm_tasks.'); }
else { fail('Repository followup-task write debe insertar en crm_tasks.', $criticalFailures); }

foreach (['app/Core/Crm/EcosistemaCrmLeadStatusRepository.php','app/Core/Crm/EcosistemaCrmLeadStatusService.php','resources/views/pages/crm/lead-status.php','resources/views/pages/crm/lead-status-result.php','docs/project/ECOSISTEMA_CRM_LEAD_STATUS_CONTROLLED.md'] as $requiredPath) {
    if (is_file($root . '/' . $requiredPath)) { ok('Existe ' . $requiredPath); } else { fail('Falta archivo requerido: ' . $requiredPath, $criticalFailures); }
}
if ($routesContent !== false && str_contains($routesContent, 'GET /crm/leads/{id}/status') && str_contains($routesContent, 'POST /crm/leads/{id}/status')) { ok('Rutas CRM lead status detectadas (GET/POST).'); }
else { fail('Faltan rutas CRM lead status esperadas.', $criticalFailures); }
if ($envExampleContent !== false && str_contains((string)$envExampleContent, 'ECOSISTEMA_CRM_LEAD_STATUS_WRITE=false')) { ok('Flag CRM lead status write en .env.example con default false.'); }
else { fail('Falta flag ECOSISTEMA_CRM_LEAD_STATUS_WRITE=false en .env.example.', $criticalFailures); }
$leadStatusRepo = is_file($root . '/app/Core/Crm/EcosistemaCrmLeadStatusRepository.php') ? (string) file_get_contents($root . '/app/Core/Crm/EcosistemaCrmLeadStatusRepository.php') : '';
if ($leadStatusRepo !== '' && str_contains($leadStatusRepo, 'UPDATE crm_leads SET status=') && str_contains($leadStatusRepo, 'UPDATE crm_campaign_leads SET status=')) { ok('LeadStatusRepository contiene UPDATE permitidos en tablas canónicas.'); }
else { fail('LeadStatusRepository no contiene UPDATE canónicos esperados.', $criticalFailures); }
if ($leadStatusRepo !== '' && !str_contains($leadStatusRepo, 'tenant_id FROM') && !str_contains($leadStatusRepo, '$_REQUEST')) { ok('LeadStatusRepository sin lectura de tenant_id desde request.'); }
else { fail('LeadStatusRepository evidencia patrón inseguro de tenant/request.', $criticalFailures); }
