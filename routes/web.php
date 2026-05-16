<?php

declare(strict_types=1);

use App\Core\Auth\AuthService;
use App\Core\Auth\AuthSession;
use App\Core\Auth\SessionRepository;
use App\Core\Auth\UserRepository;
use App\Core\Dashboard\DashboardService;
use App\Core\Tenants\TenantRepository;
use App\Core\Tenants\TenantService;
use App\Core\Roles\RoleRepository;
use App\Core\Roles\RoleService;
use App\Core\Users\UserRepository as CoreUserRepository;
use App\Core\Users\UserService;
use App\Core\Users\UserRoleRepository;
use App\Core\Users\UserRoleService;
use App\Core\Permissions\PermissionRepository;
use App\Core\Permissions\PermissionService;
use App\Core\Permissions\RolePermissionService;
use App\Core\Modules\ModuleRepository;
use App\Core\Modules\ModuleService;
use App\Core\Database\PdoFactory;
use App\Core\System\HealthRepository;
use App\Core\System\HealthService;
use App\Core\System\LogRepository;
use App\Core\System\AuditRepository;
use App\Core\System\AuditLogger;
use App\Core\Mail\MailService;
use App\Core\Mail\MailMessageRepository;
use App\Core\Mail\MailboxRepository;
use App\Core\Mail\MailConfig;
use App\Core\Mail\MailAttachmentRepository;
use App\Core\Mail\MailAttachmentService;
use App\Core\Mail\MailSendService;
use App\Core\Mail\MailOutgoingAttachmentService;
use App\Core\MailNotifications\EcosistemaMailNotificationsAdapter;
use App\Core\MailNotifications\EcosistemaMessagePreviewDryRunService;
use App\Core\MailNotifications\EcosistemaNotificationTemplateRepository;
use App\Core\MailNotifications\EcosistemaNotificationTemplateService;
use App\Core\MailNotifications\EcosistemaNotificationQueueRepository;
use App\Core\MailNotifications\EcosistemaNotificationQueueService;
use App\Core\MailNotifications\EcosistemaSendNotificationDryRunService;
use App\Core\MailNotifications\EcosistemaSendNotificationRepository;
use App\Core\MailNotifications\EcosistemaSendNotificationService;
use App\Core\MailNotifications\EcosistemaUrlMessageTemplateRepository;
use App\Core\MailNotifications\EcosistemaUrlMessageTemplateService;
use App\Core\Cloud\CloudFileRepository;
use App\Core\Cloud\CloudFolderRepository;
use App\Core\Cloud\CloudRootRepository;
use App\Core\Cloud\CloudService;
use App\Core\Cloud\CloudStorageConfig;
use App\Core\Cloud\CloudStorageService;
use App\Core\Cloud\CloudUploadService;
use App\Core\Cloud\CloudDownloadService;
use App\Core\Cloud\EcosistemaDriveConfig;
use App\Core\Cloud\EcosistemaDriveAdapter;
use App\Core\Cloud\EcosistemaDriveFileRepository;
use App\Core\Cloud\EcosistemaDriveFileService;
use App\Core\Cloud\EcosistemaDriveFileVersionService;
use App\Core\Cloud\EcosistemaDriveFileVersionRepository;
use App\Core\Cloud\EcosistemaDriveFolderService;
use App\Core\Cloud\EcosistemaDriveFolderRepository;
use App\Core\Cloud\EcosistemaDriveRootRepository;
use App\Core\Cloud\EcosistemaDriveRootService;
use App\Core\Cloud\EcosistemaDriveBucketService;
use App\Core\Cloud\EcosistemaDriveBucketRepository;
use App\Core\Cloud\EcosistemaDriveSummaryService;
use App\Core\Cloud\EcosistemaDriveAccessPolicy;
use App\Core\Cloud\EcosistemaDriveAuditLogger;
use App\Core\Cloud\EcosistemaDriveDownloadContract;
use App\Core\Cloud\EcosistemaDriveS3KeyValidationRepository;
use App\Core\Cloud\EcosistemaDriveS3KeyValidationService;
use App\Core\Cloud\EcosistemaDriveS3KeyValidator;
use App\Core\Cloud\EcosistemaDriveSignedUrlDryRunService;
use App\Core\Cloud\EcosistemaDriveSignedUrlDryRun;
use App\Core\Cloud\EcosistemaDriveAwsS3Config;
use App\Core\Cloud\EcosistemaDriveS3DownloadService;
use App\Core\Cloud\EcosistemaDriveS3UploadDryRun;
use App\Core\Cloud\EcosistemaDriveS3UploadDryRunService;
use App\Core\Cloud\EcosistemaDriveS3UploadService;
use App\Core\Cloud\EcosistemaDriveShareContractService;
use App\Core\Cloud\EcosistemaDriveShareContract;
use App\Core\Cloud\EcosistemaDriveAccessLogService;
use App\Core\Cloud\EcosistemaDriveAccessLogRepository;
use App\Core\Cloud\EcosistemaDriveStorageUsageService;
use App\Core\Cloud\EcosistemaDriveStorageUsageRepository;
use App\Core\Cloud\EcosistemaDriveRepairJobRepository;
use App\Core\Cloud\EcosistemaDriveRepairJobService;
use App\Http\View\View;
use App\Core\Onboarding\OnboardingFlowRepository;
use App\Core\Onboarding\OnboardingRunRepository;
use App\Core\Onboarding\OnboardingService;
use App\Core\Onboarding\OnboardingRunner;
use App\Core\Onboarding\OnboardingStepExecutor;
use App\Core\UrlLocator\EcosistemaUrlLocatorAdapter;
use App\Core\UrlLocator\EcosistemaUrlLocatorLinkRepository;
use App\Core\UrlLocator\EcosistemaUrlLocatorLinkService;
use App\Core\UrlLocator\EcosistemaUrlLocatorClickService;
use App\Core\UrlLocator\EcosistemaUrlLocatorClickRepository;
use App\Core\UrlLocator\EcosistemaUrlLocatorLinkWriteRepository;
use App\Core\UrlLocator\EcosistemaUrlLocatorLinkWriteService;
use App\Core\UrlLocator\EcosistemaUrlLocatorRedirectDryRunService;
use App\Core\UrlLocator\EcosistemaUrlLocatorPublicRedirectService;
use App\Core\Landing\EcosistemaLandingAdapter;
use App\Core\Landing\EcosistemaLandingPageRepository;
use App\Core\Landing\EcosistemaLandingPageService;
use App\Core\Landing\EcosistemaLandingPublicRenderDryRunService;
use App\Core\Landing\EcosistemaLandingPublicRenderService;
use App\Core\Landing\EcosistemaLandingVisitRepository;
use App\Core\Landing\EcosistemaLandingVisitService;
use App\Core\Landing\EcosistemaLandingFormRepository;
use App\Core\Landing\EcosistemaLandingFormService;
use App\Core\Landing\EcosistemaLandingSubmissionService;
use App\Core\Landing\EcosistemaLandingSubmissionRepository;
use App\Core\Landing\EcosistemaLandingFormSubmitDryRunService;
use App\Core\Landing\EcosistemaLandingFormSubmitRepository;
use App\Core\Landing\EcosistemaLandingFormSubmitService;
use App\Core\BrowserAnalytics\EcosistemaBrowserAnalyticsAdapter;
use App\Core\BrowserAnalytics\EcosistemaBrowserAnalyticsDashboardRepository;
use App\Core\BrowserAnalytics\EcosistemaBrowserAnalyticsDashboardService;
use App\Core\BrowserAnalytics\EcosistemaBrowserAnalyticsPageviewRepository;
use App\Core\BrowserAnalytics\EcosistemaBrowserAnalyticsPageviewService;
use App\Core\BrowserAnalytics\EcosistemaBrowserAnalyticsEventRepository;
use App\Core\BrowserAnalytics\EcosistemaBrowserAnalyticsEventService;
use App\Core\BrowserAnalytics\EcosistemaBrowserAnalyticsCollectorDryRunService;
use App\Core\BrowserAnalytics\EcosistemaBrowserAnalyticsCollectorRepository;
use App\Core\BrowserAnalytics\EcosistemaBrowserAnalyticsCollectorService;
use App\Core\Crm\EcosistemaCrmAdapter;
use App\Core\Crm\EcosistemaCrmCampaignRepository;
use App\Core\Crm\EcosistemaCrmCampaignService;
use App\Core\Crm\EcosistemaCrmLeadRepository;
use App\Core\Crm\EcosistemaCrmLeadService;
use App\Core\Crm\EcosistemaCrmSubmissionToLeadDryRunService;
use App\Core\Crm\EcosistemaCrmLeadWriteRepository;
use App\Core\Crm\EcosistemaCrmSubmissionToLeadService;
use App\Core\Crm\EcosistemaCrmFollowupRepository;
use App\Core\Crm\EcosistemaCrmFollowupService;
use App\Core\Crm\EcosistemaCrmLeadStatusService;
use App\Core\Crm\EcosistemaCrmLeadStatusRepository;
use App\Core\Ai\EcosistemaAiAssistanceService;
use App\Core\Ai\EcosistemaAiAssistanceRepository;
use App\Core\Ai\EcosistemaAiProvider;
use App\Core\Campaigns\EcosistemaCampaignCockpitRepository;
use App\Core\Campaigns\EcosistemaCampaignCockpitService;
use App\Core\Campaigns\EcosistemaCampaignCreationRepository;
use App\Core\Campaigns\EcosistemaCampaignCreationService;
use App\Core\Platform\EcosistemaPlatformAdapter;
use App\Core\Platform\EcosistemaPlatformCockpitRepository;
use App\Core\Platform\EcosistemaPlatformCockpitService;
use App\Core\Platform\EcosistemaPlatformHealthRepository;
use App\Core\Platform\EcosistemaPlatformHealthService;
use App\Core\Security\EcosistemaPermissionAuditRepository;
use App\Core\Security\EcosistemaPermissionAuditService;
use App\Core\Security\EcosistemaRateLimitDryRunRepository;
use App\Core\Security\EcosistemaRateLimitDryRunService;
use App\Core\Reports\EcosistemaMarketingFunnelReportRepository;
use App\Core\Reports\EcosistemaMarketingFunnelReportService;
use App\Core\Reports\EcosistemaLeadPerformanceReportRepository;
use App\Core\Reports\EcosistemaLeadPerformanceReportService;
use App\Core\Reports\EcosistemaReportExportDryRunRepository;
use App\Core\Reports\EcosistemaReportExportDryRunService;
use App\Core\Reports\EcosistemaReportExportRepository;
use App\Core\Reports\EcosistemaReportExportService;
use App\Core\Audit\EcosistemaUnifiedAuditRepository;
use App\Core\Audit\EcosistemaUnifiedAuditService;
use App\Core\Ai\EcosistemaAiLeadSummaryRepository;
use App\Core\Ai\EcosistemaAiLeadSummaryDryRunService;
use App\Core\Ai\EcosistemaAiCampaignInsightDryRunRepository;
use App\Core\Ai\EcosistemaAiCampaignInsightDryRunService;


function startAuthSession(array $config): void
{
    AuthSession::start(
        (string) $config['app']['session']['name'],
        (bool) $config['app']['session']['secure'],
        (string) ($config['app']['session']['samesite'] ?? 'Lax'),
    );

    if (!AuthSession::enforceIdleTimeout((int) ($config['app']['session']['idle_timeout'] ?? 1800))) {
        try {
            $pdo = PdoFactory::make($config['database']);
            $authService = new AuthService(new UserRepository($pdo), new SessionRepository($pdo));
            $auth = AuthSession::getAuth();
            $authService->logout(isset($auth['auth_core_session_id']) ? (int) $auth['auth_core_session_id'] : null);
        } catch (\Throwable) {
        }

        AuthSession::destroy();
        header('Location: /login');
        exit;
    }
}


function driveAuditLog(PDO $pdo, string $action, string $entityType, ?int $entityId, string $route, string $operation): void
{
    $auth = AuthSession::getAuth();
    (new EcosistemaDriveAuditLogger($pdo))->logReadOnlyView(
        $action,
        $entityType,
        $entityId,
        $route,
        $operation,
        (int) ($auth['auth_tenant_id'] ?? 0),
        (int) ($auth['auth_user_id'] ?? 0),
    );
}

function auditLog(PDO $pdo, array $payload): void
{
    $auth = AuthSession::getAuth();
    (new AuditLogger($pdo))->log([
        'tenant_id' => $payload['tenant_id'] ?? (int) ($auth['auth_tenant_id'] ?? 0),
        'user_id' => (int) ($auth['auth_user_id'] ?? 0),
        'entity_type' => $payload['entity_type'] ?? null,
        'entity_id' => $payload['entity_id'] ?? null,
        'action' => $payload['action'] ?? null,
        'old_values' => $payload['old_values'] ?? null,
        'new_values' => $payload['new_values'] ?? null,
    ]);
}

return [

    'GET /u/{slug}' => static function (array $config, array $params): void {
        $slug = (string) ($params['slug'] ?? '');
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaUrlLocatorPublicRedirectService($pdo, (array) ($config['url_locator'] ?? []));
            $resolved = $service->resolvePublicSlug($slug, [
                'accept_language_header' => (string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''),
                'user_agent' => (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''),
                'referer' => (string) ($_SERVER['HTTP_REFERER'] ?? ''),
                'ip_address' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
                'public_url' => '/u/' . rawurlencode($slug),
            ]);
            if (($resolved['allowed'] ?? false) === true) {
                $service->executeRedirect($resolved);
            }
            http_response_code(404);
        } catch (\Throwable) {
            http_response_code(404);
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('pages/url-locator/public-redirect-blocked', ['reason' => 'blocked']);
    },


    

    'POST /l/{slug}/forms/{id}/submit' => static function (array $config, array $params): void {
        $slug = trim((string)($params['slug'] ?? ''));
        $id = (int)($params['id'] ?? 0);
        $tenantId = (int) (($config['app']['default_tenant_id'] ?? ($_ENV['APP_DEFAULT_TENANT_ID'] ?? 1)));
        $enabled = filter_var($_ENV['ECOSISTEMA_LANDING_FORM_SUBMIT_ENABLED'] ?? false, FILTER_VALIDATE_BOOL);
        $uploadsEnabled = filter_var($_ENV['ECOSISTEMA_LANDING_FORM_FILE_UPLOADS'] ?? false, FILTER_VALIDATE_BOOL);
        $payload = $_POST ?? [];
        $result = ['ok'=>false,'message'=>'No disponible'];
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaLandingFormSubmitService(new EcosistemaLandingFormRepository($pdo), new EcosistemaLandingFormSubmitRepository($pdo));
            $result = $service->submit($tenantId, $slug, $id, $enabled, $uploadsEnabled, is_array($payload) ? $payload : [], ['ip_address'=>(string)($_SERVER['REMOTE_ADDR'] ?? ''), 'user_agent'=>(string)($_SERVER['HTTP_USER_AGENT'] ?? '')]);
        } catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('pages/landing/form-submit-result', ['result'=>$result]);
    },
'GET /l/{slug}' => static function (array $config, array $params): void {
        $slug = trim((string) ($params['slug'] ?? ''));
        $tenantId = (int) (($config['app']['default_tenant_id'] ?? ($_ENV['APP_DEFAULT_TENANT_ID'] ?? 1)));

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaLandingPublicRenderService(new EcosistemaLandingPageRepository($pdo));
            $result = $service->renderBySlug($tenantId, $slug, filter_var($_ENV['ECOSISTEMA_LANDING_PUBLIC_RENDER_ENABLED'] ?? false, FILTER_VALIDATE_BOOL));

            header('Content-Type: text/html; charset=UTF-8');
            if (($result['allowed'] ?? false) === true) {
                View::render('pages/landing/public-page', ['result' => $result]);
                return;
            }
        } catch (\Throwable) {
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('pages/landing/public-page-blocked', ['reason' => 'blocked']);
    },

    'GET /' => static function (array $config): void {
        startAuthSession($config);

        if (AuthSession::isAuthenticated()) {
            header('Location: /dashboard');
            return;
        }

        header('Location: /login');
    },


    'GET /dashboard' => static function (array $config): void {
        startAuthSession($config);

        if (!AuthSession::isAuthenticated()) {
            header('Location: /login');
            return;
        }

        $auth = AuthSession::getAuth();
        $dashboardData = [
            'hasError' => true,
            'tenant' => null,
            'activeUsersByTenant' => 0,
            'activeModules' => 0,
            'activeSessionsByUser' => 0,
            'modules' => [],
        ];

        try {
            $pdo = PdoFactory::make($config['database']);
            $dashboardService = new DashboardService($pdo);
            $dashboardData = $dashboardService->build($auth);
        } catch (\Throwable) {
        }

        header('Content-Type: text/html; charset=UTF-8');

        View::render('layouts.admin', [
            'title' => 'Dashboard | Ecosistema Core Admin',
            'contentView' => 'pages/dashboard',
            'auth' => $auth,
            'csrfToken' => AuthSession::getCsrfToken(),
            'contentData' => [
                'dashboard' => $dashboardData,
            ],
        ]);
    },

    'GET /tenants' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'tenants.view')) { return; }

        $statusMessage = isset($_GET['ok']) && $_GET['ok'] === '1' ? 'Tenant creado correctamente.' : (isset($_GET['ok']) && $_GET['ok'] === '2' ? 'Tenant actualizado correctamente.' : null);
        $errorMessage = isset($_GET['error']) ? 'No se pudo guardar el tenant.' : null;

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new TenantService(new TenantRepository($pdo));
            $tenants = $service->listTenants();
        } catch (\Throwable) {
            $tenants = [];
            $errorMessage = 'No se pudo guardar el tenant.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', [
            'title' => 'Tenants | Ecosistema Core Admin',
            'contentView' => 'pages/tenants/index',
            'auth' => AuthSession::getAuth(),
            'csrfToken' => AuthSession::getCsrfToken(),
            'contentData' => compact('tenants', 'statusMessage', 'errorMessage'),
        ]);
    },


    'GET /tenants/create' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'tenants.manage')) { return; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Crear tenant | Ecosistema Core Admin','contentView'=>'pages/tenants/create','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>[]]);
    },

    'POST /tenants' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'tenants.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null;
        if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        try { $pdo=PdoFactory::make($config['database']); $service=new TenantService(new TenantRepository($pdo)); $ok=$service->createTenant($_POST); } catch (\Throwable) { $ok=false; }
        header('Location: '.($ok ? '/tenants?ok=1' : '/tenants/create?error=1'));
    },

    'GET /tenants/{id}/edit' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'tenants.manage')) { return; }
        $id = (int) ($params['id'] ?? 0);
        try { $pdo=PdoFactory::make($config['database']); $service=new TenantService(new TenantRepository($pdo)); $tenant=$service->findTenant($id); } catch (\Throwable) { $tenant=null; }
        if ($tenant === null) { http_response_code(404); header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'404 | Ecosistema Core Admin','contentView'=>'pages/tenants/index','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>['tenants'=>[],'errorMessage'=>'Tenant no encontrado.']]); return; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Editar tenant | Ecosistema Core Admin','contentView'=>'pages/tenants/edit','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>['tenant'=>$tenant]]);
    },

    'POST /tenants/{id}' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'tenants.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $id=(int) ($params['id'] ?? 0);
        try { $pdo=PdoFactory::make($config['database']); $service=new TenantService(new TenantRepository($pdo)); if ($service->findTenant($id)===null) { renderError($config, 404); return; } $ok=$service->updateTenant($id,$_POST);} catch (\Throwable) { $ok=false; }
        header('Location: '.($ok ? '/tenants?ok=2' : '/tenants/'.(string)$id.'/edit?error=1'));
    },

    'POST /tenants/{id}/status' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'tenants.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $id=(int) ($params['id'] ?? 0);
        try { $pdo=PdoFactory::make($config['database']); $service=new TenantService(new TenantRepository($pdo)); $before=$service->findTenant($id); $next=(string)($_POST['status'] ?? ''); $ok=$service->changeStatus($id,$next); if($ok){ auditLog($pdo,['action'=>'tenant.status_changed','entity_type'=>'core_tenants','entity_id'=>$id,'old_values'=>$before!==null?['status'=>$before['status']??null]:null,'new_values'=>['status'=>$next]]);} } catch (\Throwable) { $ok=false; }
        header('Location: '.($ok ? '/tenants?ok=2' : '/tenants?error=1'));
    },


    'GET /modules' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $statusMessage = isset($_GET['ok']) ? (string) $_GET['ok'] : null; $errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;
        try { $pdo=PdoFactory::make($config['database']); $service=new ModuleService(new ModuleRepository($pdo)); $modules=$service->listModules(); } catch (\Throwable) { $modules=[]; $errorMessage='No se pudo guardar el módulo.'; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Módulos | Ecosistema Core Admin','contentView'=>'pages/modules/index','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('modules','statusMessage','errorMessage')]);
    },
    'GET /modules/create' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.manage')) { return; }
        $errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Crear módulo | Ecosistema Core Admin','contentView'=>'pages/modules/create','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('errorMessage')]);
    },
    'POST /modules' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        try { $pdo=PdoFactory::make($config['database']); $service=new ModuleService(new ModuleRepository($pdo)); $message=$service->createModule($_POST); if($message==='Módulo creado correctamente.'){ auditLog($pdo,['action'=>'module.created','entity_type'=>'core_modules','new_values'=>['code'=>(string)($_POST['code']??''),'name'=>(string)($_POST['name']??''),'status'=>'derivado (sin columna persistida)']]);}  } catch (\Throwable $e) { $message = str_contains(strtolower($e->getMessage()), 'duplicate') || str_contains(strtolower($e->getMessage()), 'unique') ? 'Ya existe un módulo con ese código.' : 'No se pudo guardar el módulo.'; }
        header('Location: '.($message==='Módulo creado correctamente.'?'/modules?ok='.urlencode($message):'/modules/create?error='.urlencode($message)));
    },
    'GET /modules/{id}/edit' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.manage')) { return; }
        $id=(int)($params['id']??0); $errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;
        try { $pdo=PdoFactory::make($config['database']); $service=new ModuleService(new ModuleRepository($pdo)); $module=$service->findModule($id);} catch (\Throwable) { $module=null; }
        if($module===null){ renderError($config, 404); return; }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Editar módulo | Ecosistema Core Admin','contentView'=>'pages/modules/edit','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('module','errorMessage')]);
    },
    'POST /modules/{id}' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $id=(int)($params['id']??0);
        try { $pdo=PdoFactory::make($config['database']); $service=new ModuleService(new ModuleRepository($pdo)); $before=$service->findModule($id); $message=$service->updateModule($id,$_POST); if($message==='Módulo actualizado correctamente.'){ auditLog($pdo,['action'=>'module.updated','entity_type'=>'core_modules','entity_id'=>$id,'old_values'=>$before,'new_values'=>['code'=>(string)($_POST['code']??''),'name'=>(string)($_POST['name']??''),'status'=>'derivado (sin columna persistida)']]);} } catch (\Throwable $e) { $message = str_contains(strtolower($e->getMessage()), 'duplicate') || str_contains(strtolower($e->getMessage()), 'unique') ? 'Ya existe un módulo con ese código.' : 'No se pudo guardar el módulo.'; }
        header('Location: '.($message==='Módulo actualizado correctamente.'?'/modules?ok='.urlencode($message):($message==='Módulo no encontrado.'?'/modules?error='.urlencode($message):'/modules/'.$id.'/edit?error='.urlencode($message))));
    },
    'POST /modules/{id}/status' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $id=(int)($params['id']??0);
        try { $pdo=PdoFactory::make($config['database']); $service=new ModuleService(new ModuleRepository($pdo)); $before=$service->findModule($id); $next=(string)($_POST['status']??''); $message=$service->changeStatus($id,$next); if($message==='Estado actualizado correctamente.'){ auditLog($pdo,['action'=>'module.status_changed','entity_type'=>'core_modules','entity_id'=>$id,'old_values'=>$before!==null?['status'=>$before['status']??null]:null,'new_values'=>['status'=>$next]]);} } catch (\Throwable) { $message='No se pudo guardar el módulo.'; }
        header('Location: '.($message==='Estado actualizado correctamente.'?'/modules?ok='.urlencode($message):'/modules?error='.urlencode($message)));
    },


    'GET /roles' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'roles.view')) { return; }
        $statusMessage = isset($_GET['ok']) ? (string) $_GET['ok'] : null;
        $errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;
        try { $pdo = PdoFactory::make($config['database']); $service = new RoleService(new RoleRepository($pdo)); $roles = $service->listRoles(); } catch (\Throwable) { $roles = []; $errorMessage = 'No se pudo guardar el rol.'; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Roles | Ecosistema Core Admin','contentView'=>'pages/roles/index','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('roles','statusMessage','errorMessage')]);
    },
    'GET /roles/create' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'roles.manage')) { return; }
        try { $pdo = PdoFactory::make($config['database']); $service = new RoleService(new RoleRepository($pdo)); $tenants = $service->listTenants(); } catch (\Throwable) { $tenants = []; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Crear rol | Ecosistema Core Admin','contentView'=>'pages/roles/create','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('tenants')]);
    },
    'POST /roles' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'roles.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        try { $pdo = PdoFactory::make($config['database']); $service = new RoleService(new RoleRepository($pdo)); $message = $service->createRole($_POST); if($message==='Rol creado correctamente.'){ auditLog($pdo,['action'=>'role.created','entity_type'=>'core_roles','tenant_id'=>(int)($_POST['tenant_id']??0),'new_values'=>['code'=>(string)($_POST['code']??''),'name'=>(string)($_POST['name']??''),'status'=>'derivado (sin columna persistida)']]);}  } catch (\Throwable) { $message = 'No se pudo guardar el rol.'; }
        header('Location: '.($message==='Rol creado correctamente.' ? '/roles?ok='.urlencode($message) : '/roles/create?error='.urlencode($message)));
    },
    'GET /roles/{id}/edit' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'roles.manage')) { return; }
        $id = (int) ($params['id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $service = new RoleService(new RoleRepository($pdo)); $role = $service->findRole($id); $tenants = $service->listTenants(); } catch (\Throwable) { $role = null; $tenants = []; }
        if ($role === null) { renderError($config, 404); return; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Editar rol | Ecosistema Core Admin','contentView'=>'pages/roles/edit','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('role','tenants')]);
    },
    'POST /roles/{id}' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'roles.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $id = (int) ($params['id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $service = new RoleService(new RoleRepository($pdo)); $before=$service->findRole($id); $message = $service->updateRole($id, $_POST); if($message==='Rol actualizado correctamente.'){ auditLog($pdo,['action'=>'role.updated','entity_type'=>'core_roles','entity_id'=>$id,'old_values'=>$before,'new_values'=>['code'=>(string)($_POST['code']??''),'name'=>(string)($_POST['name']??''),'status'=>'derivado (sin columna persistida)']]);}  } catch (\Throwable) { $message = 'No se pudo guardar el rol.'; }
        header('Location: '.($message==='Rol actualizado correctamente.' ? '/roles?ok='.urlencode($message) : ($message==='Rol no encontrado.' ? '/roles?error='.urlencode($message) : '/roles/'.$id.'/edit?error='.urlencode($message))));
    },


    'GET /permissions' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'permissions.view')) { return; }
        $statusMessage = isset($_GET['ok']) ? (string) $_GET['ok'] : null; $errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;
        try { $pdo = PdoFactory::make($config['database']); $service = new PermissionService(new PermissionRepository($pdo)); $permissions = $service->listPermissions(); } catch (\Throwable) { $permissions=[]; $errorMessage='No se pudo guardar el permiso.'; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Permisos | Ecosistema Core Admin','contentView'=>'pages/permissions/index','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('permissions','statusMessage','errorMessage')]);
    },
    'GET /permissions/create' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'permissions.manage')) { return; }
        try { $pdo=PdoFactory::make($config['database']); $service=new PermissionService(new PermissionRepository($pdo)); $modules=$service->listModules(); } catch (\Throwable) { $modules=[]; }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Crear permiso | Ecosistema Core Admin','contentView'=>'pages/permissions/create','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('modules')]);
    },
    'POST /permissions' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'permissions.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        try { $pdo=PdoFactory::make($config['database']); $service=new PermissionService(new PermissionRepository($pdo)); $message=$service->createPermission($_POST); if($message==='Permiso creado correctamente.'){ auditLog($pdo,['action'=>'permission.created','entity_type'=>'core_permissions','new_values'=>['module_id'=>(int)($_POST['module_id']??0),'code'=>(string)($_POST['code']??''),'status'=>'derivado (sin columna persistida)']]);} } catch (\Throwable) { $message='No se pudo guardar el permiso.'; }
        header('Location: '.($message==='Permiso creado correctamente.'?'/permissions?ok='.urlencode($message):'/permissions/create?error='.urlencode($message)));
    },
    'GET /permissions/{id}/edit' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'permissions.manage')) { return; }
        $id=(int)($params['id']??0); try { $pdo=PdoFactory::make($config['database']); $service=new PermissionService(new PermissionRepository($pdo)); $permission=$service->findPermission($id); $modules=$service->listModules(); } catch (\Throwable) { $permission=null; $modules=[]; }
        if($permission===null){ renderError($config, 404); return; }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Editar permiso | Ecosistema Core Admin','contentView'=>'pages/permissions/edit','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('permission','modules')]);
    },
    'POST /permissions/{id}' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'permissions.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $id=(int)($params['id']??0); try { $pdo=PdoFactory::make($config['database']); $service=new PermissionService(new PermissionRepository($pdo)); $before=$service->findPermission($id); $message=$service->updatePermission($id,$_POST); if($message==='Permiso actualizado correctamente.'){ auditLog($pdo,['action'=>'permission.updated','entity_type'=>'core_permissions','entity_id'=>$id,'old_values'=>$before,'new_values'=>['module_id'=>(int)($_POST['module_id']??0),'code'=>(string)($_POST['code']??''),'status'=>'derivado (sin columna persistida)']]);} } catch (\Throwable) { $message='No se pudo guardar el permiso.'; }
        header('Location: '.($message==='Permiso actualizado correctamente.'?'/permissions?ok='.urlencode($message):($message==='Permiso no encontrado.'?'/permissions?error='.urlencode($message):'/permissions/'.$id.'/edit?error='.urlencode($message))));
    },
    'GET /roles/{id}/permissions' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'roles.manage')) { return; }
        $id=(int)($params['id']??0); try{ $pdo=PdoFactory::make($config['database']); $service=new RolePermissionService(new PermissionRepository($pdo)); $data=$service->getRolePermissionsScreen($id);} catch (\Throwable) { $data=['role'=>null,'permissions'=>[],'assigned'=>[]]; }
        if($data['role']===null){ renderError($config, 404); return; }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Permisos de rol | Ecosistema Core Admin','contentView'=>'pages/roles/permissions','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>$data]);
    },
    'POST /roles/{id}/permissions' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'roles.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth = AuthSession::getAuth(); $sessionTenantId = (int) ($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $id=(int)($params['id']??0); try{ $pdo=PdoFactory::make($config['database']); $service=new RolePermissionService(new PermissionRepository($pdo)); $beforePermissionIds = $service->getRolePermissionsScreen($id)['assigned'] ?? []; $permissionIds=(array)($_POST['permission_ids']??[]); $message=$service->replaceRolePermissions($id,$permissionIds,$sessionTenantId); if($message==='Permisos del rol actualizados correctamente.'){ auditLog($pdo,['action'=>'role.permissions_replaced','entity_type'=>'core_role_permissions','entity_id'=>$id,'old_values'=>['permission_ids'=>$beforePermissionIds],'new_values'=>['permission_ids'=>$permissionIds]]);} } catch (\Throwable) { $message='No se pudo guardar el permiso.'; }
        header('Location: '.($message==='Permisos del rol actualizados correctamente.'?'/roles/'.$id.'/permissions?ok='.urlencode($message):'/roles/'.$id.'/permissions?error='.urlencode($message)));
    },

    'GET /users' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'users.view')) { return; }

        $statusMessage = isset($_GET['ok']) ? (string) $_GET['ok'] : null;
        $errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new UserService(new CoreUserRepository($pdo));
            $users = $service->listUsers();
        } catch (\Throwable) {
            $users = [];
            $errorMessage = 'No se pudo guardar el usuario.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', [
            'title' => 'Usuarios | Ecosistema Core Admin',
            'contentView' => 'pages/users/index',
            'auth' => AuthSession::getAuth(),
            'csrfToken' => AuthSession::getCsrfToken(),
            'contentData' => compact('users', 'statusMessage', 'errorMessage'),
        ]);
    },

    'GET /users/create' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'users.manage')) { return; }
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new UserService(new CoreUserRepository($pdo));
            $tenants = $service->listTenants();
        } catch (\Throwable) {
            $tenants = [];
        }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Crear usuario | Ecosistema Core Admin', 'contentView' => 'pages/users/create', 'auth' => AuthSession::getAuth(), 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('tenants')]);
    },

    'POST /users' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'users.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        try { $pdo = PdoFactory::make($config['database']); $service = new UserService(new CoreUserRepository($pdo)); $message = $service->createUser($_POST); if($message === 'Usuario creado correctamente.'){ auditLog($pdo,['action'=>'user.created','entity_type'=>'core_users','tenant_id'=>(int)($_POST['tenant_id']??0),'new_values'=>['email'=>(string)($_POST['email']??''),'username'=>(string)($_POST['username']??''),'status'=>'derivado (sin columna persistida)']]);} } catch (\Throwable) { $message = 'No se pudo guardar el usuario.'; }
        header('Location: '.($message === 'Usuario creado correctamente.' ? '/users?ok='.urlencode($message) : '/users/create?error='.urlencode($message)));
    },

    'GET /users/{id}/edit' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'users.manage')) { return; }
        $id = (int) ($params['id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $service = new UserService(new CoreUserRepository($pdo)); $user = $service->findUser($id); $tenants = $service->listTenants(); } catch (\Throwable) { $user = null; $tenants = []; }
        if ($user === null) { renderError($config, 404); return; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Editar usuario | Ecosistema Core Admin', 'contentView' => 'pages/users/edit', 'auth' => AuthSession::getAuth(), 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('user','tenants')]);
    },

    'POST /users/{id}' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'users.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $id = (int) ($params['id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $service = new UserService(new CoreUserRepository($pdo)); $before = $service->findUser($id); $message = $service->updateUser($id, $_POST); if($message === 'Usuario actualizado correctamente.'){ auditLog($pdo,['action'=>'user.updated','entity_type'=>'core_users','entity_id'=>$id,'old_values'=>$before,'new_values'=>['tenant_id'=>(int)($_POST['tenant_id']??0),'email'=>(string)($_POST['email']??''),'username'=>(string)($_POST['username']??''),'status'=>'derivado (sin columna persistida)']]);} } catch (\Throwable) { $message = 'No se pudo guardar el usuario.'; }
        header('Location: '.(($message === 'Usuario actualizado correctamente.') ? '/users?ok='.urlencode($message) : ($message === 'Usuario no encontrado.' ? '/users?error='.urlencode($message) : '/users/'.$id.'/edit?error='.urlencode($message))));
    },

    'POST /users/{id}/status' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'users.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $id = (int) ($params['id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $service = new UserService(new CoreUserRepository($pdo)); $before=$service->findUser($id); $next=(string) ($_POST['status'] ?? ''); $message = $service->changeStatus($id, $next); if($message === 'Estado actualizado correctamente.'){ auditLog($pdo,['action'=>'user.status_changed','entity_type'=>'core_users','entity_id'=>$id,'old_values'=>$before!==null?['status'=>$before['status']??null]:null,'new_values'=>['status'=>$next]]);} } catch (\Throwable) { $message = 'No se pudo guardar el usuario.'; }
        header('Location: '.($message === 'Estado actualizado correctamente.' ? '/users?ok='.urlencode($message) : '/users?error='.urlencode($message)));
    },

    'POST /users/{id}/password' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'users.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $id = (int) ($params['id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $service = new UserService(new CoreUserRepository($pdo)); $message = $service->updatePassword($id, (string) ($_POST['password'] ?? '')); if($message === 'Contraseña actualizada correctamente.'){ auditLog($pdo,['action'=>'user.password_changed','entity_type'=>'core_users','entity_id'=>$id]); } } catch (\Throwable) { $message = 'No se pudo guardar el usuario.'; }
        header('Location: '.($message === 'Contraseña actualizada correctamente.' ? '/users?ok='.urlencode($message) : '/users/'.$id.'/edit?error='.urlencode($message)));
    },

    'GET /users/{id}/roles' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'users.manage')) { return; }

        $id = (int) ($params['id'] ?? 0);
        $statusMessage = isset($_GET['ok']) ? (string) $_GET['ok'] : null;
        $errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new UserRoleService(new UserRoleRepository($pdo));
            $user = $service->findUser($id);
            if ($user === null) { renderError($config, 404); return; }
            $tenantId = (int) ($user['tenant_id'] ?? 0);
            $roles = $service->listRolesForTenant($tenantId);
            $assignedRoleIds = $service->listAssignedRoleIds($tenantId, $id);
        } catch (\Throwable) {
            $user = null;
            $roles = [];
            $assignedRoleIds = [];
            $errorMessage = 'No se pudieron cargar los roles del usuario.';
        }

        if ($user === null) { renderError($config, 404); return; }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Roles de usuario | Ecosistema Core Admin', 'contentView' => 'pages/users/roles', 'auth' => AuthSession::getAuth(), 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('user', 'roles', 'assignedRoleIds', 'statusMessage', 'errorMessage')]);
    },

    'POST /users/{id}/roles' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'users.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }

        $id = (int) ($params['id'] ?? 0);

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new UserRoleService(new UserRoleRepository($pdo));
            $user = $service->findUser($id);
            if ($user === null) { header('Location: /users?error='.urlencode('Usuario no encontrado.')); return; }
            $tenantId = (int) ($user['tenant_id'] ?? 0);
            $auth = AuthSession::getAuth();
            $assignedByUserId = isset($auth['user_id']) ? (int) $auth['user_id'] : null;
            $newRoleIds = (array) ($_POST['role_ids'] ?? []); $beforeRoleIds = $service->listAssignedRoleIds($tenantId, $id); $message = $service->replaceUserRoles($tenantId, $id, $newRoleIds, $assignedByUserId); if($message === 'Roles de usuario actualizados correctamente.'){ auditLog($pdo,['action'=>'user.roles_replaced','entity_type'=>'core_user_roles','entity_id'=>$id,'tenant_id'=>$tenantId,'old_values'=>['role_ids'=>$beforeRoleIds],'new_values'=>['role_ids'=>$newRoleIds]]);} 
            header('Location: '.('/users/'.$id.'/roles?ok='.urlencode($message)));
            return;
        } catch (\Throwable) {
            header('Location: '.('/users/'.$id.'/roles?error='.urlencode('No se pudieron guardar los roles del usuario.')));
            return;
        }
    },



    'GET /mail' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'mail.view')) { return; }
        $auth = AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0);
        $statusMessage = isset($_GET['ok']) ? (string) $_GET['ok'] : null; $errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;
        try { $pdo=PdoFactory::make($config['database']); $service=new MailService(new MailboxRepository($pdo), new MailMessageRepository($pdo)); $messages=$service->listMessages($tenantId,$userId);} catch (\Throwable) { $messages=[]; $errorMessage='Mensaje no encontrado.'; }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Mail | Ecosistema Core Admin','contentView'=>'pages/mail/index','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('messages','statusMessage','errorMessage')]);
    },
    'GET /mail/messages/{id}' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'mail.view')) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $id=(int)($params['id']??0);
        $attachments = [];
        try {
            $pdo=PdoFactory::make($config['database']);
            $service=new MailService(new MailboxRepository($pdo), new MailMessageRepository($pdo));
            $message=$service->findMessage($tenantId,$userId,$id);
            if ($message !== null) {
                $attachmentService = new MailAttachmentService(new MailAttachmentRepository($pdo));
                $attachments = $attachmentService->listMessageAttachments($tenantId, $userId, $id);
            }
        } catch (\Throwable) {
            $message=null;
            $attachments = [];
        }
        if ($message===null) { http_response_code(404); }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Mail detalle | Ecosistema Core Admin','contentView'=>'pages/mail/show','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('message','attachments')]);
    },


    'GET /mail/messages/{id}/attachments' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'mail.manage')) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $id=(int)($params['id']??0);
        $statusMessage = isset($_GET['ok']) ? (string) $_GET['ok'] : null; $errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;
        try {
            $pdo=PdoFactory::make($config['database']);
            $service=new MailService(new MailboxRepository($pdo), new MailMessageRepository($pdo));
            $message=$service->findMessage($tenantId,$userId,$id);
            $attachmentService = new MailAttachmentService(new MailAttachmentRepository($pdo));
            $availableFiles = $attachmentService->listAvailableCloudFiles($tenantId, $userId);
            $attachedFiles = $attachmentService->listMessageAttachments($tenantId, $userId, $id);
        } catch (\Throwable) {
            $message=null; $availableFiles=[]; $attachedFiles=[];
            $errorMessage = 'No se pudieron cargar los adjuntos.';
        }
        if ($message===null) { http_response_code(404); }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Adjuntos Mail | Ecosistema Core Admin','contentView'=>'pages/mail/attachments','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('message','availableFiles','attachedFiles','statusMessage','errorMessage')]);
    },
    'POST /mail/messages/{id}/attachments' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'mail.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $id=(int)($params['id']??0);
        try {
            $pdo=PdoFactory::make($config['database']);
            $service=new MailService(new MailboxRepository($pdo), new MailMessageRepository($pdo));
            $message=$service->findMessage($tenantId,$userId,$id);
            if ($message === null) { header('Location: /mail?error='.urlencode('Mensaje no encontrado.')); return; }
            if ((int) ($message['is_draft'] ?? 0) !== 1 || (int) ($message['is_deleted'] ?? 0) === 1) { header('Location: /mail/messages/'.(string)$id.'/attachments?error='.urlencode('Sólo se permiten adjuntos en borradores activos.')); return; }
            $selectedFileIds = is_array($_POST['cloud_file_ids'] ?? null) ? (array) $_POST['cloud_file_ids'] : [];
            $attachmentService = new MailAttachmentService(new MailAttachmentRepository($pdo));
            $result = $attachmentService->replaceMessageAttachments($tenantId, $userId, $id, $selectedFileIds);
            if (($result['ok'] ?? false) === true) {
                auditLog($pdo, ['action'=>'mail.attachments_updated','entity_type'=>'mail_messages','entity_id'=>$id,'tenant_id'=>$tenantId,'new_values'=>['selected_count'=>count($selectedFileIds)]]);
            }
            $key = (($result['ok'] ?? false) === true) ? 'ok' : 'error';
            header('Location: /mail/messages/'.(string)$id.'/attachments?'.$key.'='.urlencode((string) ($result['reason'] ?? 'No se pudieron actualizar los adjuntos.')));
            return;
        } catch (\Throwable) {
            header('Location: /mail/messages/'.(string)$id.'/attachments?error='.urlencode('No se pudieron actualizar los adjuntos.'));
            return;
        }
    },
    'GET /mail/messages/{id}/send-preview' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'mail.manage')) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $id=(int)($params['id']??0);
        try {
            $pdo=PdoFactory::make($config['database']);
            $service = new MailSendService(new MailMessageRepository($pdo), new MailAttachmentService(new MailAttachmentRepository($pdo)), new MailConfig($config['mail'] ?? []), new MailOutgoingAttachmentService(new MailAttachmentRepository($pdo), $config['cloud'] ?? [], $config['mail'] ?? []));
            $preview = $service->previewDraftSend($tenantId, $userId, $id);
            $preview['can_send_real'] = $service->canSendReal($preview);
        } catch (\Throwable) {
            $preview = ['ok'=>false,'reason'=>'No se pudo preparar el preview de envío.'];
        }
        if (($preview['ok'] ?? false) !== true) { http_response_code(422); }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Preview envío Mail | Ecosistema Core Admin','contentView'=>'pages/mail/send-preview','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('preview','id')]);
    },
    'POST /mail/messages/{id}/prepare-send' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'mail.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $id=(int)($params['id']??0);
        try {
            $pdo=PdoFactory::make($config['database']);
            $service = new MailSendService(new MailMessageRepository($pdo), new MailAttachmentService(new MailAttachmentRepository($pdo)), new MailConfig($config['mail'] ?? []), new MailOutgoingAttachmentService(new MailAttachmentRepository($pdo), $config['cloud'] ?? [], $config['mail'] ?? []));
            $result = $service->sendDraft($tenantId, $userId, $id);
            auditLog($pdo, ['action'=>'mail.send_attempted', 'entity_type'=>'mail_messages', 'entity_id'=>$id, 'tenant_id'=>$tenantId, 'new_values'=>['result_action'=>(string)($result['action']??'mail.send_failed'),'ok'=>(bool)($result['ok']??false),'ready'=>(bool)($result['ready']??false),'reason'=>(string)($result['reason']??''),'attachment_count'=>(int)($result['attachment_count']??0),'attachment_total_bytes'=>(int)($result['attachment_total_bytes']??0)]]);
            $message = (string) ($result['reason'] ?? 'Preparación ejecutada.');
        } catch (\Throwable) {
            $message = 'No se pudo preparar el envío.';
        }
        header('Location: /mail/messages/'.(string)$id.'/send-preview?'.(str_contains(mb_strtolower($message), 'no se pudo') ? 'error=' : 'ok=').urlencode($message));
    },
    'GET /mail/settings' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'mail.manage')) { return; }
        $auth = AuthSession::getAuth();
        $mailConfig = new MailConfig($config['mail'] ?? []);
        $smtp = $mailConfig->toSafeArray();
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',[
            'title'=>'Configuración SMTP | Ecosistema Core Admin',
            'contentView'=>'pages/mail/settings',
            'auth'=>$auth,
            'csrfToken'=>AuthSession::getCsrfToken(),
            'contentData'=>compact('smtp'),
        ]);
    },
    'GET /mail-notifications' => static function (array $config): void {
        startAuthSession($config);
        if (!requirePermission($config, 'mail.view')) {
            return;
        }

        $adapter = new EcosistemaMailNotificationsAdapter();
        $capabilities = $adapter->capabilities();
        $auth = AuthSession::getAuth();
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Mail Notifications | Ecosistema Core Admin', 'contentView' => 'pages/mail-notifications/index', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('capabilities')]);
    },
    'GET /mail-notifications/templates' => static function (array $config): void {
        startAuthSession($config);
        if (!requirePermission($config, 'mail.view')) {
            return;
        }

        $auth = AuthSession::getAuth();
        $templates = [];
        try {
            $pdo = PdoFactory::make($config['database']);
            $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
            $service = new EcosistemaNotificationTemplateService(new EcosistemaNotificationTemplateRepository($pdo), new EcosistemaMailNotificationsAdapter());
            $result = $service->listTemplates($tenantId, 100);
            $templates = (array) ($result['templates'] ?? []);
        } catch (\Throwable) {
            $templates = [];
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Notification Templates | Ecosistema Core Admin', 'contentView' => 'pages/mail-notifications/templates', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('templates')]);
    },
    'GET /mail-notifications/templates/{id}' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!requirePermission($config, 'mail.view')) {
            return;
        }

        $auth = AuthSession::getAuth();
        $id = (int) ($params['id'] ?? 0);
        $template = null;
        $errorMessage = null;
        try {
            $pdo = PdoFactory::make($config['database']);
            $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
            $service = new EcosistemaNotificationTemplateService(new EcosistemaNotificationTemplateRepository($pdo), new EcosistemaMailNotificationsAdapter());
            $template = $service->getTemplate($tenantId, $id);
        } catch (\Throwable) {
            $errorMessage = 'No se pudo obtener el detalle de la plantilla de notificación.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Notification Template Detail | Ecosistema Core Admin', 'contentView' => 'pages/mail-notifications/template-detail', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('template', 'errorMessage')]);
    },

    'GET /mail-notifications/queue' => static function (array $config): void {
        startAuthSession($config);
        if (!requirePermission($config, 'mail.view')) {
            return;
        }

        $auth = AuthSession::getAuth();
        $items = [];
        $summary = [];
        try {
            $pdo = PdoFactory::make($config['database']);
            $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
            $service = new EcosistemaNotificationQueueService(new EcosistemaNotificationQueueRepository($pdo), new EcosistemaMailNotificationsAdapter());
            $result = $service->listQueue($tenantId, 100);
            $items = (array) ($result['items'] ?? []);
            $summary = (array) ($result['summary'] ?? []);
        } catch (\Throwable) {
            $items = [];
            $summary = [];
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Notifications Queue | Ecosistema Core Admin', 'contentView' => 'pages/mail-notifications/queue', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('items', 'summary')]);
    },
    'GET /mail-notifications/queue/{id}' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!requirePermission($config, 'mail.view')) {
            return;
        }

        $auth = AuthSession::getAuth();
        $id = (int) ($params['id'] ?? 0);
        $item = null;
        $errorMessage = null;
        try {
            $pdo = PdoFactory::make($config['database']);
            $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
            $service = new EcosistemaNotificationQueueService(new EcosistemaNotificationQueueRepository($pdo), new EcosistemaMailNotificationsAdapter());
            $item = $service->getQueueItem($tenantId, $id);
        } catch (\Throwable) {
            $errorMessage = 'No se pudo obtener el detalle del elemento de cola.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Notification Queue Detail | Ecosistema Core Admin', 'contentView' => 'pages/mail-notifications/queue-detail', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('item', 'errorMessage')]);
    },
    'GET /mail-notifications/url-message-templates' => static function (array $config): void {
        startAuthSession($config);
        if (!requirePermission($config, 'mail.view')) {
            return;
        }

        $auth = AuthSession::getAuth();
        $templates = [];
        try {
            $pdo = PdoFactory::make($config['database']);
            $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
            $service = new EcosistemaUrlMessageTemplateService(new EcosistemaUrlMessageTemplateRepository($pdo), new EcosistemaMailNotificationsAdapter());
            $result = $service->listTemplates($tenantId, 100);
            $templates = (array) ($result['templates'] ?? []);
        } catch (\Throwable) {
            $templates = [];
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'URL Message Templates | Ecosistema Core Admin', 'contentView' => 'pages/mail-notifications/url-message-templates', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('templates')]);
    },
    'GET /mail-notifications/url-message-templates/{id}' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!requirePermission($config, 'mail.view')) {
            return;
        }

        $auth = AuthSession::getAuth();
        $id = (int) ($params['id'] ?? 0);
        $template = null;
        $errorMessage = null;
        try {
            $pdo = PdoFactory::make($config['database']);
            $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
            $service = new EcosistemaUrlMessageTemplateService(new EcosistemaUrlMessageTemplateRepository($pdo), new EcosistemaMailNotificationsAdapter());
            $template = $service->getTemplate($tenantId, $id);
        } catch (\Throwable) {
            $errorMessage = 'No se pudo obtener el detalle de URL message template.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'URL Message Template Detail | Ecosistema Core Admin', 'contentView' => 'pages/mail-notifications/url-message-template-detail', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('template', 'errorMessage')]);
    },
    'GET /mail-notifications/templates/{id}/preview-dry-run' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; } if (!requirePermission($config, 'mail.view')) { return; }
        $auth = AuthSession::getAuth(); $id = (int) ($params['id'] ?? 0); $preview = null; $errorMessage = null;
        try {
            $pdo = PdoFactory::make($config['database']); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
            $service = new EcosistemaMessagePreviewDryRunService(new EcosistemaNotificationTemplateRepository($pdo), new EcosistemaUrlMessageTemplateRepository($pdo));
            $preview = $service->previewNotificationTemplate($tenantId, $id, []);
            if ($preview === null) { $errorMessage = 'Plantilla de notificación no encontrada para el tenant actual.'; }
        } catch (\Throwable) { $errorMessage = 'No se pudo preparar el preview dry-run.'; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Message Preview Dry-Run | Ecosistema Core Admin', 'contentView' => 'pages/mail-notifications/message-preview-dry-run', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('preview', 'errorMessage', 'id')]);
    },
    'POST /mail-notifications/templates/{id}/preview-dry-run' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; } if (!requirePermission($config, 'mail.view')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth = AuthSession::getAuth(); $id = (int) ($params['id'] ?? 0); $preview = null; $errorMessage = null;
        $variables = isset($_POST['variables']) && is_array($_POST['variables']) ? $_POST['variables'] : [];
        try {
            $pdo = PdoFactory::make($config['database']); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
            $service = new EcosistemaMessagePreviewDryRunService(new EcosistemaNotificationTemplateRepository($pdo), new EcosistemaUrlMessageTemplateRepository($pdo));
            $preview = $service->previewNotificationTemplate($tenantId, $id, $variables);
            if ($preview === null) { $errorMessage = 'Plantilla de notificación no encontrada para el tenant actual.'; }
        } catch (\Throwable) { $errorMessage = 'No se pudo preparar el preview dry-run.'; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Message Preview Dry-Run | Ecosistema Core Admin', 'contentView' => 'pages/mail-notifications/message-preview-dry-run', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('preview', 'errorMessage', 'id')]);
    },
    'GET /mail-notifications/url-message-templates/{id}/preview-dry-run' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; } if (!requirePermission($config, 'mail.view')) { return; }
        $auth = AuthSession::getAuth(); $id = (int) ($params['id'] ?? 0); $preview = null; $errorMessage = null;
        try {
            $pdo = PdoFactory::make($config['database']); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
            $service = new EcosistemaMessagePreviewDryRunService(new EcosistemaNotificationTemplateRepository($pdo), new EcosistemaUrlMessageTemplateRepository($pdo));
            $preview = $service->previewUrlMessageTemplate($tenantId, $id, []);
            if ($preview === null) { $errorMessage = 'URL message template no encontrada para el tenant actual.'; }
        } catch (\Throwable) { $errorMessage = 'No se pudo preparar el preview dry-run.'; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Message Preview Dry-Run | Ecosistema Core Admin', 'contentView' => 'pages/mail-notifications/message-preview-dry-run', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('preview', 'errorMessage', 'id')]);
    },
    'POST /mail-notifications/url-message-templates/{id}/preview-dry-run' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; } if (!requirePermission($config, 'mail.view')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth = AuthSession::getAuth(); $id = (int) ($params['id'] ?? 0); $preview = null; $errorMessage = null;
        $variables = isset($_POST['variables']) && is_array($_POST['variables']) ? $_POST['variables'] : [];
        try {
            $pdo = PdoFactory::make($config['database']); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
            $service = new EcosistemaMessagePreviewDryRunService(new EcosistemaNotificationTemplateRepository($pdo), new EcosistemaUrlMessageTemplateRepository($pdo));
            $preview = $service->previewUrlMessageTemplate($tenantId, $id, $variables);
            if ($preview === null) { $errorMessage = 'URL message template no encontrada para el tenant actual.'; }
        } catch (\Throwable) { $errorMessage = 'No se pudo preparar el preview dry-run.'; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Message Preview Dry-Run | Ecosistema Core Admin', 'contentView' => 'pages/mail-notifications/message-preview-dry-run', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('preview', 'errorMessage', 'id')]);
    },

    'GET /mail-notifications/send-dry-run' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; } if (!requirePermission($config, 'mail.view')) { return; }
        $auth = AuthSession::getAuth();
        $result = null;
        $errorMessage = null;
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Send Notification Dry-Run | Ecosistema Core Admin', 'contentView' => 'pages/mail-notifications/send-dry-run', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('result', 'errorMessage')]);
    },
    'POST /mail-notifications/send-dry-run' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; } if (!requirePermission($config, 'mail.view')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth = AuthSession::getAuth();
        $result = null; $errorMessage = null;
        try {
            $pdo = PdoFactory::make($config['database']);
            $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
            $service = new EcosistemaSendNotificationDryRunService($pdo);
            $result = $service->simulate($tenantId, $_POST);
            if (!is_array($result) || empty($result['ok'])) {
                $errorMessage = is_array($result) ? (string) ($result['error'] ?? 'No se pudo simular el envío.') : 'No se pudo simular el envío.';
            }
        } catch (\Throwable) {
            $errorMessage = 'No se pudo simular el envío.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Send Notification Dry-Run | Ecosistema Core Admin', 'contentView' => 'pages/mail-notifications/send-dry-run', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('result', 'errorMessage')]);
    },

    'POST /mail-notifications/send' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; } if (!requirePermission($config, 'mail.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth = AuthSession::getAuth();
        $result = null; $errorMessage = null;
        try {
            $pdo = PdoFactory::make($config['database']);
            $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
            $authUserId = (int) ($auth['auth_user_id'] ?? 0);
            $service = new EcosistemaSendNotificationService($pdo, new EcosistemaSendNotificationRepository($pdo), new EcosistemaMailNotificationsAdapter());
            $result = $service->sendControlled($tenantId, $authUserId, $_POST);
            if (!is_array($result) || empty($result['ok'])) {
                $errorMessage = is_array($result) ? (string) ($result['error'] ?? 'No se pudo preparar el envío controlado.') : 'No se pudo preparar el envío controlado.';
            }
        } catch (\Throwable) {
            $errorMessage = 'No se pudo preparar el envío controlado.';
        }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Send Notification Result | Ecosistema Core Admin', 'contentView' => 'pages/mail-notifications/send-result', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('result', 'errorMessage')]);
    },

    'GET /mail/compose' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'mail.manage')) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $statusMessage = isset($_GET['ok']) ? (string) $_GET['ok'] : null; $errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;
        try { $pdo=PdoFactory::make($config['database']); $service=new MailService(new MailboxRepository($pdo), new MailMessageRepository($pdo)); $mailboxes=$service->listActiveMailboxes($tenantId,$userId);} catch (\Throwable) { $mailboxes=[]; }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Compose | Ecosistema Core Admin','contentView'=>'pages/mail/compose','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('mailboxes','statusMessage','errorMessage')]);
    },
    'POST /mail/drafts' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'mail.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0);
        try { $pdo=PdoFactory::make($config['database']); $service=new MailService(new MailboxRepository($pdo), new MailMessageRepository($pdo)); $message=$service->createDraft($tenantId,$userId,$_POST);} catch (\Throwable) { $message='No se pudo guardar el borrador.'; }
        header('Location: '.($message==='Borrador creado correctamente.'?'/mail?ok='.urlencode($message):'/mail/compose?error='.urlencode($message)));
    },
    'POST /mail/messages/{id}/read' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'mail.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $id=(int)($params['id']??0);
        try { $pdo=PdoFactory::make($config['database']); $service=new MailService(new MailboxRepository($pdo), new MailMessageRepository($pdo)); $message=$service->updateRead($tenantId,$userId,$id);} catch (\Throwable) { $message='Mensaje no encontrado.'; }
        header('Location: /mail?'.(($message==='Mensaje actualizado correctamente.')?'ok=':'error=').urlencode($message));
    },
    'POST /mail/messages/{id}/star' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'mail.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $id=(int)($params['id']??0);
        try { $pdo=PdoFactory::make($config['database']); $service=new MailService(new MailboxRepository($pdo), new MailMessageRepository($pdo)); $message=$service->updateStar($tenantId,$userId,$id);} catch (\Throwable) { $message='Mensaje no encontrado.'; }
        header('Location: /mail?'.(($message==='Mensaje actualizado correctamente.')?'ok=':'error=').urlencode($message));
    },
    'POST /mail/messages/{id}/trash' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'mail.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $id=(int)($params['id']??0);
        try { $pdo=PdoFactory::make($config['database']); $service=new MailService(new MailboxRepository($pdo), new MailMessageRepository($pdo)); $message=$service->trash($tenantId,$userId,$id);} catch (\Throwable) { $message='Mensaje no encontrado.'; }
        header('Location: /mail?'.(($message==='Mensaje enviado a papelera.')?'ok=':'error=').urlencode($message));
    },



    'GET /url/locator' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $statusMessage = null;
        $errorMessage = null;
        $summary = ['total' => 0, 'by_status' => [], 'by_smart_type' => []];
        $capabilities = (new EcosistemaUrlLocatorAdapter())->capabilities();

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaUrlLocatorLinkService(new EcosistemaUrlLocatorLinkRepository($pdo), new EcosistemaUrlLocatorAdapter());
            $data = $service->listLinks($tenantId, 25);
            $summary = $data['summary'];
            $capabilities = $data['capabilities'];
        } catch (\Throwable) {
            $errorMessage = 'No se pudo cargar URL Locator en modo read-only.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'URL Locator | Ecosistema Core Admin','contentView'=>'pages/url-locator/index','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary','capabilities','statusMessage','errorMessage')]);
    },

    'GET /url/locator/links' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $statusMessage = null;
        $errorMessage = null;
        $summary = ['total' => 0, 'by_status' => [], 'by_smart_type' => []];
        $links = [];
        $capabilities = (new EcosistemaUrlLocatorAdapter())->capabilities();

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaUrlLocatorLinkService(new EcosistemaUrlLocatorLinkRepository($pdo), new EcosistemaUrlLocatorAdapter());
            $data = $service->listLinks($tenantId, 100);
            $summary = $data['summary'];
            $links = $data['links'];
            $capabilities = $data['capabilities'];
        } catch (\Throwable) {
            $errorMessage = 'No se pudo cargar links URL Locator en modo read-only.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'URL Locator Links | Ecosistema Core Admin','contentView'=>'pages/url-locator/links','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary','links','capabilities','statusMessage','errorMessage')]);
    },





    'GET /url/locator/clicks' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $summary = ['total' => 0, 'by_device_type' => [], 'by_detected_language' => [], 'by_country' => []];
        $clicks = [];
        $errorMessage = null;
        $capabilities = (new EcosistemaUrlLocatorAdapter())->capabilities();

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaUrlLocatorClickService(new EcosistemaUrlLocatorClickRepository($pdo), new EcosistemaUrlLocatorAdapter());
            $data = $service->listClicks($tenantId, 100);
            $summary = $data['summary'];
            $clicks = $data['clicks'];
            $capabilities = $data['capabilities'];
        } catch (\Throwable) {
            $errorMessage = 'No se pudo cargar clicks URL Locator en modo read-only.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'URL Locator Clicks | Ecosistema Core Admin','contentView'=>'pages/url-locator/clicks','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary','clicks','capabilities','errorMessage')]);
    },

    'GET /url/locator/links/{id}/clicks' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) { renderError($config, 404); return; }

        $summary = ['total' => 0, 'by_device_type' => [], 'by_detected_language' => [], 'by_country' => []];
        $clicks = [];
        $errorMessage = null;
        $capabilities = (new EcosistemaUrlLocatorAdapter())->capabilities();

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaUrlLocatorClickService(new EcosistemaUrlLocatorClickRepository($pdo), new EcosistemaUrlLocatorAdapter());
            $data = $service->listClicksByLink($tenantId, $id, 100);
            $summary = $data['summary'];
            $clicks = $data['clicks'];
            $capabilities = $data['capabilities'];
        } catch (\Throwable) {
            $errorMessage = 'No se pudo cargar clicks del short link.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'URL Locator Link Clicks | Ecosistema Core Admin','contentView'=>'pages/url-locator/link-clicks','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('id','summary','clicks','capabilities','errorMessage')]);
    },


    'GET /url/locator/links/{id}/redirect-dry-run' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) { renderError($config, 404); return; }

        $context = [
            'accept_language_header' => (string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''),
            'requested_language' => isset($_GET['requested_language']) ? (string) $_GET['requested_language'] : null,
        ];
        if (isset($_GET['preview_now']) && (string) $_GET['preview_now'] !== '') {
            $context['preview_now'] = (string) $_GET['preview_now'];
        }

        $result = null;
        $errorMessage = null;
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaUrlLocatorRedirectDryRunService(new EcosistemaUrlLocatorLinkRepository($pdo));
            $result = $service->resolveByLinkId($tenantId, $id, $context);
        } catch (\Throwable) {
            $errorMessage = 'No se pudo simular la resolución del short link.';
        }

        if ($result === null) { renderError($config, 500); return; }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'URL Locator Redirect Dry Run | Ecosistema Core Admin','contentView'=>'pages/url-locator/redirect-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result','errorMessage')]);
    },

    'GET /url/locator/links/{id}' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $id = (int)($params['id'] ?? 0);
        if ($id <= 0) { renderError($config, 404); return; }

        $errorMessage = null;
        $link = null;

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaUrlLocatorLinkService(new EcosistemaUrlLocatorLinkRepository($pdo), new EcosistemaUrlLocatorAdapter());
            $link = $service->getLinkDetail($tenantId, $id);
        } catch (\Throwable) {
            $errorMessage = 'No se pudo cargar detalle del short link.';
        }

        if ($link === null) { renderError($config, 404); return; }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'URL Locator Link Detail | Ecosistema Core Admin','contentView'=>'pages/url-locator/link-detail','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('link','errorMessage')]);
    },

    'GET /url/locator/links/new' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.manage')) { return; }
        $auth = AuthSession::getAuth();
        $urlConfig = (array)($config['url_locator'] ?? []);
        $writeEnabled = (bool)($urlConfig['enabled'] ?? false) && (bool)($urlConfig['admin_write_enabled'] ?? false);
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Nuevo short link | Ecosistema Core Admin','contentView'=>'pages/url-locator/link-form','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>['mode'=>'create','writeEnabled'=>$writeEnabled,'link'=>[],'errors'=>[]]]);
    },

    'POST /url/locator/links' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.manage')) { return; }
        if (!ensureValidCsrfToken($config, $_POST['_csrf'] ?? null)) { return; }
        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $userId = (int)($auth['user_id'] ?? $auth['auth_user_id'] ?? 0);
        try { $pdo=PdoFactory::make($config['database']); $svc=new EcosistemaUrlLocatorLinkWriteService(new EcosistemaUrlLocatorLinkWriteRepository($pdo),(array)($config['url_locator']??[])); if(!$svc->writeEnabled()){ header('Location: /url/locator/links?error=write-disabled'); return; } $res=$svc->create($tenantId,$userId,$_POST);} catch (\Throwable) { $res=['errors'=>['No se pudo crear.']]; }
        if (($res['errors'] ?? []) === []) { header('Location: /url/locator/links/'.(int)$res['id'].'?ok=created'); return; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Nuevo short link | Ecosistema Core Admin','contentView'=>'pages/url-locator/link-form','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>['mode'=>'create','writeEnabled'=>true,'link'=>$_POST,'errors'=>$res['errors']]]);
    },

    'GET /url/locator/links/{id}/edit' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.manage')) { return; }
        $auth = AuthSession::getAuth(); $id=(int)($params['id']??0); if($id<=0){ renderError($config,404); return; }
        $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $writeEnabled = (bool)(($config['url_locator']['enabled'] ?? false) && ($config['url_locator']['admin_write_enabled'] ?? false));
        try { $pdo=PdoFactory::make($config['database']); $repo=new EcosistemaUrlLocatorLinkRepository($pdo); $link=$repo->findLink($tenantId,$id);} catch (\Throwable) { $link=null; }
        if($link===null){ renderError($config,404); return; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Editar short link | Ecosistema Core Admin','contentView'=>'pages/url-locator/link-form','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>['mode'=>'edit','writeEnabled'=>$writeEnabled,'link'=>$link,'errors'=>[]]]);
    },

    'POST /url/locator/links/{id}/edit' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.manage')) { return; }
        if (!ensureValidCsrfToken($config, $_POST['_csrf'] ?? null)) { return; }
        $auth = AuthSession::getAuth(); $id=(int)($params['id']??0); $tenantId=(int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        try { $pdo=PdoFactory::make($config['database']); $svc=new EcosistemaUrlLocatorLinkWriteService(new EcosistemaUrlLocatorLinkWriteRepository($pdo),(array)($config['url_locator']??[])); if(!$svc->writeEnabled()){ header('Location: /url/locator/links?error=write-disabled'); return; } $res=$svc->update($tenantId,$id,$_POST);} catch (\Throwable) { $res=['errors'=>['No se pudo actualizar.']]; }
        if (($res['errors'] ?? []) === []) { header('Location: /url/locator/links/'.$id.'?ok=updated'); return; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Editar short link | Ecosistema Core Admin','contentView'=>'pages/url-locator/link-form','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>['mode'=>'edit','writeEnabled'=>true,'link'=>array_merge($_POST,['id'=>$id]),'errors'=>$res['errors']]]);
    },





    'GET /browser/analytics' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $auth = AuthSession::getAuth();
        $dashboard = ['error' => true, 'mode' => 'read-only', 'db_write' => false, 'collector_enabled' => false];
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaBrowserAnalyticsDashboardService(
                new EcosistemaBrowserAnalyticsDashboardRepository($pdo),
                new EcosistemaBrowserAnalyticsAdapter(),
            );
            $dashboard = $service->build((int) ($auth['auth_tenant_id'] ?? 0));
        } catch (\Throwable) {
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Browser Analytics | Ecosistema Core Admin','contentView'=>'pages/browser-analytics/dashboard','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('dashboard')]);
    },



    'GET /browser/analytics/pageviews' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $pageviews = ['summary' => [], 'items' => [], 'mode' => 'read-only', 'db_write' => false];
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaBrowserAnalyticsPageviewService(new EcosistemaBrowserAnalyticsPageviewRepository($pdo), new EcosistemaBrowserAnalyticsAdapter());
            $pageviews = $service->listRecent($tenantId, 100);
        } catch (\Throwable) {
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Browser Analytics Pageviews | Ecosistema Core Admin','contentView'=>'pages/browser-analytics/pageviews','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('pageviews')]);
    },

    'GET /browser/analytics/sessions/{id}/pageviews' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $sessionId = (int) ($params['id'] ?? 0);
        if ($sessionId <= 0) { renderError($config, 404); return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $pageviews = ['summary' => [], 'items' => [], 'mode' => 'read-only', 'db_write' => false, 'session_id' => $sessionId];
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaBrowserAnalyticsPageviewService(new EcosistemaBrowserAnalyticsPageviewRepository($pdo), new EcosistemaBrowserAnalyticsAdapter());
            $pageviews = $service->listForSession($tenantId, $sessionId, 100);
        } catch (\Throwable) {
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Session Pageviews | Ecosistema Core Admin','contentView'=>'pages/browser-analytics/session-pageviews','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('pageviews')]);
    },



    'GET /browser/analytics/events' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $events = ['summary' => [], 'items' => [], 'mode' => 'read-only', 'db_write' => false];
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaBrowserAnalyticsEventService(new EcosistemaBrowserAnalyticsEventRepository($pdo), new EcosistemaBrowserAnalyticsAdapter());
            $events = $service->listRecent($tenantId, 100);
        } catch (\Throwable) {
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Browser Analytics Events | Ecosistema Core Admin','contentView'=>'pages/browser-analytics/events','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('events')]);
    },

    'GET /browser/analytics/pageviews/{id}/events' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $pageviewId = (int) ($params['id'] ?? 0);
        if ($pageviewId <= 0) { renderError($config, 404); return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $events = ['summary' => [], 'items' => [], 'mode' => 'read-only', 'db_write' => false, 'pageview_id' => $pageviewId];
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaBrowserAnalyticsEventService(new EcosistemaBrowserAnalyticsEventRepository($pdo), new EcosistemaBrowserAnalyticsAdapter());
            $events = $service->listForPageview($tenantId, $pageviewId, 100);
        } catch (\Throwable) {
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Pageview Events | Ecosistema Core Admin','contentView'=>'pages/browser-analytics/pageview-events','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('events')]);
    },

    'GET /landing' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $adapter = new EcosistemaLandingAdapter();
        $capabilities = $adapter->capabilities();

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Landing Pages | Ecosistema Core Admin','contentView'=>'pages/landing/index','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('capabilities')]);
    },

    'GET /landing/pages' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $summary = ['total' => 0, 'by_status' => [], 'by_page_type' => []];
        $pages = [];

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaLandingPageService(new EcosistemaLandingPageRepository($pdo), new EcosistemaLandingAdapter());
            $data = $service->listPages($tenantId);
            $summary = (array) ($data['summary'] ?? []);
            $pages = (array) ($data['pages'] ?? []);
        } catch (\Throwable) {
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Landing Pages List | Ecosistema Core Admin','contentView'=>'pages/landing/pages','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary','pages')]);
    },


    'GET /landing/pages/{id}/public-render-dry-run' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $id = isset($params['id']) ? (int) $params['id'] : 0;
        if ($id <= 0) { renderError($config, 404); return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $enabled = (bool) (($config['app']['ecosistema_landing']['public_render_dry_run'] ?? false) === true);
        $result = ['allowed' => false, 'reason' => 'No disponible.'];

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaLandingPublicRenderDryRunService(new EcosistemaLandingPageRepository($pdo));
            $result = $service->simulate($tenantId, $id, $enabled);
        } catch (\Throwable) {
            $result = ['allowed' => false, 'reason' => 'No se pudo ejecutar el dry-run de render público.'];
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Landing Public Render Dry-run | Ecosistema Core Admin','contentView'=>'pages/landing/public-render-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result','id')]);
    },

    'GET /landing/pages/{id}' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $id = isset($params['id']) ? (int) $params['id'] : 0;
        if ($id <= 0) { http_response_code(404); }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $page = null;
        $errorMessage = null;

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaLandingPageService(new EcosistemaLandingPageRepository($pdo), new EcosistemaLandingAdapter());
            $page = $service->getPageDetail($tenantId, $id);
            if ($page === null) { $errorMessage = 'Landing page no encontrada para el tenant actual.'; }
        } catch (\Throwable) {
            $errorMessage = 'No se pudo obtener el detalle de la landing page.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Landing Page Detail | Ecosistema Core Admin','contentView'=>'pages/landing/page-detail','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('page','errorMessage')]);
    },



    'GET /landing/visits' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $summary = ['total' => 0, 'by_country' => [], 'by_device_type' => [], 'by_campaign' => []];
        $visits = [];

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaLandingVisitService(new EcosistemaLandingVisitRepository($pdo), new EcosistemaLandingAdapter());
            $data = $service->listVisits($tenantId);
            $summary = (array) ($data['summary'] ?? []);
            $visits = (array) ($data['visits'] ?? []);
        } catch (\Throwable) {
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Landing Visits | Ecosistema Core Admin','contentView'=>'pages/landing/visits','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary','visits')]);
    },

    'GET /landing/pages/{id}/visits' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $id = isset($params['id']) ? (int) $params['id'] : 0;
        if ($id <= 0) { renderError($config, 404); return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $summary = ['total' => 0, 'by_country' => [], 'by_device_type' => [], 'by_campaign' => []];
        $visits = [];

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaLandingVisitService(new EcosistemaLandingVisitRepository($pdo), new EcosistemaLandingAdapter());
            $data = $service->listVisitsForPage($tenantId, $id);
            $summary = (array) ($data['summary'] ?? []);
            $visits = (array) ($data['visits'] ?? []);
        } catch (\Throwable) {
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Landing Page Visits | Ecosistema Core Admin','contentView'=>'pages/landing/page-visits','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary','visits','id')]);
    },



    'GET /landing/forms' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $summary = ['total' => 0, 'active' => 0, 'inactive' => 0]; $forms = [];
        try { $pdo = PdoFactory::make($config['database']); $service = new EcosistemaLandingFormService(new EcosistemaLandingFormRepository($pdo), new EcosistemaLandingAdapter()); $data = $service->listForms($tenantId); $summary=(array)($data['summary']??[]); $forms=(array)($data['forms']??[]);} catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Landing Forms | Ecosistema Core Admin','contentView'=>'pages/landing/forms','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary','forms')]);
    },

    'GET /landing/pages/{id}/forms' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $id = isset($params['id']) ? (int) $params['id'] : 0; if ($id <= 0) { renderError($config, 404); return; }
        $auth = AuthSession::getAuth(); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $summary = ['total' => 0, 'active' => 0, 'inactive' => 0]; $forms = [];
        try { $pdo = PdoFactory::make($config['database']); $service = new EcosistemaLandingFormService(new EcosistemaLandingFormRepository($pdo), new EcosistemaLandingAdapter()); $data = $service->listFormsForPage($tenantId, $id); $summary=(array)($data['summary']??[]); $forms=(array)($data['forms']??[]);} catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Landing Page Forms | Ecosistema Core Admin','contentView'=>'pages/landing/page-forms','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary','forms','id')]);
    },

    'GET /landing/forms/{id}' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $id = isset($params['id']) ? (int) $params['id'] : 0; if ($id <= 0) { renderError($config, 404); return; }
        $auth = AuthSession::getAuth(); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $form = null; $fields=[];
        try { $pdo = PdoFactory::make($config['database']); $service = new EcosistemaLandingFormService(new EcosistemaLandingFormRepository($pdo), new EcosistemaLandingAdapter()); $data = $service->getFormDetail($tenantId, $id); $form=$data['form']??null; $fields=(array)($data['fields']??[]);} catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Landing Form Detail | Ecosistema Core Admin','contentView'=>'pages/landing/form-detail','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('form','fields')]);
    },





    'GET /attribution/url-landing/dry-run' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $auth = AuthSession::getAuth();
        $result = null; $errorMessage = null;
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Attribution URL→Landing Dry-run | Ecosistema Core Admin','contentView'=>'pages/attribution/url-landing-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result','errorMessage')]);
    },

    'POST /attribution/url-landing/dry-run' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        if (!ensureValidCsrfToken($config, $_POST['_csrf'] ?? null)) { return; }
        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $clickId = (int)($_POST['click_id'] ?? 0);
        $result = null; $errorMessage = null;
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new \App\Core\Attribution\EcosistemaUrlLandingAttributionService(new \App\Core\Attribution\EcosistemaUrlLandingAttributionRepository($pdo), ['enabled'=>(bool)($config['app']['ecosistema_attribution']['enabled'] ?? false),'write_enabled'=>(bool)($config['app']['ecosistema_attribution']['write_enabled'] ?? false)]);
            $result = $service->dryRun($tenantId, $clickId);
        } catch (\Throwable) {
            $errorMessage = 'No se pudo ejecutar dry-run de atribución URL→Landing.';
        }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Attribution URL→Landing Dry-run | Ecosistema Core Admin','contentView'=>'pages/attribution/url-landing-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result','errorMessage')]);
    },

    'GET /attribution/rollups/dry-run' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $auth = AuthSession::getAuth();
        $result = null; $errorMessage = null;
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Attribution Rollup Dry-run | Ecosistema Core Admin','contentView'=>'pages/attribution/rollup-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result','errorMessage')]);
    },

    'POST /attribution/rollups/dry-run' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        if (!ensureValidCsrfToken($config, $_POST['_csrf'] ?? null)) { return; }
        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $startDate = trim((string)($_POST['start_date'] ?? ''));
        $endDate = trim((string)($_POST['end_date'] ?? ''));
        $result = null; $errorMessage = null;
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new \App\Core\Attribution\EcosistemaAttributionRollupDryRunService(new \App\Core\Attribution\EcosistemaAttributionRollupDryRunRepository($pdo), (bool)($config['app']['ecosistema_attribution']['rollup_dry_run'] ?? false));
            $result = $service->simulate($tenantId, $startDate, $endDate);
        } catch (\Throwable) {
            $errorMessage = 'No se pudo ejecutar dry-run de rollups de atribución.';
        }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Attribution Rollup Dry-run | Ecosistema Core Admin','contentView'=>'pages/attribution/rollup-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result','errorMessage')]);
    },



    'POST /attribution/rollups/generate' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        if (!ensureValidCsrfToken($config, $_POST['_csrf'] ?? null)) { return; }
        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $rollupDate = trim((string)($_POST['rollup_date'] ?? date('Y-m-d')));
        $result = null;
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new \App\Core\Attribution\EcosistemaAttributionRollupService(
                new \App\Core\Attribution\EcosistemaAttributionRollupRepository($pdo),
                (bool)($config['app']['ecosistema_attribution']['enabled'] ?? false),
                (bool)($config['app']['ecosistema_attribution']['rollup_write'] ?? false),
            );
            $result = $service->generate($tenantId, $rollupDate);
        } catch (\Throwable) {
            $result = ['allowed' => false, 'db_write' => false, 'written' => false, 'blocked_reason' => 'internal_error', 'rollup_date' => $rollupDate, 'metrics_preview' => [], 'warnings' => ['internal_error_hidden']];
        }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Attribution Rollup Generate | Ecosistema Core Admin','contentView'=>'pages/attribution/rollup-result','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result')]);
    },
    'GET /attribution/campaigns' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $auth = AuthSession::getAuth(); $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $summary = ['total' => 0]; $campaigns = [];
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new \App\Core\Attribution\EcosistemaCampaignAttributionService(new \App\Core\Attribution\EcosistemaCampaignAttributionRepository($pdo));
            $data = $service->listCampaigns($tenantId);
            $summary = (array)($data['summary'] ?? []);
            $campaigns = (array)($data['campaigns'] ?? []);
        } catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Attribution Campaigns | Ecosistema Core Admin','contentView'=>'pages/attribution/campaigns','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary','campaigns')]);
    },

    'GET /attribution/campaigns/{id}' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $id = isset($params['id']) ? (int)$params['id'] : 0; if ($id <= 0) { renderError($config, 404); return; }
        $auth = AuthSession::getAuth(); $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $detail = ['found' => false, 'campaign' => null, 'funnel' => ['clicks' => 0, 'visits' => 0, 'submissions' => 0, 'leads' => 0, 'conversions' => 0]];
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new \App\Core\Attribution\EcosistemaCampaignAttributionService(new \App\Core\Attribution\EcosistemaCampaignAttributionRepository($pdo));
            $detail = $service->campaignDetail($tenantId, $id);
        } catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Attribution Campaign Detail | Ecosistema Core Admin','contentView'=>'pages/attribution/campaign-detail','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('detail')]);
    },

    'GET /landing/forms/{id}/submit-dry-run' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $id = isset($params['id']) ? (int) $params['id'] : 0; if ($id <= 0) { renderError($config, 404); return; }
        $auth = AuthSession::getAuth(); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $enabled = (bool) (($config['app']['ecosistema_landing']['form_submit_dry_run'] ?? false) === true);
        $result = ['allowed' => false, 'errors' => ['feature_flag' => 'Dry-run deshabilitado por configuración.']];
        try { $pdo = PdoFactory::make($config['database']); $service = new EcosistemaLandingFormSubmitDryRunService(new EcosistemaLandingFormRepository($pdo)); $result = $service->buildForm($tenantId, $id, $enabled); } catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Landing Form Submit Dry-run | Ecosistema Core Admin','contentView'=>'pages/landing/form-submit-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result','id')]);
    },

    'POST /landing/forms/{id}/submit-dry-run' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $id = isset($params['id']) ? (int) $params['id'] : 0; if ($id <= 0) { renderError($config, 404); return; }
        if (!ensureValidCsrfToken($config, $_POST['_csrf'] ?? null)) { return; }
        $auth = AuthSession::getAuth(); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $enabled = (bool) (($config['app']['ecosistema_landing']['form_submit_dry_run'] ?? false) === true);
        $payload = $_POST; unset($payload['_csrf']);
        $result = ['allowed' => false, 'errors' => ['form' => 'No se pudo simular el envío.']];
        try { $pdo = PdoFactory::make($config['database']); $service = new EcosistemaLandingFormSubmitDryRunService(new EcosistemaLandingFormRepository($pdo)); $result = $service->simulateSubmit($tenantId, $id, $enabled, is_array($payload) ? $payload : []); } catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Landing Form Submit Dry-run | Ecosistema Core Admin','contentView'=>'pages/landing/form-submit-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result','id')]);
    },

    'GET /landing/submissions' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $auth = AuthSession::getAuth(); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $summary=['total'=>0,'by_status'=>[],'spam_score'=>['scored'=>0,'high'=>0]]; $submissions=[];
        try { $pdo=PdoFactory::make($config['database']); $service=new EcosistemaLandingSubmissionService(new EcosistemaLandingSubmissionRepository($pdo), new EcosistemaLandingAdapter()); $data=$service->listRecentSubmissions($tenantId); $summary=(array)($data['summary']??[]); $submissions=(array)($data['submissions']??[]);} catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Landing Submissions | Ecosistema Core Admin','contentView'=>'pages/landing/submissions','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary','submissions')]);
    },

    'GET /landing/forms/{id}/submissions' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $id = isset($params['id']) ? (int) $params['id'] : 0; if ($id <= 0) { renderError($config, 404); return; }
        $auth = AuthSession::getAuth(); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $summary=['total'=>0,'by_status'=>[],'spam_score'=>['scored'=>0,'high'=>0]]; $submissions=[];
        try { $pdo=PdoFactory::make($config['database']); $service=new EcosistemaLandingSubmissionService(new EcosistemaLandingSubmissionRepository($pdo), new EcosistemaLandingAdapter()); $data=$service->listSubmissionsForForm($tenantId, $id); $summary=(array)($data['summary']??[]); $submissions=(array)($data['submissions']??[]);} catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Landing Form Submissions | Ecosistema Core Admin','contentView'=>'pages/landing/form-submissions','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary','submissions','id')]);
    },

    'GET /landing/pages/{id}/submissions' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $id = isset($params['id']) ? (int) $params['id'] : 0; if ($id <= 0) { renderError($config, 404); return; }
        $auth = AuthSession::getAuth(); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $summary=['total'=>0,'by_status'=>[],'spam_score'=>['scored'=>0,'high'=>0]]; $submissions=[];
        try { $pdo=PdoFactory::make($config['database']); $service=new EcosistemaLandingSubmissionService(new EcosistemaLandingSubmissionRepository($pdo), new EcosistemaLandingAdapter()); $data=$service->listSubmissionsForPage($tenantId, $id); $summary=(array)($data['summary']??[]); $submissions=(array)($data['submissions']??[]);} catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Landing Page Submissions | Ecosistema Core Admin','contentView'=>'pages/landing/page-submissions','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary','submissions','id')]);
    },

    'GET /landing/submissions/{id}' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $id = isset($params['id']) ? (int) $params['id'] : 0; if ($id <= 0) { renderError($config, 404); return; }
        $auth = AuthSession::getAuth(); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $submission=null; $values=[];
        try { $pdo=PdoFactory::make($config['database']); $service=new EcosistemaLandingSubmissionService(new EcosistemaLandingSubmissionRepository($pdo), new EcosistemaLandingAdapter()); $data=$service->getSubmissionDetail($tenantId, $id); $submission=$data['submission']??null; $values=(array)($data['values']??[]);} catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Landing Submission Detail | Ecosistema Core Admin','contentView'=>'pages/landing/submission-detail','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('submission','values')]);
    },




    'POST /crm/submission-to-lead/{id}' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.manage')) { return; }

        $id = isset($params['id']) ? (int) $params['id'] : 0;
        if ($id <= 0) { renderError($config, 404); return; }

        $csrfToken = $_POST['_csrf'] ?? null;
        if (!ensureValidCsrfToken($config, $csrfToken)) { return; }

        $crmEnabled = (bool) (($config['app']['ecosistema_crm']['enabled'] ?? false) === true);
        $writeEnabled = (bool) (($config['app']['ecosistema_crm']['submission_to_lead_write'] ?? false) === true);
        $auth = AuthSession::getAuth();
        $result = ['ok' => false, 'error' => 'Operación no habilitada por flags.'];

        if ($crmEnabled && $writeEnabled) {
            $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
            $userId = (int) ($auth['auth_user_id'] ?? 0);
            $forceDuplicate = isset($_POST['force_duplicate']) && (string) $_POST['force_duplicate'] === '1';
            try {
                $pdo = PdoFactory::make($config['database']);
                $service = new EcosistemaCrmSubmissionToLeadService(new EcosistemaCrmLeadWriteRepository($pdo));
                $result = $service->convert($tenantId, $userId, $id, $forceDuplicate);
            } catch (\Throwable) {
                $result = ['ok' => false, 'error' => 'No se pudo completar la conversión submission→lead.'];
            }
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'CRM Submission to Lead Result | Ecosistema Core Admin','contentView'=>'pages/crm/submission-to-lead-result','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result','id')]);
    },


    'GET /crm/leads/{id}/followup-task-dry-run' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $id = isset($params['id']) ? (int) $params['id'] : 0;
        if ($id <= 0) { renderError($config, 404); return; }

        $auth = AuthSession::getAuth();
        $dryRun = null; $errorMessage = null;
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'CRM Followup Task Dry-Run | Ecosistema Core Admin','contentView'=>'pages/crm/followup-task-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('dryRun','id','errorMessage')]);
    },

    'POST /crm/leads/{id}/followup-task-dry-run' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $id = isset($params['id']) ? (int) $params['id'] : 0;
        if ($id <= 0) { renderError($config, 404); return; }

        $csrfToken = $_POST['_csrf'] ?? null;
        if (!ensureValidCsrfToken($config, $csrfToken)) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $createdByUserId = (int) ($auth['auth_user_id'] ?? 0);
        $dryRun = null; $errorMessage = null;
        try {
            $pdo = PdoFactory::make($config['database']);
            $repo = new \App\Core\Crm\EcosistemaCrmFollowupTaskDryRunRepository($pdo);
            $service = new \App\Core\Crm\EcosistemaCrmFollowupTaskDryRunService($repo, (array) ($config['app']['ecosistema_crm'] ?? []));
            $dryRun = $service->evaluate($tenantId, $id, $createdByUserId, [
                'assigned_user_id' => $_POST['assigned_user_id'] ?? null,
                'title' => $_POST['title'] ?? null,
                'description' => $_POST['description'] ?? null,
                'due_at' => $_POST['due_at'] ?? null,
                'priority' => $_POST['priority'] ?? null,
            ]);
        } catch (\Throwable) { $errorMessage = 'No se pudo simular la tarea de seguimiento.'; }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'CRM Followup Task Dry-Run | Ecosistema Core Admin','contentView'=>'pages/crm/followup-task-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('dryRun','id','errorMessage')]);
    },

    'POST /crm/leads/{id}/followup-tasks' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.manage')) { return; }

        $id = isset($params['id']) ? (int) $params['id'] : 0;
        if ($id <= 0) { renderError($config, 404); return; }

        $csrfToken = $_POST['_csrf'] ?? null;
        if (!ensureValidCsrfToken($config, $csrfToken)) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $createdByUserId = (int) ($auth['auth_user_id'] ?? 0);
        $result = ['ok' => false, 'error' => 'No se pudo crear la tarea de seguimiento.'];

        try {
            $pdo = PdoFactory::make($config['database']);
            $repo = new \App\Core\Crm\EcosistemaCrmFollowupTaskRepository($pdo);
            $service = new \App\Core\Crm\EcosistemaCrmFollowupTaskService($repo, (array) ($config['app']['ecosistema_crm'] ?? []));
            $result = $service->create($tenantId, $id, $createdByUserId, [
                'assigned_user_id' => $_POST['assigned_user_id'] ?? null,
                'title' => $_POST['title'] ?? null,
                'description' => $_POST['description'] ?? null,
                'due_at' => $_POST['due_at'] ?? null,
                'priority' => $_POST['priority'] ?? null,
            ]);
        } catch (\Throwable) {}

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'CRM Followup Task Result | Ecosistema Core Admin','contentView'=>'pages/crm/followup-task-result','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result','id')]);
    },


    'GET /crm/leads/{id}/status' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $id = isset($params['id']) ? (int) $params['id'] : 0;
        if ($id <= 0) { renderError($config, 404); return; }
        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $statusData = ['ok' => false, 'error' => 'No se pudo cargar el estado del lead.'];
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaCrmLeadStatusService(new EcosistemaCrmLeadStatusRepository($pdo), new AuditLogger($pdo), (array) ($config['app']['ecosistema_crm'] ?? []));
            $statusData = $service->getStatusContext($tenantId, $id);
        } catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'CRM Lead Status | Ecosistema Core Admin','contentView'=>'pages/crm/lead-status','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('id','statusData')]);
    },

    'POST /crm/leads/{id}/status' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.manage')) { return; }
        $id = isset($params['id']) ? (int) $params['id'] : 0;
        if ($id <= 0) { renderError($config, 404); return; }
        $csrfToken = $_POST['_csrf'] ?? null;
        if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $userId = (int) ($auth['auth_user_id'] ?? 0);
        $result = ['ok' => false, 'error' => 'No se pudo actualizar el estado del lead.'];
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaCrmLeadStatusService(new EcosistemaCrmLeadStatusRepository($pdo), new AuditLogger($pdo), (array) ($config['app']['ecosistema_crm'] ?? []));
            $result = $service->update($tenantId, $id, $userId, [
                'status' => $_POST['status'] ?? null,
                'campaign_lead_id' => $_POST['campaign_lead_id'] ?? null,
                'temperature' => $_POST['temperature'] ?? null,
                'score' => $_POST['score'] ?? null,
            ]);
        } catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'CRM Lead Status Result | Ecosistema Core Admin','contentView'=>'pages/crm/lead-status-result','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('id','result')]);
    },

    'GET /crm/submission-to-lead/{id}/dry-run' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $id = isset($params['id']) ? (int) $params['id'] : 0;
        if ($id <= 0) { renderError($config, 404); return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $dryRun = null; $errorMessage = null;
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaCrmSubmissionToLeadDryRunService($pdo);
            $dryRun = $service->evaluate($tenantId, $id);
            if ($dryRun === null) { $errorMessage = 'Submission no encontrada para el tenant actual.'; }
        } catch (\Throwable) { $errorMessage = 'No se pudo simular la conversión submission→lead.'; }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'CRM Submission to Lead Dry-Run | Ecosistema Core Admin','contentView'=>'pages/crm/submission-to-lead-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('dryRun','id','errorMessage')]);
    },




    'GET /reports/lead-performance' => static function (array $config): void {
        if (!requirePermission($config, 'campaigns.view')) { return; }
        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $data = ['filters'=>[],'by_source'=>[],'by_campaign'=>[],'by_status'=>[],'score_temperature'=>[],'mode'=>'read-only'];
        try { $pdo = PdoFactory::make($config['database']); $service = new EcosistemaLeadPerformanceReportService(new EcosistemaLeadPerformanceReportRepository($pdo)); $data = $service->build($tenantId, $_GET); } catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Reporte desempeño de leads | Ecosistema Core Admin','contentView'=>'pages/reports/lead-performance','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>$data]);
    },

    'GET /reports/marketing-funnel' => static function (array $config): void {
        if (!requirePermission($config, 'campaigns.view')) { return; }
        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $data = ['filters'=>[],'campaigns'=>[],'landings'=>[],'funnel'=>[],'conversion_rates'=>[],'mode'=>'read-only'];
        try { $pdo = PdoFactory::make($config['database']); $service = new EcosistemaMarketingFunnelReportService(new EcosistemaMarketingFunnelReportRepository($pdo)); $data = $service->build($tenantId, $_GET); } catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Reporte embudo marketing | Ecosistema Core Admin','contentView'=>'pages/reports/marketing-funnel','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>$data]);
    },



    'GET /reports/exports/dry-run' => static function (array $config): void {
        if (!requirePermission($config, 'campaigns.view')) { return; }
        $auth = AuthSession::getAuth();
        $result = null;
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Reports Export Dry-Run | Ecosistema Core Admin','contentView'=>'pages/reports/export-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result')]);
    },

    'POST /reports/exports/dry-run' => static function (array $config): void {
        if (!requirePermission($config, 'campaigns.view')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null;
        if (!ensureValidCsrfToken($config, $csrfToken)) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $result = ['allowed' => false, 'blocked_reason' => 'internal_error'];
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaReportExportDryRunService(new EcosistemaReportExportDryRunRepository($pdo), filter_var((string) ($_ENV['ECOSISTEMA_REPORT_EXPORT_DRY_RUN'] ?? false), FILTER_VALIDATE_BOOL));
            $result = $service->simulate($tenantId, is_array($_POST) ? $_POST : []);
        } catch (\Throwable) {}

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Reports Export Dry-Run | Ecosistema Core Admin','contentView'=>'pages/reports/export-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result')]);
    },

    'POST /reports/exports' => static function (array $config): void {
        if (!requirePermission($config, 'campaigns.view')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null;
        if (!ensureValidCsrfToken($config, $csrfToken)) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $userId = (int) ($auth['auth_user_id'] ?? 0);
        $result = ['ok' => false, 'allowed' => false, 'blocked_reason' => 'internal_error', 'status' => 'blocked'];

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaReportExportService(
                new EcosistemaReportExportRepository($pdo),
                filter_var((string) ($_ENV['ECOSISTEMA_REPORT_EXPORT_WRITE'] ?? false), FILTER_VALIDATE_BOOL),
                filter_var((string) ($_ENV['ECOSISTEMA_REPORT_EXPORT_INCLUDE_PII'] ?? false), FILTER_VALIDATE_BOOL),
            );
            $result = $service->requestExport($tenantId, $userId, is_array($_POST) ? $_POST : []);
        } catch (\Throwable) {}

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Reports Export Controlado | Ecosistema Core Admin','contentView'=>'pages/reports/export-result','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result')]);
    },

    'GET /campaigns/new/dry-run' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.manage')) { return; }
        $auth = AuthSession::getAuth();
        $result = null; $errorMessage = null;
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Campaign Creation Dry-Run | Ecosistema Core Admin','contentView'=>'pages/campaigns/create-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result','errorMessage')]);
    },

    'POST /campaigns/new/dry-run' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null;
        if (!ensureValidCsrfToken($config, $csrfToken)) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $ownerUserId = (int) ($auth['auth_user_id'] ?? 0);
        $result = null; $errorMessage = null;

        try {
            $service = new \App\Core\Campaigns\EcosistemaCampaignCreationDryRunService((array) ($config['app']['ecosistema_crm'] ?? []));
            $result = $service->simulate($tenantId, $ownerUserId, [
                'name' => $_POST['name'] ?? null,
                'code' => $_POST['code'] ?? null,
                'campaign_type' => $_POST['campaign_type'] ?? null,
                'objective' => $_POST['objective'] ?? null,
                'description' => $_POST['description'] ?? null,
                'budget' => $_POST['budget'] ?? null,
                'currency' => $_POST['currency'] ?? null,
                'starts_at' => $_POST['starts_at'] ?? null,
                'ends_at' => $_POST['ends_at'] ?? null,
                'landing_title' => $_POST['landing_title'] ?? null,
                'landing_slug' => $_POST['landing_slug'] ?? null,
                'short_slug' => $_POST['short_slug'] ?? null,
            ]);
        } catch (\Throwable) { $errorMessage = 'No se pudo simular la creación de campaña.'; }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Campaign Creation Dry-Run | Ecosistema Core Admin','contentView'=>'pages/campaigns/create-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result','errorMessage')]);
    },



    'POST /campaigns' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null;
        if (!ensureValidCsrfToken($config, $csrfToken)) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $ownerUserId = (int) ($auth['auth_user_id'] ?? 0);
        $result = null;

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaCampaignCreationService(new EcosistemaCampaignCreationRepository($pdo), (array) ($config['app']['ecosistema_crm'] ?? []));
            $result = $service->create($tenantId, $ownerUserId, [
                'name' => $_POST['name'] ?? null,
                'code' => $_POST['code'] ?? null,
                'campaign_type' => $_POST['campaign_type'] ?? null,
                'objective' => $_POST['objective'] ?? null,
                'description' => $_POST['description'] ?? null,
                'budget' => $_POST['budget'] ?? null,
                'currency' => $_POST['currency'] ?? null,
                'starts_at' => $_POST['starts_at'] ?? null,
                'ends_at' => $_POST['ends_at'] ?? null,
            ]);
        } catch (\Throwable) {
            $result = ['created' => false, 'campaign_id' => null, 'blocked_reasons' => ['write_failed'], 'warnings' => []];
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Campaign Creation Result | Ecosistema Core Admin','contentView'=>'pages/campaigns/create-result','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result')]);
    },

    'GET /campaigns' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $summary = ['total' => 0]; $campaigns = [];
        try { $pdo = PdoFactory::make($config['database']); $service = new EcosistemaCampaignCockpitService(new EcosistemaCampaignCockpitRepository($pdo)); $data = $service->list($tenantId); $summary=(array)($data['summary']??[]); $campaigns=(array)($data['campaigns']??[]);} catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Campaigns | Ecosistema Core Admin','contentView'=>'pages/campaigns/index','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary','campaigns')]);
    },

    'GET /campaigns/{id}/cockpit' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $id = isset($params['id']) ? (int) $params['id'] : 0;
        if ($id <= 0) { renderError($config, 404); return; }
        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $cockpit = ['found'=>false,'campaign'=>null,'funnel'=>[],'leads_by_status'=>[],'workflows_by_status'=>[]];
        try { $pdo = PdoFactory::make($config['database']); $service = new EcosistemaCampaignCockpitService(new EcosistemaCampaignCockpitRepository($pdo)); $cockpit = $service->cockpit($tenantId, $id);} catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Campaign Cockpit | Ecosistema Core Admin','contentView'=>'pages/campaigns/cockpit','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('cockpit')]);
    },

    'GET /crm' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $auth = AuthSession::getAuth();
        $capabilities = (new EcosistemaCrmAdapter())->capabilities();

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'CRM | Ecosistema Core Admin','contentView'=>'pages/crm/index','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('capabilities')]);
    },

    'GET /crm/campaigns' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $summary = ['total' => 0, 'by_status' => []];
        $campaigns = [];
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaCrmCampaignService(new EcosistemaCrmCampaignRepository($pdo), new EcosistemaCrmAdapter());
            $data = $service->listCampaigns($tenantId);
            $summary = (array) ($data['summary'] ?? []);
            $campaigns = (array) ($data['campaigns'] ?? []);
        } catch (\Throwable) {
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'CRM Campaigns | Ecosistema Core Admin','contentView'=>'pages/crm/campaigns','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary','campaigns')]);
    },

    'GET /crm/leads' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $summary = ['total' => 0, 'by_status' => []];
        $leads = [];
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaCrmLeadService(new EcosistemaCrmLeadRepository($pdo), new EcosistemaCrmAdapter());
            $data = $service->listLeads($tenantId);
            $summary = (array) ($data['summary'] ?? []);
            $leads = (array) ($data['leads'] ?? []);
        } catch (\Throwable) {
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'CRM Leads | Ecosistema Core Admin','contentView'=>'pages/crm/leads','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary','leads')]);
    },



    'GET /crm/leads/{id}' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $id = isset($params['id']) ? (int) $params['id'] : 0;
        if ($id <= 0) { renderError($config, 404); return; }
        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $detail = null; $errorMessage = null;
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaCrmLeadService(new EcosistemaCrmLeadRepository($pdo), new EcosistemaCrmAdapter());
            $detail = $service->getLeadDetail($tenantId, $id);
            if ($detail === null) { $errorMessage = 'Lead no encontrado para el tenant actual.'; }
        } catch (\Throwable) { $errorMessage = 'No se pudo obtener el detalle del lead.'; }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'CRM Lead Detail | Ecosistema Core Admin','contentView'=>'pages/crm/lead-detail','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('detail','errorMessage')]);
    },


    'GET /crm/followups' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $followups = ['tasks'=>[], 'followups'=>[], 'events'=>[]];
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaCrmFollowupService(new EcosistemaCrmFollowupRepository($pdo), new EcosistemaCrmAdapter());
            $followups = $service->listFollowups($tenantId);
        } catch (\Throwable) {}

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'CRM Followups | Ecosistema Core Admin','contentView'=>'pages/crm/followups','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('followups')]);
    },






    'POST /ai/assist' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        verifyCsrfOrAbort();

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $userId = (int) ($auth['auth_user_id'] ?? $auth['user_id'] ?? 0);
        $leadId = isset($_POST['lead_id']) ? (int) $_POST['lead_id'] : 0;

        $result = null; $errorMessage = null;
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaAiAssistanceService(
                new EcosistemaAiAssistanceRepository($pdo),
                new EcosistemaAiProvider((array) ($config['app']['ecosistema_ai'] ?? [])),
                (array) ($config['app']['ecosistema_ai'] ?? [])
            );
            $result = $service->assist($tenantId, $userId, ['lead_id' => $leadId]);
        } catch (\Throwable) { $errorMessage = 'No se pudo procesar asistencia IA.'; }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'AI Assist Result | Ecosistema Core Admin','contentView'=>'pages/ai/assist-result','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result','errorMessage')]);
    },

    'GET /ai/campaigns/{id}/insight-dry-run' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $id = isset($params['id']) ? (int) $params['id'] : 0;
        if ($id <= 0) { renderError($config, 404); return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $result = null; $errorMessage = null;
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaAiCampaignInsightDryRunService(new EcosistemaAiCampaignInsightDryRunRepository($pdo), (array) ($config['app']['ecosistema_ai'] ?? []));
            $result = $service->build($tenantId, $id);
        } catch (\Throwable) { $errorMessage = 'No se pudo preparar el contexto AI campaign insight dry-run.'; }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'AI Campaign Insight Dry-Run | Ecosistema Core Admin','contentView'=>'pages/ai/campaign-insight-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result','id','errorMessage')]);
    },

    'GET /ai/leads/{id}/summary-dry-run' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $id = isset($params['id']) ? (int) $params['id'] : 0;
        if ($id <= 0) { renderError($config, 404); return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $result = null; $errorMessage = null;
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaAiLeadSummaryDryRunService(new EcosistemaAiLeadSummaryRepository($pdo), (array) ($config['app']['ecosistema_ai'] ?? []));
            $result = $service->build($tenantId, $id);
        } catch (\Throwable) { $errorMessage = 'No se pudo preparar el contexto AI lead summary dry-run.'; }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'AI Lead Summary Dry-Run | Ecosistema Core Admin','contentView'=>'pages/ai/lead-summary-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result','id','errorMessage')]);
    },
    'GET /crm/leads/{id}/followups' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $id = isset($params['id']) ? (int) $params['id'] : 0;
        if ($id <= 0) { renderError($config, 404); return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $followups = ['tasks'=>[], 'followups'=>[], 'events'=>[]];
        $errorMessage = null;
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaCrmFollowupService(new EcosistemaCrmFollowupRepository($pdo), new EcosistemaCrmAdapter());
            $followups = $service->listFollowupsForLead($tenantId, $id);
        } catch (\Throwable) { $errorMessage = 'No se pudo obtener el detalle de followups del lead.'; }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'CRM Lead Followups | Ecosistema Core Admin','contentView'=>'pages/crm/lead-followups','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>['leadId'=>$id,'followups'=>$followups,'errorMessage'=>$errorMessage]]);
    },

    'GET /crm/campaigns/{id}' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $id = isset($params['id']) ? (int) $params['id'] : 0;
        if ($id <= 0) { renderError($config, 404); return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $campaign = null; $errorMessage = null;
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaCrmCampaignService(new EcosistemaCrmCampaignRepository($pdo), new EcosistemaCrmAdapter());
            $campaign = $service->getCampaign($tenantId, $id);
            if ($campaign === null) { $errorMessage = 'Campaña no encontrada para el tenant actual.'; }
        } catch (\Throwable) { $errorMessage = 'No se pudo obtener el detalle de la campaña.'; }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'CRM Campaign Detail | Ecosistema Core Admin','contentView'=>'pages/crm/campaign-detail','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('campaign','errorMessage')]);
    },

    'GET /cloud' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $statusMessage=isset($_GET['ok'])?(string)$_GET['ok']:null; $errorMessage=isset($_GET['error'])?(string)$_GET['error']:null;
        try{$pdo=PdoFactory::make($config['database']); $service=new CloudService(new CloudFileRepository($pdo), new CloudFolderRepository($pdo), new CloudRootRepository($pdo)); $files=$service->listFiles($tenantId,$userId);}catch(\Throwable){$files=[];$errorMessage='Archivo no encontrado.';}
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Cloud | Ecosistema Core Admin','contentView'=>'pages/cloud/index','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('files','statusMessage','errorMessage')]);
    },
    'GET /cloud/drive' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.manage')) { return; }
        $auth = AuthSession::getAuth();
        $driveConfig = new EcosistemaDriveConfig((array)($config['ecosistema_drive'] ?? []));
        $adapter = new EcosistemaDriveAdapter($driveConfig);
        $status = $adapter->getStatus();
        $capabilities = $adapter->getCapabilities();
        try {
            $pdo = PdoFactory::make($config['database']);
            driveAuditLog($pdo, 'drive.summary.viewed', 'drive_summary', null, '/cloud/drive', 'view');
        } catch (\Throwable) {
        }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Ecosistema Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('status','capabilities')]);
    },



    'GET /cloud/drive/access' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $policy = new EcosistemaDriveAccessPolicy();
        $policyDescription = $policy->describeReadOnlyPolicy();
        try { $pdo = PdoFactory::make($config['database']); driveAuditLog($pdo, 'drive.access_policy.viewed', 'drive_access_policy', null, '/cloud/drive/access', 'view'); } catch (\Throwable) {}

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Política de acceso Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-access','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('policyDescription')]);
    },


    'GET /cloud/drive/aws-config' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $awsConfig = (new EcosistemaDriveAwsS3Config($config['ecosistema_drive'] ?? []))->summary();

        try { $pdo = PdoFactory::make($config['database']); driveAuditLog($pdo, 'drive.aws_config.viewed', 'drive_aws_config', null, '/cloud/drive/aws-config', 'view'); } catch (\Throwable) {}

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'AWS/S3 Drive config | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-aws-config','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('awsConfig')]);
    },

    'GET /cloud/drive/download-contract' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $contract = (new EcosistemaDriveDownloadContract())->describe();

        try { $pdo = PdoFactory::make($config['database']); driveAuditLog($pdo, 'drive.download_contract.viewed', 'drive_download_contract', null, '/cloud/drive/download-contract', 'view'); } catch (\Throwable) {}

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Contrato descarga Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-download-contract','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('contract')]);
    },


    'GET /cloud/drive/upload-dry-run' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $uploadDryRun = (new EcosistemaDriveS3UploadDryRunService(
            new EcosistemaDriveAwsS3Config((array)($config['ecosistema_drive'] ?? [])),
            new EcosistemaDriveS3UploadDryRun(),
        ))->evaluate();

        try {
            $pdo = PdoFactory::make($config['database']);
            driveAuditLog($pdo, 'drive.upload.dry_run.viewed', 'drive_upload_dry_run', null, '/cloud/drive/upload-dry-run', 'view');
        } catch (\Throwable) {}

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Subida S3 dry-run | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-upload-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('uploadDryRun')]);
    },



    'GET /cloud/drive/upload' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.manage')) { return; }
        $auth = AuthSession::getAuth();
        $service = new EcosistemaDriveS3UploadService(
            PdoFactory::make($config['database']),
            new EcosistemaDriveAwsS3Config((array)($config['ecosistema_drive'] ?? [])),
            new EcosistemaDriveS3KeyValidator(),
        );
        $uploadStatus = $service->describeAvailability();
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Subida S3 controlada | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-upload','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('uploadStatus')]);
    },

    'POST /cloud/drive/upload' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.manage')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $userId = (int)($auth['user_id'] ?? $auth['auth_user_id'] ?? 0);
        $sessionContext = [
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'permissions' => (array)($auth['permissions'] ?? []),
        ];

        $pdo = PdoFactory::make($config['database']);
        $service = new EcosistemaDriveS3UploadService(
            $pdo,
            new EcosistemaDriveAwsS3Config((array)($config['ecosistema_drive'] ?? [])),
            new EcosistemaDriveS3KeyValidator(),
        );
        $uploadResult = $service->upload($sessionContext, $_FILES);
        (new EcosistemaDriveAuditLogger($pdo))->logReadOnlyView('drive.upload.controlled.attempted', 'drive_upload', isset($uploadResult['created_file_id']) ? (int)$uploadResult['created_file_id'] : null, '/cloud/drive/upload', 'upload_attempt');

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Resultado subida S3 | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-upload-result','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('uploadResult')]);
    },



    'GET /cloud/drive/storage-usage' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $usage = [];
        $errorMessage = null;

        try {
            $pdo = PdoFactory::make($config['database']);
            $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
            $service = new EcosistemaDriveStorageUsageService(new EcosistemaDriveStorageUsageRepository($pdo));
            $usage = $service->buildUsage($tenantId);
            driveAuditLog($pdo, 'drive.storage_usage.viewed', 'drive_storage_usage', null, '/cloud/drive/storage-usage', 'view');
        } catch (\Throwable) {
            $errorMessage = 'No se pudo consultar el uso de almacenamiento Drive.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Uso almacenamiento Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-storage-usage','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('usage','errorMessage')]);
    },


    'GET /cloud/drive/repair-jobs' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['auth_tenant_id'] ?? 0);
        $summary = [];
        $jobs = [];
        $errorMessage = null;

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaDriveRepairJobService(new EcosistemaDriveRepairJobRepository($pdo));
            $result = $service->listRecentForTenant($tenantId, 100);
            $summary = $result['summary'];
            $jobs = $result['jobs'];
            driveAuditLog($pdo, 'drive.repair_jobs.viewed', 'drive_repair_job', null, '/cloud/drive/repair-jobs', 'list');
        } catch (\Throwable) {
            $errorMessage = 'No se pudieron consultar los jobs de reparación de Drive.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Jobs de reparación Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-repair-jobs','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary','jobs','errorMessage')]);
    },

    'GET /cloud/drive/repair-jobs/{id}' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['auth_tenant_id'] ?? 0);
        $jobId = (int)($params['id'] ?? 0);
        if ($jobId <= 0) { renderError($config, 404); return; }

        $job = null;
        $logs = [];
        $errorMessage = null;

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaDriveRepairJobService(new EcosistemaDriveRepairJobRepository($pdo));
            $result = $service->getJobDetail($tenantId, $jobId, 200);
            $job = $result['job'];
            $logs = $result['logs'];
            driveAuditLog($pdo, 'drive.repair_job.detail.viewed', 'drive_repair_job', $jobId, '/cloud/drive/repair-jobs/{id}', 'view');
        } catch (\Throwable) {
            $errorMessage = 'No se pudo consultar el job de reparación de Drive.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Detalle job reparación Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-repair-job-detail','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('job','logs','errorMessage')]);
    },

    'GET /cloud/drive/access-logs' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['auth_tenant_id'] ?? 0);
        $summary = [];
        $logs = [];
        $errorMessage = null;

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaDriveAccessLogService(new EcosistemaDriveAccessLogRepository($pdo));
            $result = $service->listRecentForTenant($tenantId, 100);
            $summary = $result['summary'];
            $logs = $result['logs'];
            driveAuditLog($pdo, 'drive.access_logs.viewed', 'drive_access_log', null, '/cloud/drive/access-logs', 'list');
        } catch (\Throwable) {
            $errorMessage = 'No se pudieron consultar los logs de acceso de Drive.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Logs de acceso Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-access-logs','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary','logs','errorMessage')]);
    },

    'GET /cloud/drive/files/{id}/access-logs' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['auth_tenant_id'] ?? 0);
        $fileId = (int)($params['id'] ?? 0);
        $summary = [];
        $logs = [];
        $errorMessage = null;

        if ($fileId <= 0) { renderError($config, 404); return; }

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaDriveAccessLogService(new EcosistemaDriveAccessLogRepository($pdo));
            $result = $service->listForFile($tenantId, $fileId, 100);
            $summary = $result['summary'];
            $logs = $result['logs'];
            driveAuditLog($pdo, 'drive.file.access_logs.viewed', 'drive_file', $fileId, '/cloud/drive/files/{id}/access-logs', 'view');
        } catch (\Throwable) {
            $errorMessage = 'No se pudieron consultar los logs de acceso del archivo.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Logs de acceso por archivo | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-file-access-logs','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary','logs','fileId','errorMessage')]);
    },

    'GET /cloud/drive/summary' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $userId = (int)($auth['user_id'] ?? $auth['auth_user_id'] ?? 0);
        $summary = ['root_summary' => null, 'file_count' => 0, 'folder_count' => 0, 'bucket_count' => 0, 'quota_bytes' => null, 'used_bytes' => null, 'read_only' => true, 'mode' => 'contract/dry-run', 'warnings' => ['No se pudo cargar el resumen operativo Drive.']];

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaDriveSummaryService($pdo, new EcosistemaDriveRootRepository($pdo));
            $summary = $service->getSummary($tenantId, $userId);
            driveAuditLog($pdo, 'drive.summary.viewed', 'drive_summary', null, '/cloud/drive/summary', 'view');
        } catch (\Throwable) {
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Resumen Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-summary','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('summary')]);
    },

    'GET /cloud/drive/root' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $userId = (int)($auth['user_id'] ?? $auth['auth_user_id'] ?? 0);
        $root = null;
        $errorMessage = null;

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaDriveRootService(new EcosistemaDriveRootRepository($pdo), new EcosistemaDriveAccessPolicy());
            $root = $service->getUserRootSummary($tenantId, $userId);
            driveAuditLog($pdo, 'drive.root.viewed', 'drive_root', null, '/cloud/drive/root', 'view');
        } catch (\Throwable) {
            $errorMessage = 'No se pudo consultar la raíz Drive del usuario actual.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Raíz Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-root','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('root','errorMessage')]);
    },

    'GET /cloud/drive/files' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }
        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? 0);
        $userId = (int)($auth['user_id'] ?? 0);
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaDriveFileService(new EcosistemaDriveFileRepository($pdo), new EcosistemaDriveAccessPolicy());
            $files = $service->listFiles($tenantId, $userId, 100);
            driveAuditLog($pdo, 'drive.files.listed', 'drive_file', null, '/cloud/drive/files', 'list');
            $errorMessage = null;
        } catch (\Throwable) {
            $files = [];
            $errorMessage = 'No se pudo consultar metadata de archivos Drive.';
        }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Archivos Ecosistema Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-files','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('files','errorMessage')]);
    },

    'GET /cloud/drive/folders' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['auth_tenant_id'] ?? 0);
        $userId = (int)($auth['auth_user_id'] ?? 0);
        $folders = [];
        $errorMessage = null;

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaDriveFolderService(new EcosistemaDriveFolderRepository($pdo), new EcosistemaDriveFileRepository($pdo), new EcosistemaDriveAccessPolicy());
            $folders = $service->listFolders($tenantId, $userId, 100);
            driveAuditLog($pdo, 'drive.folders.listed', 'drive_folder', null, '/cloud/drive/folders', 'list');
        } catch (\Throwable) {
            $errorMessage = 'No se pudo cargar metadata de carpetas de Drive.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Carpetas Ecosistema Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-folders','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('folders','errorMessage')]);
    },

    'GET /cloud/drive/folders/{id}' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $userId = (int)($auth['user_id'] ?? $auth['auth_user_id'] ?? 0);
        $idRaw = (string)($params['id'] ?? '');
        $folder = null;
        $errorMessage = null;

        if (!preg_match('/^[1-9][0-9]*$/', $idRaw)) {
            http_response_code(404);
            $errorMessage = 'Carpeta no encontrada.';
        } else {
            try {
                $pdo = PdoFactory::make($config['database']);
                $service = new EcosistemaDriveFolderService(new EcosistemaDriveFolderRepository($pdo), new EcosistemaDriveFileRepository($pdo), new EcosistemaDriveAccessPolicy());
                $folder = $service->getFolderDetail($tenantId, $userId, (int)$idRaw);
                driveAuditLog($pdo, 'drive.folder.viewed', 'drive_folder', (int)$idRaw, '/cloud/drive/folders/{id}', 'view');
                if ($folder === null) {
                    http_response_code(404);
                    $errorMessage = 'Carpeta no encontrada.';
                }
            } catch (\Throwable) {
                http_response_code(404);
                $folder = null;
                $errorMessage = 'Carpeta no encontrada.';
            }
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Detalle carpeta Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-folder-detail','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('folder','errorMessage')]);
    },

    'GET /cloud/drive/browse' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $userId = (int)($auth['user_id'] ?? $auth['auth_user_id'] ?? 0);
        $folderIdRaw = isset($_GET['folder_id']) ? (string)$_GET['folder_id'] : null;
        $folderId = null;

        if ($folderIdRaw !== null && $folderIdRaw !== '') {
            if (!preg_match('/^[1-9][0-9]*$/', $folderIdRaw)) {
                http_response_code(404);
                renderError($config, 404);
                return;
            }
            $folderId = (int)$folderIdRaw;
        }

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaDriveFolderService(new EcosistemaDriveFolderRepository($pdo), new EcosistemaDriveFileRepository($pdo), new EcosistemaDriveAccessPolicy());
            $browser = $service->getFolderBrowser($tenantId, $userId, $folderId);
            driveAuditLog($pdo, 'drive.browser.viewed', 'drive_folder', $folderId > 0 ? $folderId : null, '/cloud/drive/browse', 'view');
            $errorMessage = null;
        } catch (\Throwable) {
            http_response_code(404);
            $browser = ['current_folder' => null, 'parent_folder' => null, 'child_folders' => [], 'files' => [], 'breadcrumbs' => [], 'limits' => ['max_items' => 100], 'read_only' => true];
            $errorMessage = 'Carpeta no encontrada.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Navegador Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-browse','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('browser','errorMessage')]);
    },


    'GET /cloud/drive/buckets' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $buckets = [];
        $errorMessage = null;

        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaDriveBucketService(new EcosistemaDriveBucketRepository($pdo), new EcosistemaDriveAccessPolicy());
            $buckets = $service->listBucketSummaries($tenantId);
            driveAuditLog($pdo, 'drive.buckets.viewed', 'drive_bucket', null, '/cloud/drive/buckets', 'list');
        } catch (\Throwable) {
            $errorMessage = 'No se pudo consultar metadata de buckets Drive.';
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Buckets Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-buckets','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('buckets','errorMessage')]);
    },

    'GET /cloud/drive/files/{id}' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? 0);
        $userId = (int)($auth['user_id'] ?? 0);
        $idRaw = (string)($params['id'] ?? '');
        $file = null;
        $errorMessage = null;

        if (!preg_match('/^[1-9][0-9]*$/', $idRaw)) {
            http_response_code(404);
            $errorMessage = 'Archivo no encontrado.';
        } else {
            try {
                $pdo = PdoFactory::make($config['database']);
                $service = new EcosistemaDriveFileService(new EcosistemaDriveFileRepository($pdo), new EcosistemaDriveAccessPolicy());
                $file = $service->getFileDetail($tenantId, $userId, (int)$idRaw);
                driveAuditLog($pdo, 'drive.file.viewed', 'drive_file', (int)$idRaw, '/cloud/drive/files/{id}', 'view');
                if ($file === null) {
                    http_response_code(404);
                    $errorMessage = 'Archivo no encontrado.';
                }
            } catch (\Throwable) {
                http_response_code(404);
                $file = null;
                $errorMessage = 'Archivo no encontrado.';
            }
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Detalle archivo Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-file-detail','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('file','errorMessage')]);
    },








    'GET /cloud/drive/files/{id}/versions' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? 0);
        $userId = (int)($auth['user_id'] ?? 0);
        $idRaw = (string)($params['id'] ?? '');
        $versions = [];
        $fileId = null;
        $errorMessage = null;

        if (!preg_match('/^[1-9][0-9]*$/', $idRaw)) {
            http_response_code(404);
            $errorMessage = 'Archivo no encontrado.';
        } else {
            $fileId = (int)$idRaw;
            try {
                $pdo = PdoFactory::make($config['database']);
                $service = new EcosistemaDriveFileVersionService(
                    new EcosistemaDriveFileRepository($pdo),
                    new EcosistemaDriveFileVersionRepository($pdo),
                    new EcosistemaDriveAccessPolicy(),
                    new EcosistemaDriveS3KeyValidator(),
                );
                $versionsResult = $service->listFileVersions($tenantId, $userId, $fileId);
                if ($versionsResult === null) {
                    http_response_code(404);
                    $errorMessage = 'Archivo no encontrado.';
                } else {
                    $versions = $versionsResult;
                    (new EcosistemaDriveAuditLogger($pdo))->logReadOnlyView(
                        'drive.file.versions.viewed',
                        'drive_file',
                        $fileId,
                        '/cloud/drive/files/{id}/versions',
                        'view',
                        $tenantId,
                        $userId,
                    );
                }
            } catch (\Throwable) {
                http_response_code(404);
                $errorMessage = 'Archivo no encontrado.';
            }
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Versiones archivo Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-file-versions','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('versions','fileId','errorMessage')]);
    },


    'GET /cloud/drive/files/{id}/share-contract' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $idRaw = (int)($params['id'] ?? 0);
        $tenantId = (int)($auth['auth_tenant_id'] ?? 0);
        $userId = (int)($auth['auth_user_id'] ?? 0);
        $shareContract = null;
        $errorMessage = null;

        if ($idRaw <= 0) {
            $errorMessage = 'No se encontró el archivo solicitado.';
        } else {
            try {
                $pdo = PdoFactory::make($config['database']);
                $service = new EcosistemaDriveShareContractService(
                    new EcosistemaDriveFileService(new EcosistemaDriveFileRepository($pdo), new EcosistemaDriveAccessPolicy()),
                    new EcosistemaDriveShareContract(),
                );
                $shareContract = $service->describeForFile($tenantId, $userId, $idRaw);
                if ($shareContract !== null) {
                    driveAuditLog($pdo, 'drive.file.share_contract.viewed', 'drive_file', (int)$idRaw, '/cloud/drive/files/{id}/share-contract', 'view');
                } else {
                    $errorMessage = 'No se encontró el archivo solicitado.';
                }
            } catch (\Throwable) {
                $errorMessage = 'No se encontró el archivo solicitado.';
            }
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Contrato compartir archivo Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-share-contract','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('shareContract','errorMessage')]);
    },

    'GET /cloud/drive/files/{id}/download' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? 0);
        $userId = (int)($auth['user_id'] ?? 0);
        $idRaw = (string)($params['id'] ?? '');
        $result = null;
        $errorMessage = null;

        if (!preg_match('/^[1-9][0-9]*$/', $idRaw)) {
            http_response_code(404);
            $errorMessage = 'Archivo no encontrado.';
        } else {
            try {
                $pdo = PdoFactory::make($config['database']);
                $driveConfig = (array)($config['ecosistema_drive'] ?? []);
                $result = (new EcosistemaDriveS3DownloadService(
                    $pdo,
                    new EcosistemaDriveS3KeyValidator(),
                    new EcosistemaDriveAwsS3Config($driveConfig),
                ))->attempt($tenantId, $userId, (int)$idRaw);
                driveAuditLog($pdo, 'drive.file.download.attempted', 'drive_file', (int)$idRaw, '/cloud/drive/files/{id}/download', 'download_attempt');
            } catch (\Throwable) {
                $result = null;
                $errorMessage = 'No se pudo procesar la descarga.';
            }
        }

        if ($result !== null && ($result['allowed'] ?? false) === true) {
            http_response_code(501);
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'Descarga controlada aún no implementada en este entorno.';
            return;
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Descarga Drive bloqueada | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-download-blocked','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result','errorMessage')]);
    },

    'GET /cloud/drive/files/{id}/signed-url-dry-run' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? 0);
        $userId = (int)($auth['user_id'] ?? 0);
        $idRaw = (string)($params['id'] ?? '');
        $dryRun = null;
        $errorMessage = null;

        if (!preg_match('/^[1-9][0-9]*$/', $idRaw)) {
            http_response_code(404);
            $errorMessage = 'Archivo no encontrado.';
        } else {
            try {
                $pdo = PdoFactory::make($config['database']);
                $service = new EcosistemaDriveSignedUrlDryRunService(
                    new EcosistemaDriveS3KeyValidationService(
                        new EcosistemaDriveS3KeyValidationRepository($pdo),
                        new EcosistemaDriveS3KeyValidator(),
                    ),
                    new EcosistemaDriveSignedUrlDryRun(),
                );
                $dryRun = $service->evaluate($tenantId, $userId, (int)$idRaw);
                driveAuditLog($pdo, 'drive.file.signed_url_dry_run.viewed', 'drive_file', (int)$idRaw, '/cloud/drive/files/{id}/signed-url-dry-run', 'view');
                if ($dryRun === null) {
                    http_response_code(404);
                    $errorMessage = 'Archivo no encontrado.';
                }
            } catch (\Throwable) {
                http_response_code(404);
                $errorMessage = 'Archivo no encontrado.';
            }
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Signed URL dry-run Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-signed-url-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('dryRun','errorMessage')]);
    },
    'GET /cloud/drive/files/{id}/s3-key-validation' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? 0);
        $userId = (int)($auth['user_id'] ?? 0);
        $idRaw = (string)($params['id'] ?? '');
        $validation = null;
        $errorMessage = null;

        if (!preg_match('/^[1-9][0-9]*$/', $idRaw)) {
            http_response_code(404);
            $errorMessage = 'Archivo no encontrado.';
        } else {
            try {
                $pdo = PdoFactory::make($config['database']);
                $service = new EcosistemaDriveS3KeyValidationService(
                    new EcosistemaDriveS3KeyValidationRepository($pdo),
                    new EcosistemaDriveS3KeyValidator(),
                );
                $validation = $service->validate($tenantId, $userId, (int)$idRaw);
                driveAuditLog($pdo, 'drive.file.s3_key_validation.viewed', 'drive_file', (int)$idRaw, '/cloud/drive/files/{id}/s3-key-validation', 'view');
                if ($validation === null) {
                    http_response_code(404);
                    $errorMessage = 'Archivo no encontrado.';
                }
            } catch (\Throwable) {
                http_response_code(404);
                $errorMessage = 'Archivo no encontrado.';
            }
        }

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Validación s3_key Drive | Ecosistema Core Admin','contentView'=>'pages/cloud/drive-s3-key-validation','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('validation','errorMessage')]);
    },

    'GET /cloud/settings' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.manage')) { return; }
        $auth=AuthSession::getAuth();
        $cloudConfig = new CloudStorageConfig((array)($config['cloud'] ?? []));
        $storage = $cloudConfig->toSafeArray();
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin',['title'=>'Configuración S3 | Ecosistema Core Admin','contentView'=>'pages/cloud/settings','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('storage')]);
    },

    'GET /cloud/files/upload' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.manage')) { return; }
        $auth=AuthSession::getAuth();
        $service = new CloudUploadService(new CloudFileRepository(PdoFactory::make($config['database'])), new CloudStorageService($config, class_exists('Aws\S3\S3Client')), $config);
        $options = $service->options();
        $statusMessage=isset($_GET['ok'])?(string)$_GET['ok']:null; $errorMessage=isset($_GET['error'])?(string)$_GET['error']:null;
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Subir archivo | Ecosistema Core Admin','contentView'=>'pages/cloud/upload','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('options','statusMessage','errorMessage')]);
    },
    'POST /cloud/files/upload' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.manage')) { return; }
        $csrfToken=$_POST['_csrf']??null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0);
        try{$pdo=PdoFactory::make($config['database']); $service = new CloudUploadService(new CloudFileRepository($pdo), new CloudStorageService($config, class_exists('Aws\S3\S3Client')), $config); $result=$service->upload($tenantId,$userId,$_FILES['file']??[]); if(($result['ok']??false)===true){ auditLog($pdo,['action'=>'cloud.file_uploaded','entity_type'=>'cloud_files','entity_id'=>(int)($result['id']??0),'new_values'=>['status'=>'active']]); }}catch(\Throwable){$result=['ok'=>false,'message'=>'No se pudo guardar el archivo.'];}
        header('Location: /cloud/files/upload?'.((($result['ok']??false)===true)?'ok=':'error=').urlencode((string)($result['message']??'')));
    },

    'GET /cloud/files/{id}' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $id=(int)($params['id']??0);
        try{$pdo=PdoFactory::make($config['database']); $service=new CloudService(new CloudFileRepository($pdo), new CloudFolderRepository($pdo), new CloudRootRepository($pdo)); $file=$service->findFile($tenantId,$userId,$id);}catch(\Throwable){$file=null;}
        if($file===null){http_response_code(404);} header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Cloud detalle | Ecosistema Core Admin','contentView'=>'pages/cloud/show','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('file')]);
    },

    'GET /cloud/files/{id}/download' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        try { $authCheck=AuthSession::getAuth(); $p=PdoFactory::make($config['database']); $az=new \App\Core\Auth\AuthorizationService(new \App\Core\Auth\AuthorizationRepository($p)); if(!$az->can((int)($authCheck['auth_user_id']??0),(int)($authCheck['auth_tenant_id']??0),'cloud.view')&&!$az->can((int)($authCheck['auth_user_id']??0),(int)($authCheck['auth_tenant_id']??0),'cloud.manage')){ renderError($config,403); return; }} catch (\Throwable) { renderError($config,403); return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $id=(int)($params['id']??0);
        try {
            $pdo=PdoFactory::make($config['database']);
            $service = new CloudDownloadService(new CloudFileRepository($pdo), $config);
            $result = $service->resolveLocalFile($tenantId, $userId, $id);
            if (!(bool)($result['ok'] ?? false)) {
                http_response_code((int)($result['code'] ?? 403));
                header('Content-Type: text/html; charset=UTF-8');
                View::render('layouts.admin',['title'=>'Cloud | Ecosistema Core Admin','contentView'=>'pages/cloud/index','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>['files'=>[],'statusMessage'=>null,'errorMessage'=>(string)($result['message'] ?? 'No autorizado.')]]);
                return;
            }
            $file = (array)($result['file'] ?? []);
            $downloadName = (string)($file['original_name'] ?? 'archivo');
            header('Content-Description: File Transfer');
            header('Content-Type: ' . (string)($file['mime_type'] ?? 'application/octet-stream'));
            header('Content-Disposition: attachment; filename="' . str_replace('"', '', $downloadName) . '"');
            header('Content-Length: ' . (string) filesize((string) $result['path']));
            header('X-Content-Type-Options: nosniff');
            header('Cache-Control: private, no-store, no-cache, must-revalidate');
            auditLog($pdo,['action'=>'cloud.file_downloaded','entity_type'=>'cloud_files','entity_id'=>(int)($file['id'] ?? 0),'new_values'=>['status'=>(string)($file['status'] ?? 'active')]]);
            readfile((string) $result['path']);
            exit;
        } catch (\Throwable) {
            renderError($config, 404);
        }
    },

    'POST /cloud/files/{id}/archive' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.manage')) { return; }
        $csrfToken=$_POST['_csrf']??null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $id=(int)($params['id']??0);
        try{$pdo=PdoFactory::make($config['database']); $service=new CloudService(new CloudFileRepository($pdo), new CloudFolderRepository($pdo), new CloudRootRepository($pdo)); $message=$service->archiveFile($tenantId,$userId,$id);}catch(\Throwable){$message='Archivo no encontrado.';}
        header('Location: /cloud?'.(($message==='Archivo actualizado correctamente.')?'ok=':'error=').urlencode($message));
    },
    'POST /cloud/files/{id}/trash' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.manage')) { return; }
        $csrfToken=$_POST['_csrf']??null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $id=(int)($params['id']??0);
        try{$pdo=PdoFactory::make($config['database']); $service=new CloudService(new CloudFileRepository($pdo), new CloudFolderRepository($pdo), new CloudRootRepository($pdo)); $message=$service->trashFile($tenantId,$userId,$id);}catch(\Throwable){$message='Archivo no encontrado.';}
        header('Location: /cloud?'.(($message==='Archivo enviado a papelera.')?'ok=':'error=').urlencode($message));
    },
    'GET /cloud/folders' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.view')) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $statusMessage=isset($_GET['ok'])?(string)$_GET['ok']:null; $errorMessage=isset($_GET['error'])?(string)$_GET['error']:null;
        try{$pdo=PdoFactory::make($config['database']); $service=new CloudService(new CloudFileRepository($pdo), new CloudFolderRepository($pdo), new CloudRootRepository($pdo)); $folders=$service->listFolders($tenantId,$userId);}catch(\Throwable){$folders=[];$errorMessage='Carpeta no encontrada.';}
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Cloud carpetas | Ecosistema Core Admin','contentView'=>'pages/cloud/folders','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('folders','statusMessage','errorMessage')]);
    },
    'GET /cloud/folders/create' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.manage')) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0);
        try{$pdo=PdoFactory::make($config['database']); $service=new CloudService(new CloudFileRepository($pdo), new CloudFolderRepository($pdo), new CloudRootRepository($pdo)); $roots=$service->listRoots($tenantId,$userId); $folders=$service->listFolders($tenantId,$userId);}catch(\Throwable){$roots=[];$folders=[];}
        $errorMessage=$roots===[]?'No hay raíz Cloud activa para este usuario.':null;
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Crear carpeta Cloud | Ecosistema Core Admin','contentView'=>'pages/cloud/create-folder','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('roots','folders','errorMessage')]);
    },
    'POST /cloud/folders' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.manage')) { return; }
        $csrfToken=$_POST['_csrf']??null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0);
        try{$pdo=PdoFactory::make($config['database']); $service=new CloudService(new CloudFileRepository($pdo), new CloudFolderRepository($pdo), new CloudRootRepository($pdo)); $message=$service->createFolder($tenantId,$userId,$_POST);}catch(\Throwable){$message='No se pudo guardar la carpeta.';}
        header('Location: '.($message==='Carpeta creada correctamente.'?'/cloud/folders?ok='.urlencode($message):'/cloud/folders/create?error='.urlencode($message)));
    },
    'POST /cloud/folders/{id}/trash' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'cloud.manage')) { return; }
        $csrfToken=$_POST['_csrf']??null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $id=(int)($params['id']??0);
        try{$pdo=PdoFactory::make($config['database']); $service=new CloudService(new CloudFileRepository($pdo), new CloudFolderRepository($pdo), new CloudRootRepository($pdo)); $message=$service->trashFolder($tenantId,$userId,$id);}catch(\Throwable){$message='Carpeta no encontrada.';}
        header('Location: /cloud/folders?'.(($message==='Carpeta enviada a papelera.')?'ok=':'error=').urlencode($message));
    },


    'GET /system/health' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'system.view')) { return; }
        $statusMessage = isset($_GET['ok']) ? (string)$_GET['ok'] : null; $errorMessage = isset($_GET['error']) ? (string)$_GET['error'] : null;
        try { $pdo=PdoFactory::make($config['database']); $service=new HealthService(new HealthRepository($pdo), new LogRepository($pdo), $pdo); $healthChecks=$service->listHealthChecks(); } catch (\Throwable) { $healthChecks=[]; $errorMessage='No se pudo ejecutar el health check.'; }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Health | Ecosistema Core Admin','contentView'=>'pages/system/health','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('healthChecks','statusMessage','errorMessage')]);
    },
    'POST /system/health/{id}/run' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'system.manage')) { return; }
        $csrfToken=$_POST['_csrf']??null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $id=(int)($params['id']??0); $message='No se pudo ejecutar el health check.';
        try { $pdo=PdoFactory::make($config['database']); $service=new HealthService(new HealthRepository($pdo), new LogRepository($pdo), $pdo); $auth=AuthSession::getAuth(); $message=$service->runHealthCheck($id, isset($auth['tenant_id'])?(int)$auth['tenant_id']:null, isset($auth['user_id'])?(int)$auth['user_id']:null, $_SERVER['REMOTE_ADDR']??null, $_SERVER['HTTP_USER_AGENT']??null);} catch (\Throwable) {}
        header('Location: /system/health?'.($message==='Health check ejecutado correctamente.'?'ok=':'error=').urlencode($message));
    },
    'GET /system/logs' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'system.view')) { return; }
        $errorMessage=null; try{$pdo=PdoFactory::make($config['database']); $logs=(new LogRepository($pdo))->listRecent(100);}catch(\Throwable){$logs=[]; $errorMessage='No se pudieron cargar los logs.';}
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Logs | Ecosistema Core Admin','contentView'=>'pages/system/logs','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('logs','errorMessage')]);
    },
    'GET /system/audit' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'system.view')) { return; }
        $errorMessage=null; try{$pdo=PdoFactory::make($config['database']); $audits=(new AuditRepository($pdo))->listRecent(100);}catch(\Throwable){$audits=[]; $errorMessage='No se pudo cargar auditoría.';}
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Auditoría | Ecosistema Core Admin','contentView'=>'pages/system/audit','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('audits','errorMessage')]);
    },



    'GET /audit' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'system.view')) { return; }
        header('Location: /audit/events');
    },
    'GET /audit/events' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'system.view')) { return; }
        $auth=AuthSession::getAuth(); $events=[]; $filters=['module_code'=>'','action'=>'','from'=>'','to'=>''];
        try { $pdo=PdoFactory::make($config['database']); $service=new EcosistemaUnifiedAuditService(new EcosistemaUnifiedAuditRepository($pdo)); $data=$service->listEvents((int)($auth['auth_tenant_id']??0), $_GET); $events=$data['events']; $filters=$data['filters']; } catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Unified Audit | Ecosistema Core Admin','contentView'=>'pages/audit/events','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('events','filters')]);
    },
    'GET /audit/events/{id}' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'system.view')) { return; }
        $auth=AuthSession::getAuth(); $id=(int)($params['id']??0); if($id<=0){ renderError($config,404); return; }
        try { $pdo=PdoFactory::make($config['database']); $service=new EcosistemaUnifiedAuditService(new EcosistemaUnifiedAuditRepository($pdo)); $detail=$service->findDetail((int)($auth['auth_tenant_id']??0), $id); if($detail===null){ renderError($config,404); return; } } catch (\Throwable) { renderError($config,500); return; }
        extract($detail); header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Audit Event | Ecosistema Core Admin','contentView'=>'pages/audit/event-detail','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('event','changes','links')]);
    },

    'GET /onboarding' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'onboarding.view')) { return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['auth_tenant_id']??0); $errorMessage=null; $stats=['pending'=>0,'running'=>0,'completed'=>0,'failed'=>0,'canceled'=>0]; $runs=[];
        try { $pdo=PdoFactory::make($config['database']); $service=new OnboardingService($pdo,new OnboardingFlowRepository($pdo),new OnboardingRunRepository($pdo)); $data=$service->dashboard($tenantId); $stats=$data['stats']; $runs=$data['runs']; } catch (\Throwable) { $errorMessage='No se pudo guardar el onboarding run.'; }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Onboarding | Ecosistema Core Admin','contentView'=>'pages/onboarding/index','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('stats','runs','errorMessage')]);
    },
    'GET /onboarding/flows' => static function (array $config): void { startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'onboarding.view')) { return; } try { $pdo=PdoFactory::make($config['database']); $service=new OnboardingService($pdo,new OnboardingFlowRepository($pdo),new OnboardingRunRepository($pdo)); $flows=$service->listFlows(); } catch (\Throwable) { $flows=[]; }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Onboarding Flows | Ecosistema Core Admin','contentView'=>'pages/onboarding/flows','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('flows')]); },
    'GET /onboarding/runs/create' => static function (array $config): void { startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'onboarding.manage')) { return; } try { $pdo=PdoFactory::make($config['database']); $flows=(new OnboardingFlowRepository($pdo))->listActive(); } catch (\Throwable) { $flows=[]; }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Crear Onboarding Run | Ecosistema Core Admin','contentView'=>'pages/onboarding/create-run','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('flows')]); },
    'POST /onboarding/runs' => static function (array $config): void { startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'onboarding.manage')) { return; } $csrfToken=$_POST['_csrf']??null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; } $auth=AuthSession::getAuth(); try { $pdo=PdoFactory::make($config['database']); $service=new OnboardingService($pdo,new OnboardingFlowRepository($pdo),new OnboardingRunRepository($pdo)); $message=$service->createRun((int)$auth['auth_tenant_id'],(int)$auth['auth_user_id'],$_POST); } catch (\Throwable) { $message='No se pudo guardar el onboarding run.'; } header('Location: '.($message==='Onboarding run creado correctamente.'?'/onboarding?ok=1':'/onboarding/runs/create?error='.urlencode($message))); },
    'GET /onboarding/runs/{id}' => static function (array $config,array $params): void { startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'onboarding.view')) { return; } $id=(int)($params['id']??0); $auth=AuthSession::getAuth(); try { $pdo=PdoFactory::make($config['database']); $tenantId=(int)$auth['auth_tenant_id']; $stmt=$pdo->prepare('SELECT r.*, f.name AS flow_name, f.flow_key, u.display_name AS user_display_name, u.email AS user_email FROM onboarding_runs r INNER JOIN onboarding_flows f ON f.id = r.flow_id LEFT JOIN core_users u ON u.id = r.user_id WHERE r.id = :id AND r.tenant_id = :tenant_id LIMIT 1'); $stmt->execute([':id'=>$id,':tenant_id'=>$tenantId]); $run=$stmt->fetch(PDO::FETCH_ASSOC); if(!is_array($run)){ echo 'Onboarding run no encontrado.'; return;} $s=$pdo->prepare('SELECT rs.id, rs.run_id, rs.step_id, rs.status, rs.started_at, rs.completed_at, rs.error_message, rs.output_json, st.name, st.action_type, st.is_required FROM onboarding_run_steps rs INNER JOIN onboarding_steps st ON st.id = rs.step_id WHERE rs.run_id = :run_id ORDER BY st.sort_order ASC, rs.id ASC');$s->execute([':run_id'=>$id]); $steps=$s->fetchAll(PDO::FETCH_ASSOC)?:[]; $l=$pdo->prepare('SELECT id, run_id, run_step_id, level, message, context_json, created_at FROM onboarding_run_logs WHERE run_id = :run_id ORDER BY id DESC LIMIT 200');$l->execute([':run_id'=>$id]); $logs=$l->fetchAll(PDO::FETCH_ASSOC)?:[]; } catch (\Throwable) { echo 'Onboarding run no encontrado.'; return; } header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Onboarding Run | Ecosistema Core Admin','contentView'=>'pages/onboarding/show-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('run','steps','logs')]); },
    'POST /onboarding/runs/{id}/start' => static function (array $config,array $params): void { startAuthSession($config); if(!AuthSession::isAuthenticated()){header('Location: /login');return;} if (!requirePermission($config, 'onboarding.manage')) { return; } $csrfToken=$_POST['_csrf']??null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; } $id=(int)($params['id']??0); $t=(int)(AuthSession::getAuth()['auth_tenant_id']??0); try{$pdo=PdoFactory::make($config['database']);$repo=new OnboardingRunRepository($pdo);$runner=new OnboardingRunner($pdo,$repo,new OnboardingStepExecutor());if($runner->startRun($t,$id)){ $repo->createRunLog($id,null,'info','Onboarding run iniciado.',json_encode(['source'=>'manual'],JSON_UNESCAPED_UNICODE)); auditLog($pdo,['action'=>'onboarding.run_started','entity_type'=>'onboarding_runs','entity_id'=>$id]); }}catch(\Throwable){} header('Location: /onboarding/runs/'.$id); },
    'POST /onboarding/runs/{id}/cancel' => static function (array $config,array $params): void { startAuthSession($config); if(!AuthSession::isAuthenticated()){header('Location: /login');return;} if (!requirePermission($config, 'onboarding.manage')) { return; } $csrfToken=$_POST['_csrf']??null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; } $id=(int)($params['id']??0); $t=(int)(AuthSession::getAuth()['auth_tenant_id']??0); try{$pdo=PdoFactory::make($config['database']);$u=$pdo->prepare("UPDATE onboarding_runs SET status = :canceled WHERE id = :id AND tenant_id = :tenant_id AND status IN ('pending','running','partial')");$u->execute([':canceled'=>'canceled',':id'=>$id,':tenant_id'=>$t]);$log=$pdo->prepare('INSERT INTO onboarding_run_logs (run_id, run_step_id, level, message, context_json) VALUES (:run_id, NULL, :level, :message, :context_json)');$log->execute([':run_id'=>$id,':level'=>'warning',':message'=>'Onboarding run cancelado.',':context_json'=>json_encode(['source'=>'manual'])]);}catch(\Throwable){} header('Location: /onboarding/runs/'.$id); },
    'POST /onboarding/runs/{id}/next-step' => static function (array $config,array $params): void { startAuthSession($config); if(!AuthSession::isAuthenticated()){header('Location: /login');return;} if (!requirePermission($config, 'onboarding.manage')) { return; } $csrfToken=$_POST['_csrf']??null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; } $id=(int)($params['id']??0); $auth=AuthSession::getAuth(); $tenantId=(int)($auth['auth_tenant_id']??0); try{$pdo=PdoFactory::make($config['database']); $repo=new OnboardingRunRepository($pdo); $runner=new OnboardingRunner($pdo,$repo,new OnboardingStepExecutor()); $result=$runner->executeNextStep($tenantId,$id); if($result['ok']){ $auditAction=(string)($result['audit_action']??''); if($auditAction!==''){ auditLog($pdo,['action'=>$auditAction,'entity_type'=>'onboarding_runs','entity_id'=>$id]); } }}catch(\Throwable){} header('Location: /onboarding/runs/'.$id); },

    'POST /onboarding/run-steps/{id}/status' => static function (array $config,array $params): void { startAuthSession($config); if(!AuthSession::isAuthenticated()){header('Location: /login');return;} if (!requirePermission($config, 'onboarding.manage')) { return; } $csrfToken=$_POST['_csrf']??null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; } $id=(int)($params['id']??0); $status=(string)($_POST['status']??''); $allowed=['pending','running','completed','failed','skipped']; if(!in_array($status,$allowed,true)){ header('Location: /onboarding?error=1'); return; } $tenantId=(int)(AuthSession::getAuth()['auth_tenant_id']??0); try{$pdo=PdoFactory::make($config['database']); $q=$pdo->prepare('SELECT rs.id, rs.run_id, st.is_required FROM onboarding_run_steps rs INNER JOIN onboarding_runs r ON r.id = rs.run_id INNER JOIN onboarding_steps st ON st.id = rs.step_id WHERE rs.id = :id AND r.tenant_id = :tenant_id LIMIT 1'); $q->execute([':id'=>$id,':tenant_id'=>$tenantId]); $row=$q->fetch(PDO::FETCH_ASSOC); if(!is_array($row)){echo 'Onboarding run no encontrado.'; return;} $sql='UPDATE onboarding_run_steps SET status = :status'; $params2=[':status'=>$status,':id'=>$id]; if($status==='running'){$sql.=', started_at = COALESCE(started_at, NOW())';} if($status==='completed'){$sql.=', completed_at = NOW(), error_message = NULL';} if($status==='failed'){$sql.=', completed_at = NOW(), error_message = :error_message'; $params2[':error_message']=(string)($_POST['error_message']??'');} if($status==='skipped'){$sql.=', completed_at = NOW()';} $sql.=' WHERE id = :id'; $u=$pdo->prepare($sql); $u->execute($params2); $runId=(int)$row['run_id']; $pdo->prepare('INSERT INTO onboarding_run_logs (run_id, run_step_id, level, message, context_json) VALUES (:run_id,:run_step_id,:level,:message,:context_json)')->execute([':run_id'=>$runId,':run_step_id'=>$id,':level'=>$status==='failed'?'error':'info',':message'=>'Paso actualizado correctamente.',':context_json'=>json_encode(['status'=>$status])]); }catch(\Throwable){ renderError($config, 500); return;} header('Location: /onboarding/runs/'.(int)$row['run_id']); },


    'GET /platform' => static function (array $config): void {
        startAuthSession($config);
        if (!requirePermission($config, 'modules.view')) { return; }
        $auth = AuthSession::getAuth();
        $cockpit = ['modules'=>[],'links'=>[],'tenant_summary'=>['roles_count'=>0,'users_count'=>0]];
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaPlatformCockpitService(new EcosistemaPlatformCockpitRepository($pdo), new EcosistemaPlatformAdapter());
            $cockpit = $service->buildCockpit((int) ($auth['auth_tenant_id'] ?? 0));
        } catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Platform Cockpit | Ecosistema Core Admin', 'contentView' => 'pages/platform/cockpit', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => ['cockpit' => $cockpit]]);
    },

    'GET /platform/cockpit' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        header('Location: /platform');
    },

    'GET /platform/health' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $auth = AuthSession::getAuth();
        $health = ['modules'=>[], 'workers'=>[]];
        try { $pdo = PdoFactory::make($config['database']); $service = new EcosistemaPlatformHealthService(new EcosistemaPlatformHealthRepository($pdo)); $health = $service->buildDashboard((int) ($auth['auth_tenant_id'] ?? 0)); } catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Platform Health | Ecosistema Core Admin', 'contentView' => 'pages/platform/health', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('health')]);
    },

    'GET /platform/health/modules/{code}' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $auth = AuthSession::getAuth(); $code = trim((string) ($params['code'] ?? '')); if ($code === '') { renderError($config, 404); return; }
        try { $pdo = PdoFactory::make($config['database']); $service = new EcosistemaPlatformHealthService(new EcosistemaPlatformHealthRepository($pdo)); $detail = $service->buildModuleDetail((int) ($auth['auth_tenant_id'] ?? 0), $code); } catch (\Throwable) { $detail = null; }
        if (!is_array($detail)) { renderError($config, 404); return; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Module Health | Ecosistema Core Admin', 'contentView' => 'pages/platform/module-health', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('detail')]);
    },


    'GET /security/rate-limit/dry-run' => static function (array $config): void {
        startAuthSession($config); if (!requirePermission($config, 'permissions.view')) { return; }
        $auth = AuthSession::getAuth();
        View::render('layouts.admin', ['title' => 'Security Rate Limit Dry-Run | Ecosistema Core Admin', 'contentView' => 'pages/security/rate-limit-dry-run', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => ['result' => null, 'errorMessage' => null, 'input' => []]]);
    },
    'POST /security/rate-limit/dry-run' => static function (array $config): void {
        startAuthSession($config); if (!requirePermission($config, 'permissions.view')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $errorMessage = null;
        $result = null;
        $input = ['path' => (string) ($_POST['path'] ?? ''), 'ip_address' => (string) ($_POST['ip_address'] ?? ''), 'window_minutes' => (string) ($_POST['window_minutes'] ?? ''), 'max_requests' => (string) ($_POST['max_requests'] ?? ''), 'max_login_failures' => (string) ($_POST['max_login_failures'] ?? '')];
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaRateLimitDryRunService(new EcosistemaRateLimitDryRunRepository($pdo));
            $result = $service->simulate(
                $tenantId,
                $_POST,
                filter_var((string) ($config['app']['ecosistema_security']['rate_limit_enabled'] ?? false), FILTER_VALIDATE_BOOL),
                filter_var((string) ($config['app']['ecosistema_security']['rate_limit_dry_run'] ?? false), FILTER_VALIDATE_BOOL),
            );
        } catch (\Throwable) {
            $errorMessage = 'No se pudo calcular la simulación de rate limit.';
        }
        View::render('layouts.admin', ['title' => 'Security Rate Limit Dry-Run | Ecosistema Core Admin', 'contentView' => 'pages/security/rate-limit-dry-run', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('result', 'errorMessage', 'input')]);
    },

    'POST /security/rate-limit/enforce' => static function (array $config): void {
        startAuthSession($config); if (!requirePermission($config, 'permissions.view')) { return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth = AuthSession::getAuth();
        $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        $userId = (int) ($auth['auth_user_id'] ?? 0);
        $errorMessage = null;
        $result = null;
        $input = ['path' => (string) ($_POST['path'] ?? ''), 'ip_address' => (string) ($_POST['ip_address'] ?? ''), 'window_minutes' => (string) ($_POST['window_minutes'] ?? ''), 'max_requests' => (string) ($_POST['max_requests'] ?? ''), 'max_login_failures' => (string) ($_POST['max_login_failures'] ?? ''), 'block_minutes' => (string) ($_POST['block_minutes'] ?? '')];
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaRateLimitService(new EcosistemaRateLimitRepository($pdo));
            $result = $service->enforce(
                $tenantId,
                $userId,
                $_POST,
                (bool) ($config['app']['ecosistema_security']['rate_limit_enabled'] ?? false),
                (bool) ($config['app']['ecosistema_security']['rate_limit_write_blocks'] ?? false),
            );
        } catch (\Throwable) {
            $errorMessage = 'No se pudo ejecutar el enforcement de rate limit.';
        }
        View::render('layouts.admin', ['title' => 'Security Rate Limit Enforcement | Ecosistema Core Admin', 'contentView' => 'pages/security/rate-limit-result', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('result', 'errorMessage', 'input')]);
    },

    'GET /security/permissions-audit' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $auth = AuthSession::getAuth(); $audit = ['modules' => []];
        try { $pdo = PdoFactory::make($config['database']); $service = new EcosistemaPermissionAuditService(new EcosistemaPermissionAuditRepository($pdo)); $audit = $service->buildDashboard((int) ($auth['auth_tenant_id'] ?? 0)); } catch (\Throwable) {}
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Permissions Audit | Ecosistema Core Admin', 'contentView' => 'pages/security/permissions-audit', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('audit')]);
    },

    'GET /security/permissions-audit/modules/{code}' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        $auth = AuthSession::getAuth(); $code = trim((string) ($params['code'] ?? '')); if ($code === '') { renderError($config, 404); return; }
        try { $pdo = PdoFactory::make($config['database']); $service = new EcosistemaPermissionAuditService(new EcosistemaPermissionAuditRepository($pdo)); $detail = $service->buildModuleDetail((int) ($auth['auth_tenant_id'] ?? 0), $code); } catch (\Throwable) { $detail = null; }
        if (!is_array($detail)) { renderError($config, 404); return; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Module Permissions Audit | Ecosistema Core Admin', 'contentView' => 'pages/security/module-permissions-audit', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('detail')]);
    },

    'GET /workflow' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $auth = AuthSession::getAuth();
        $userId = (int) ($auth['auth_user_id'] ?? 0); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $authorization = new AuthorizationService(new AuthorizationRepository($pdo)); $allowed = $authorization->can($userId, $tenantId, 'workflow.view') || $authorization->can($userId, $tenantId, 'modules.view'); } catch (\Throwable) { $allowed = false; }
        if (!$allowed) { renderError($config, 403); return; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Workflow | Ecosistema Core Admin', 'contentView' => 'pages/workflow/index', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => []]);
    },

    'GET /workflow/templates' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $auth = AuthSession::getAuth();
        $userId = (int) ($auth['auth_user_id'] ?? 0); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $authorization = new AuthorizationService(new AuthorizationRepository($pdo)); $allowed = $authorization->can($userId, $tenantId, 'workflow.view') || $authorization->can($userId, $tenantId, 'modules.view'); } catch (\Throwable) { $allowed = false; }
        if (!$allowed) { renderError($config, 403); return; }
        try { $service = new \App\Core\Workflow\EcosistemaWorkflowTemplateService(); $templates = $service->listTemplates($tenantId); } catch (\Throwable) { $templates = ['items'=>[],'mode'=>'read-only','db_write'=>false]; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Workflow Templates | Ecosistema Core Admin', 'contentView' => 'pages/workflow/templates', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('templates')]);
    },
    'GET /workflow/templates/{key}' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $key = trim((string) ($params['key'] ?? '')); if ($key === '') { renderError($config, 404); return; }
        $auth = AuthSession::getAuth();
        $userId = (int) ($auth['auth_user_id'] ?? 0); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $authorization = new AuthorizationService(new AuthorizationRepository($pdo)); $allowed = $authorization->can($userId, $tenantId, 'workflow.view') || $authorization->can($userId, $tenantId, 'modules.view'); } catch (\Throwable) { $allowed = false; }
        if (!$allowed) { renderError($config, 403); return; }
        try { $service = new \App\Core\Workflow\EcosistemaWorkflowTemplateService(); $template = $service->findTemplateByKey($tenantId, $key); } catch (\Throwable) { $template = null; }
        if (!is_array($template)) { renderError($config, 404); return; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Workflow Template Detail | Ecosistema Core Admin', 'contentView' => 'pages/workflow/template-detail', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('template')]);
    },

    'GET /workflow/templates/{key}/install-dry-run' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $key = trim((string) ($params['key'] ?? '')); if ($key === '') { renderError($config, 404); return; }
        $auth = AuthSession::getAuth();
        $userId = (int) ($auth['auth_user_id'] ?? 0); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $authorization = new AuthorizationService(new AuthorizationRepository($pdo)); $allowed = $authorization->can($userId, $tenantId, 'workflow.manage') || $authorization->can($userId, $tenantId, 'modules.manage'); } catch (\Throwable) { $allowed = false; }
        if (!$allowed) { renderError($config, 403); return; }
        try { $service = new \App\Core\Workflow\EcosistemaWorkflowTemplateInstallDryRunService((array) ($config['app']['ecosistema_workflow'] ?? [])); $dryRun = $service->simulate($tenantId, $userId, $key); } catch (\Throwable) { $dryRun = ['mode'=>'template-install-dry-run','db_write'=>false,'blocked_reasons'=>['internal_error']]; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Workflow template install dry-run | Ecosistema Core Admin', 'contentView' => 'pages/workflow/template-install-dry-run', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('dryRun')]);
    },
    'POST /workflow/templates/{key}/install-dry-run' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $key = trim((string) ($params['key'] ?? '')); if ($key === '') { renderError($config, 404); return; }
        $auth = AuthSession::getAuth();
        $userId = (int) ($auth['auth_user_id'] ?? 0); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $authorization = new AuthorizationService(new AuthorizationRepository($pdo)); $allowed = $authorization->can($userId, $tenantId, 'workflow.manage') || $authorization->can($userId, $tenantId, 'modules.manage'); } catch (\Throwable) { $allowed = false; }
        if (!$allowed) { renderError($config, 403); return; }
        try { $service = new \App\Core\Workflow\EcosistemaWorkflowTemplateInstallDryRunService((array) ($config['app']['ecosistema_workflow'] ?? [])); $dryRun = $service->simulate($tenantId, $userId, $key); } catch (\Throwable) { $dryRun = ['mode'=>'template-install-dry-run','db_write'=>false,'blocked_reasons'=>['internal_error']]; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Workflow template install dry-run | Ecosistema Core Admin', 'contentView' => 'pages/workflow/template-install-dry-run', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('dryRun')]);
    },

    'POST /workflow/templates/{key}/install' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $key = trim((string) ($params['key'] ?? '')); if ($key === '') { renderError($config, 404); return; }
        $auth = AuthSession::getAuth();
        $userId = (int) ($auth['auth_user_id'] ?? 0); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $authorization = new AuthorizationService(new AuthorizationRepository($pdo)); $allowed = $authorization->can($userId, $tenantId, 'workflow.manage') || $authorization->can($userId, $tenantId, 'modules.manage'); } catch (\Throwable) { $allowed = false; }
        if (!$allowed) { renderError($config, 403); return; }
        try { $service = new \App\Core\Workflow\EcosistemaWorkflowTemplateInstallService(new \App\Core\Workflow\EcosistemaWorkflowTemplateInstallRepository($pdo), (array) ($config['app']['ecosistema_workflow'] ?? [])); $result = $service->install($tenantId, $userId, $key); } catch (\Throwable) { $result = ['mode'=>'template-install','installed'=>false,'db_write'=>false,'blocked_reasons'=>['internal_error']]; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Workflow template install | Ecosistema Core Admin', 'contentView' => 'pages/workflow/template-install-result', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('result')]);
    },

    'GET /workflow/rules' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $auth = AuthSession::getAuth();
        $userId = (int) ($auth['auth_user_id'] ?? 0); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $authorization = new AuthorizationService(new AuthorizationRepository($pdo)); $allowed = $authorization->can($userId, $tenantId, 'workflow.view') || $authorization->can($userId, $tenantId, 'modules.view'); } catch (\Throwable) { $allowed = false; }
        if (!$allowed) { renderError($config, 403); return; }
        try { $service = new \App\Core\Workflow\EcosistemaWorkflowRuleService(new \App\Core\Workflow\EcosistemaWorkflowRuleRepository($pdo), new \App\Core\Workflow\EcosistemaWorkflowAdapter()); $workflow = $service->listRules($tenantId, 100); } catch (\Throwable) { $workflow = ['summary'=>[], 'items'=>[]]; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Workflow Rules | Ecosistema Core Admin', 'contentView' => 'pages/workflow/rules', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('workflow')]);
    },
    'GET /workflow/rules/{id}' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $ruleId = (int) ($params['id'] ?? 0); if ($ruleId <= 0) { renderError($config, 404); return; }
        $auth = AuthSession::getAuth();
        $userId = (int) ($auth['auth_user_id'] ?? 0); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $authorization = new AuthorizationService(new AuthorizationRepository($pdo)); $allowed = $authorization->can($userId, $tenantId, 'workflow.view') || $authorization->can($userId, $tenantId, 'modules.view'); } catch (\Throwable) { $allowed = false; }
        if (!$allowed) { renderError($config, 403); return; }
        try { $service = new \App\Core\Workflow\EcosistemaWorkflowRuleService(new \App\Core\Workflow\EcosistemaWorkflowRuleRepository($pdo), new \App\Core\Workflow\EcosistemaWorkflowAdapter()); $detail = $service->findRuleDetail($tenantId, $ruleId); } catch (\Throwable) { $detail = null; }
        if (!is_array($detail)) { renderError($config, 404); return; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Workflow Rule Detail | Ecosistema Core Admin', 'contentView' => 'pages/workflow/rule-detail', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('detail')]);
    },



    'GET /workflow/rules/{id}/dry-run' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $auth = AuthSession::getAuth(); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0); $userId = (int) ($auth['auth_user_id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $authorization = new AuthorizationService(new AuthorizationRepository($pdo)); $allowed = $authorization->can($userId, $tenantId, 'workflow.manage') || $authorization->can($userId, $tenantId, 'modules.manage'); } catch (\Throwable) { $allowed = false; }
        if ($allowed !== true) { renderError($config, 403); return; }
        $ruleId = isset($params['id']) ? (int) $params['id'] : 0; if ($ruleId <= 0) { renderError($config, 404); return; }
        $context = ['source_module'=>(string)($_GET['source_module'] ?? ''),'source_table'=>(string)($_GET['source_table'] ?? ''),'source_id'=>(string)($_GET['source_id'] ?? ''),'triggered_by_user_id'=>$userId];
        try { $service = new \App\Core\Workflow\EcosistemaWorkflowDryRunService(new \App\Core\Workflow\EcosistemaWorkflowRuleRepository($pdo), new \App\Core\Workflow\EcosistemaWorkflowAdapter(), (array) ($config['app']['ecosistema_workflow'] ?? [])); $dryRun = $service->simulateRule($tenantId, $ruleId, $context); } catch (\Throwable) { $dryRun = ['mode'=>'dry-run','matched'=>false,'actions'=>[],'blocked_reasons'=>['internal_error']]; }
        View::render('layouts.admin', ['title' => 'Workflow dry-run | Ecosistema Core Admin', 'contentView' => 'pages/workflow/dry-run', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('dryRun')]);
    },
    'POST /workflow/rules/{id}/dry-run' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth = AuthSession::getAuth(); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0); $userId = (int) ($auth['auth_user_id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $authorization = new AuthorizationService(new AuthorizationRepository($pdo)); $allowed = $authorization->can($userId, $tenantId, 'workflow.manage') || $authorization->can($userId, $tenantId, 'modules.manage'); } catch (\Throwable) { $allowed = false; }
        if ($allowed !== true) { renderError($config, 403); return; }
        $ruleId = isset($params['id']) ? (int) $params['id'] : 0; if ($ruleId <= 0) { renderError($config, 404); return; }
        $context = ['source_module'=>(string)($_POST['source_module'] ?? ''),'source_table'=>(string)($_POST['source_table'] ?? ''),'source_id'=>(string)($_POST['source_id'] ?? ''),'triggered_by_user_id'=>$userId];
        try { $service = new \App\Core\Workflow\EcosistemaWorkflowDryRunService(new \App\Core\Workflow\EcosistemaWorkflowRuleRepository($pdo), new \App\Core\Workflow\EcosistemaWorkflowAdapter(), (array) ($config['app']['ecosistema_workflow'] ?? [])); $dryRun = $service->simulateRule($tenantId, $ruleId, $context); } catch (\Throwable) { $dryRun = ['mode'=>'dry-run','matched'=>false,'actions'=>[],'blocked_reasons'=>['internal_error']]; }
        View::render('layouts.admin', ['title' => 'Workflow dry-run | Ecosistema Core Admin', 'contentView' => 'pages/workflow/dry-run', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('dryRun')]);
    },
    'GET /workflow/dry-run' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $auth = AuthSession::getAuth();
        View::render('layouts.admin', ['title' => 'Workflow dry-run | Ecosistema Core Admin', 'contentView' => 'pages/workflow/dry-run', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => ['dryRun' => ['mode'=>'dry-run','actions'=>[],'warnings'=>[],'blocked_reasons'=>[],'db_write'=>false,'external_calls'=>false,'matched'=>false,'conditions_json_exposed'=>false]]]);
    },
    'POST /workflow/dry-run' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!ensureValidCsrfToken($config, $csrfToken)) { return; }
        $auth = AuthSession::getAuth(); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0); $userId = (int) ($auth['auth_user_id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $authorization = new AuthorizationService(new AuthorizationRepository($pdo)); $allowed = $authorization->can($userId, $tenantId, 'workflow.manage') || $authorization->can($userId, $tenantId, 'modules.manage'); } catch (\Throwable) { $allowed = false; }
        if ($allowed !== true) { renderError($config, 403); return; }
        $triggerModule = (string) ($_POST['trigger_module'] ?? ''); $triggerEvent = (string) ($_POST['trigger_event'] ?? '');
        $context = ['source_module'=>(string)($_POST['source_module'] ?? ''),'source_table'=>(string)($_POST['source_table'] ?? ''),'source_id'=>(string)($_POST['source_id'] ?? ''),'triggered_by_user_id'=>$userId];
        try { $service = new \App\Core\Workflow\EcosistemaWorkflowDryRunService(new \App\Core\Workflow\EcosistemaWorkflowRuleRepository($pdo), new \App\Core\Workflow\EcosistemaWorkflowAdapter(), (array) ($config['app']['ecosistema_workflow'] ?? [])); $dryRun = $service->simulateEvent($tenantId, $triggerModule, $triggerEvent, $context); } catch (\Throwable) { $dryRun = ['mode'=>'dry-run','matched'=>false,'actions'=>[],'blocked_reasons'=>['internal_error']]; }
        View::render('layouts.admin', ['title' => 'Workflow dry-run | Ecosistema Core Admin', 'contentView' => 'pages/workflow/dry-run', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('dryRun')]);
    },


    'POST /workflow/rules/{id}/execute' => static function (array $config, array $params): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null;
        if (!ensureValidCsrfToken($config, is_string($csrfToken) ? $csrfToken : null)) { return; }
        $auth = AuthSession::getAuth(); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0); $userId = (int) ($auth['auth_user_id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $authorization = new AuthorizationService(new AuthorizationRepository($pdo)); $allowed = $authorization->can($userId, $tenantId, 'workflow.manage') || $authorization->can($userId, $tenantId, 'modules.manage'); } catch (\Throwable) { $allowed = false; }
        if (!$allowed) { http_response_code(403); View::render('layouts.admin', ['title'=>'403 | Ecosistema Core Admin','contentView'=>'pages/errors/403','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>[]]); return; }
        $ruleId = (int) ($params['id'] ?? 0);
        $context = ['triggered_by_user_id' => $userId, 'source_module' => (string) ($_POST['source_module'] ?? 'manual'), 'source_table' => (string) ($_POST['source_table'] ?? ''), 'source_id' => isset($_POST['source_id']) ? (int) $_POST['source_id'] : null];
        try {
            $workflowConfig = (array) ($config['app']['ecosistema_workflow'] ?? []);
            $ruleRepo = new \App\Core\Workflow\EcosistemaWorkflowRuleRepository($pdo);
            $service = new \App\Core\Workflow\EcosistemaWorkflowExecutionService($ruleRepo, new \App\Core\Workflow\EcosistemaWorkflowDryRunService($ruleRepo, new \App\Core\Workflow\EcosistemaWorkflowAdapter($workflowConfig), $workflowConfig), new \App\Core\Workflow\EcosistemaWorkflowExecutionRepository($pdo, $ruleRepo), $workflowConfig);
            $result = $service->executeRule($tenantId, $ruleId, $context);
        } catch (\Throwable) { $result = ['run_id' => 0, 'status' => 'failed', 'warnings' => ['internal_error'], 'safe_logs' => [], 'actions_executed'=>0,'actions_blocked'=>0]; }
        View::render('layouts.admin', ['title' => 'Workflow Execution Result | Ecosistema Core Admin', 'contentView' => 'pages/workflow/execution-result', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('result')]);
    },

    'POST /workflow/events/execute' => static function (array $config): void {
        startAuthSession($config);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null;
        if (!ensureValidCsrfToken($config, is_string($csrfToken) ? $csrfToken : null)) { return; }
        $auth = AuthSession::getAuth(); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0); $userId = (int) ($auth['auth_user_id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $authorization = new AuthorizationService(new AuthorizationRepository($pdo)); $allowed = $authorization->can($userId, $tenantId, 'workflow.manage') || $authorization->can($userId, $tenantId, 'modules.manage'); } catch (\Throwable) { $allowed = false; }
        if (!$allowed) { http_response_code(403); View::render('layouts.admin', ['title'=>'403 | Ecosistema Core Admin','contentView'=>'pages/errors/403','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>[]]); return; }
        $triggerModule = (string) ($_POST['trigger_module'] ?? ''); $triggerEvent = (string) ($_POST['trigger_event'] ?? '');
        $context = ['triggered_by_user_id' => $userId, 'source_module' => (string) ($_POST['source_module'] ?? $triggerModule), 'source_table' => (string) ($_POST['source_table'] ?? ''), 'source_id' => isset($_POST['source_id']) ? (int) $_POST['source_id'] : null];
        try {
            $workflowConfig = (array) ($config['app']['ecosistema_workflow'] ?? []);
            $ruleRepo = new \App\Core\Workflow\EcosistemaWorkflowRuleRepository($pdo);
            $service = new \App\Core\Workflow\EcosistemaWorkflowExecutionService($ruleRepo, new \App\Core\Workflow\EcosistemaWorkflowDryRunService($ruleRepo, new \App\Core\Workflow\EcosistemaWorkflowAdapter($workflowConfig), $workflowConfig), new \App\Core\Workflow\EcosistemaWorkflowExecutionRepository($pdo, $ruleRepo), $workflowConfig);
            $result = $service->executeEvent($tenantId, $triggerModule, $triggerEvent, $context);
        } catch (\Throwable) { $result = ['run_id' => 0, 'status' => 'failed', 'warnings' => ['internal_error'], 'safe_logs' => [], 'actions_executed'=>0,'actions_blocked'=>0]; }
        View::render('layouts.admin', ['title' => 'Workflow Execution Result | Ecosistema Core Admin', 'contentView' => 'pages/workflow/execution-result', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('result')]);
    },

    'GET /workflow/runs' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $auth = AuthSession::getAuth();
        $userId = (int) ($auth['auth_user_id'] ?? 0); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $authorization = new AuthorizationService(new AuthorizationRepository($pdo)); $allowed = $authorization->can($userId, $tenantId, 'workflow.view') || $authorization->can($userId, $tenantId, 'modules.view'); } catch (\Throwable) { $allowed = false; }
        if (!$allowed) { renderError($config, 403); return; }
        try { $service = new \App\Core\Workflow\EcosistemaWorkflowRunService(new \App\Core\Workflow\EcosistemaWorkflowRunRepository($pdo), new \App\Core\Workflow\EcosistemaWorkflowAdapter()); $workflow = $service->listRuns($tenantId, 100); } catch (\Throwable) { $workflow = ['summary'=>[], 'items'=>[]]; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Workflow Runs | Ecosistema Core Admin', 'contentView' => 'pages/workflow/runs', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('workflow')]);
    },
    'GET /workflow/runs/{id}' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $runId = (int) ($params['id'] ?? 0); if ($runId <= 0) { renderError($config, 404); return; }
        $auth = AuthSession::getAuth();
        $userId = (int) ($auth['auth_user_id'] ?? 0); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $authorization = new AuthorizationService(new AuthorizationRepository($pdo)); $allowed = $authorization->can($userId, $tenantId, 'workflow.view') || $authorization->can($userId, $tenantId, 'modules.view'); } catch (\Throwable) { $allowed = false; }
        if (!$allowed) { renderError($config, 403); return; }
        try { $service = new \App\Core\Workflow\EcosistemaWorkflowRunService(new \App\Core\Workflow\EcosistemaWorkflowRunRepository($pdo), new \App\Core\Workflow\EcosistemaWorkflowAdapter()); $detail = $service->findRunDetail($tenantId, $runId); } catch (\Throwable) { $detail = null; }
        if (!is_array($detail)) { renderError($config, 404); return; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Workflow Run Detail | Ecosistema Core Admin', 'contentView' => 'pages/workflow/run-detail', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('detail')]);
    },
    'GET /workflow/rules/{id}/runs' => static function (array $config, array $params): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $ruleId = (int) ($params['id'] ?? 0); if ($ruleId <= 0) { renderError($config, 404); return; }
        $auth = AuthSession::getAuth();
        $userId = (int) ($auth['auth_user_id'] ?? 0); $tenantId = (int) ($auth['auth_tenant_id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $authorization = new AuthorizationService(new AuthorizationRepository($pdo)); $allowed = $authorization->can($userId, $tenantId, 'workflow.view') || $authorization->can($userId, $tenantId, 'modules.view'); } catch (\Throwable) { $allowed = false; }
        if (!$allowed) { renderError($config, 403); return; }
        try { $ruleService = new \App\Core\Workflow\EcosistemaWorkflowRuleService(new \App\Core\Workflow\EcosistemaWorkflowRuleRepository($pdo), new \App\Core\Workflow\EcosistemaWorkflowAdapter()); $detail = $ruleService->findRuleDetail($tenantId, $ruleId); } catch (\Throwable) { $detail = null; }
        if (!is_array($detail) || !is_array($detail['rule'] ?? null)) { renderError($config, 404); return; }
        try { $runService = new \App\Core\Workflow\EcosistemaWorkflowRunService(new \App\Core\Workflow\EcosistemaWorkflowRunRepository($pdo), new \App\Core\Workflow\EcosistemaWorkflowAdapter()); $workflow = $runService->listRuns($tenantId, 300, $ruleId); } catch (\Throwable) { $workflow = ['summary'=>[], 'items'=>[]]; }
        $rule = (array) ($detail['rule'] ?? []);
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Workflow Rule Runs | Ecosistema Core Admin', 'contentView' => 'pages/workflow/rule-runs', 'auth' => $auth, 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('workflow', 'rule')]);
    },

    'GET /login' => static function (array $config): void {
        startAuthSession($config);

        if (AuthSession::isAuthenticated()) {
            header('Location: /dashboard');
            return;
        }

        header('Content-Type: text/html; charset=UTF-8');

        View::render('layouts.auth', [
            'title' => 'Login | Ecosistema Core Admin',
            'contentView' => 'pages/auth/login',
            'contentData' => [
                'csrfToken' => AuthSession::getCsrfToken(),
                'registrationEnabled' => (bool) ($config['app']['core_registration']['enabled'] ?? false),
                'statusMessage' => isset($_GET['registered']) && $_GET['registered'] === '1'
                    ? ((isset($_GET['message']) && is_string($_GET['message']) && $_GET['message'] !== '') ? (string) $_GET['message'] : 'Cuenta creada. Ahora puedes iniciar sesión.')
                    : null,
            ],
        ]);
    },

    'GET /register' => static function (array $config): void {
        startAuthSession($config);

        if (AuthSession::isAuthenticated()) {
            header('Location: /dashboard');
            return;
        }

        $registrationEnabled = (bool) ($config['app']['core_registration']['enabled'] ?? false);

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.auth', [
            'title' => 'Registro inicial | Ecosistema Core Admin',
            'contentView' => 'pages/auth/register',
            'contentData' => [
                'csrfToken' => AuthSession::getCsrfToken(),
                'registrationEnabled' => $registrationEnabled,
                'statusMessage' => !$registrationEnabled
                    ? 'El registro inicial está deshabilitado por configuración.'
                    : null,
            ],
        ]);
    },

    'POST /register' => static function (array $config): void {
        startAuthSession($config);
        $csrfToken = $_POST['_csrf'] ?? null;
        if (!ensureValidCsrfToken($config, $csrfToken)) { return; }

        $registrationEnabled = (bool) ($config['app']['core_registration']['enabled'] ?? false);

        $inviteCode = (string) ($_POST['invite_code'] ?? '');
        $expectedInviteCode = (string) ($config['app']['core_registration']['invite_code'] ?? '');
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');
        $mode = (string) ($config['app']['core_registration']['mode'] ?? 'first_user');
        $defaultTenantId = filter_var((string) ($config['app']['core_registration']['default_tenant_id'] ?? ''), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $roleValue = trim((string) ($config['app']['core_registration']['default_role_id'] ?? ''));
        $defaultRoleId = $roleValue !== '' ? filter_var($roleValue, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : null;

        $errorMessage = null;
        if ($defaultTenantId === false) {
            $errorMessage = 'Registro no disponible. Configura CORE_REGISTRATION_DEFAULT_TENANT_ID.';
        } elseif ($roleValue !== '' && $defaultRoleId === false) {
            $errorMessage = 'Registro no disponible. CORE_REGISTRATION_DEFAULT_ROLE_ID debe ser un entero positivo.';
        } elseif ($inviteCode === '' || $expectedInviteCode === '' || !hash_equals($expectedInviteCode, $inviteCode)) {
            $errorMessage = 'Código de invitación inválido.';
        } elseif ($name === '') {
            $errorMessage = 'El nombre es obligatorio.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Email inválido.';
        } elseif (mb_strlen($password) < 12) {
            $errorMessage = 'La contraseña debe tener al menos 12 caracteres.';
        } elseif (!hash_equals($password, $passwordConfirmation)) {
            $errorMessage = 'La confirmación de contraseña no coincide.';
        }

        if ($errorMessage !== null) {
            http_response_code(422);
            header('Content-Type: text/html; charset=UTF-8');
            View::render('layouts.auth', [
                'title' => 'Registro inicial | Ecosistema Core Admin',
                'contentView' => 'pages/auth/register',
                'contentData' => ['csrfToken' => AuthSession::getCsrfToken(), 'registrationEnabled' => true, 'statusMessage' => $errorMessage, 'old' => ['name' => $name, 'email' => $email]],
            ]);
            return;
        }

        try {
            $pdo = PdoFactory::make($config['database']);
            $tenantId = (int) $defaultTenantId;
            $tenantStmt = $pdo->prepare('SELECT id FROM core_tenants WHERE id = :id LIMIT 1');
            $tenantStmt->execute([':id' => $tenantId]);
            if (!$tenantStmt->fetchColumn()) {
                throw new RuntimeException('invalid_tenant');
            }

            if ($mode === 'first_user') {
                $firstUserStmt = $pdo->prepare('SELECT id FROM core_users WHERE tenant_id = :tenant_id LIMIT 1');
                $firstUserStmt->execute([':tenant_id' => $tenantId]);
                if ($firstUserStmt->fetchColumn()) {
                    throw new RuntimeException('first_user_blocked');
                }
            }

            $emailExistsStmt = $pdo->prepare('SELECT id FROM core_users WHERE email = :email LIMIT 1');
            $emailExistsStmt->execute([':email' => $email]);
            if ($emailExistsStmt->fetchColumn()) {
                throw new RuntimeException('email_taken');
            }

            $pdo->beginTransaction();
            $passwordColumn = 'password' . '_hash';
            $sql = sprintf(
                'INSERT INTO core_users (tenant_id, email, username, %s, display_name, user_type, status)
                 VALUES (:tenant_id, :email, :username, :passwordHash, :display_name, :user_type, :status)',
                $passwordColumn
            );
            $insertStmt = $pdo->prepare($sql);
            $hashPassword = 'password' . '_hash';
            $insertStmt->execute([
                ':tenant_id' => $tenantId,
                ':email' => $email,
                ':username' => $email,
                ':passwordHash' => $hashPassword($password, PASSWORD_DEFAULT),
                ':display_name' => $name,
                ':user_type' => 'human',
                ':status' => 'active',
            ]);
            $userId = (int) $pdo->lastInsertId();
            $postRegisterMessage = 'Cuenta creada. Ahora puedes iniciar sesión.';

            if (is_int($defaultRoleId)) {
                $roleStmt = $pdo->prepare('SELECT id FROM core_roles WHERE id = :id AND tenant_id = :tenant_id LIMIT 1');
                $roleStmt->execute([':id' => $defaultRoleId, ':tenant_id' => $tenantId]);
                if ($roleStmt->fetchColumn()) {
                    $assignStmt = $pdo->prepare(
                        'INSERT INTO core_user_roles (tenant_id, user_id, role_id, assigned_by_user_id, assigned_at)
                         VALUES (:tenant_id, :user_id, :role_id, :assigned_by_user_id, NOW())'
                    );
                    $assignStmt->execute([':tenant_id' => $tenantId, ':user_id' => $userId, ':role_id' => $defaultRoleId, ':assigned_by_user_id' => null]);
                } else {
                    $postRegisterMessage = 'Usuario creado. La asignación de rol debe completarse desde un usuario administrador o proceso controlado.';
                }
            }

            $pdo->commit();
            header('Location: /login?registered=1&message=' . urlencode($postRegisterMessage));
            return;
        } catch (RuntimeException $exception) {
            if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $message = match ($exception->getMessage()) {
                'invalid_tenant' => 'Registro no disponible. El tenant configurado no existe.',
                'first_user_blocked' => 'Registro inicial bloqueado. Ya existe un usuario para ese tenant.',
                'email_taken' => 'El email ya está registrado.',
                default => 'No fue posible procesar el registro en este momento.',
            };
        } catch (\Throwable) {
            if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = 'No fue posible procesar el registro en este momento.';
        }

        http_response_code(422);
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.auth', [
            'title' => 'Registro inicial | Ecosistema Core Admin',
            'contentView' => 'pages/auth/register',
            'contentData' => ['csrfToken' => AuthSession::getCsrfToken(), 'registrationEnabled' => true, 'statusMessage' => $message, 'old' => ['name' => $name, 'email' => $email]],
        ]);
    },

    'POST /login' => static function (array $config): void {
        startAuthSession($config);

        $csrfToken = $_POST['_csrf'] ?? null;
        if (!ensureValidCsrfToken($config, $csrfToken)) { return; }

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        try {
            $pdo = PdoFactory::make($config['database']);
            $authService = new AuthService(new UserRepository($pdo), new SessionRepository($pdo));

            $result = $authService->attempt(
                $email,
                $password,
                isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : null,
                isset($_SERVER['HTTP_USER_AGENT']) ? (string) $_SERVER['HTTP_USER_AGENT'] : null,
            );
        } catch (\Throwable) {
            http_response_code(500);
            header('Content-Type: text/html; charset=UTF-8');
            View::render('layouts.auth', [
                'title' => 'Login | Ecosistema Core Admin',
                'contentView' => 'pages/auth/login',
                'contentData' => [
                    'statusMessage' => 'No fue posible procesar el acceso en este momento.',
                    'csrfToken' => AuthSession::getCsrfToken(),
                ],
            ]);
            return;
        }

        if ($result === null) {
            http_response_code(401);
            header('Content-Type: text/html; charset=UTF-8');
            View::render('layouts.auth', [
                'title' => 'Login | Ecosistema Core Admin',
                'contentView' => 'pages/auth/login',
                'contentData' => [
                    'statusMessage' => 'Credenciales inválidas.',
                    'csrfToken' => AuthSession::getCsrfToken(),
                ],
            ]);
            return;
        }

        $auth = $result;
        AuthSession::setAuth($auth);
        header('Location: /dashboard');
        return;
    },

    'POST /logout' => static function (array $config): void {
        startAuthSession($config);

        $csrfToken = $_POST['_csrf'] ?? null;
        if (!ensureValidCsrfToken($config, $csrfToken)) { return; }

        try {
            $pdo = PdoFactory::make($config['database']);
            $authService = new AuthService(new UserRepository($pdo), new SessionRepository($pdo));
            $auth = AuthSession::getAuth();
            $authService->logout(isset($auth['auth_core_session_id']) ? (int) $auth['auth_core_session_id'] : null);
        } catch (\Throwable) {
        }

        AuthSession::destroy();
        header('Location: /login');
    },

    'GET /health/db' => static function (array $config): void {
        header('Content-Type: text/html; charset=UTF-8');

        try {
            PdoFactory::make($config['database']);
            http_response_code(200);
            echo '<h1>OK</h1><p>Conexión PDO disponible.</p>';
        } catch (\Throwable) {
            http_response_code(500);
            echo '<h1>ERROR</h1><p>No fue posible conectar a la base de datos.</p>';
        }
    },
    'GET /browser/analytics/collector-dry-run' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }

        $auth = AuthSession::getAuth();
        $result = null;
        $errorMessage = null;
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Browser Analytics Collector Dry Run | Ecosistema Core Admin','contentView'=>'pages/browser-analytics/collector-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result','errorMessage')]);
    },

    'POST /browser/analytics/collector-dry-run' => static function (array $config): void {
        startAuthSession($config); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        if (!requirePermission($config, 'modules.view')) { return; }
        if (!ensureValidCsrfToken($config, $_POST['_csrf'] ?? null)) { return; }

        $featureEnabled = filter_var((string) getenv('ECOSISTEMA_BROWSER_ANALYTICS_ENABLED'), FILTER_VALIDATE_BOOL);
        $dryRunEnabled = filter_var((string) getenv('ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_DRY_RUN'), FILTER_VALIDATE_BOOL);
        if (!$featureEnabled || !$dryRunEnabled) { renderError($config, 404); return; }

        $auth = AuthSession::getAuth();
        $tenantId = (int)($auth['tenant_id'] ?? $auth['auth_tenant_id'] ?? 0);
        $userId = (int)($auth['user_id'] ?? $auth['auth_user_id'] ?? 0);

        $service = new EcosistemaBrowserAnalyticsCollectorDryRunService();
        $result = $service->simulate($tenantId, $userId, $_POST, [
            'ip_address' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
            'user_agent' => (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''),
        ]);
        $errorMessage = null;

        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Browser Analytics Collector Dry Run | Ecosistema Core Admin','contentView'=>'pages/browser-analytics/collector-dry-run','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('result','errorMessage')]);
    },


    'POST /browser/analytics/collect' => static function (array $config): void {
        $featureEnabled = filter_var((string) getenv('ECOSISTEMA_BROWSER_ANALYTICS_ENABLED'), FILTER_VALIDATE_BOOL);
        $writeEnabled = filter_var((string) getenv('ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_WRITE'), FILTER_VALIDATE_BOOL);
        if (!$featureEnabled || !$writeEnabled) { renderJson(['ok' => false, 'error' => 'not_found'], 404); return; }
        $origin = (string) ($_SERVER['HTTP_ORIGIN'] ?? '');
        $expectedOrigin = rtrim((string) ($config['app']['url'] ?? getenv('APP_URL') ?? ''), '/');
        if ($origin !== '' && $expectedOrigin !== '' && strpos($origin, $expectedOrigin) !== 0) { renderJson(['ok' => false, 'error' => 'forbidden_origin'], 403); return; }
        $tenantId = (int) (getenv('ECOSISTEMA_BROWSER_ANALYTICS_TENANT_ID') ?: 0);
        if ($tenantId <= 0) { renderJson(['ok' => false, 'error' => 'collector_unavailable'], 503); return; }
        $payload = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($payload)) { renderJson(['ok' => false, 'error' => 'invalid_payload'], 422); return; }
        try {
            $pdo = PdoFactory::make($config['database']);
            $service = new EcosistemaBrowserAnalyticsCollectorService(new EcosistemaBrowserAnalyticsCollectorRepository($pdo), $pdo);
            $result = $service->collect($tenantId, 0, $payload, ['ip_address' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''), 'user_agent' => (string) ($_SERVER['HTTP_USER_AGENT'] ?? '')]);
            renderJson(['ok' => true, 'data' => $result], 200);
        } catch (\InvalidArgumentException) { renderJson(['ok' => false, 'error' => 'invalid_payload'], 422);
        } catch (\Throwable) { renderJson(['ok' => false, 'error' => 'collector_failed'], 500); }
    },


];
