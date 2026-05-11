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
use App\Core\Mail\MailService;
use App\Core\Mail\MailMessageRepository;
use App\Core\Mail\MailboxRepository;
use App\Http\View\View;

return [
    'GET /' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);

        if (AuthSession::isAuthenticated()) {
            header('Location: /dashboard');
            return;
        }

        header('Location: /login');
    },


    'GET /dashboard' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);

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
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }

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
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Crear tenant | Ecosistema Core Admin','contentView'=>'pages/tenants/create','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>[]]);
    },

    'POST /tenants' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null;
        if (!AuthSession::validateCsrfToken(is_string($csrfToken) ? $csrfToken : null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        try { $pdo=PdoFactory::make($config['database']); $service=new TenantService(new TenantRepository($pdo)); $ok=$service->createTenant($_POST); } catch (\Throwable) { $ok=false; }
        header('Location: '.($ok ? '/tenants?ok=1' : '/tenants/create?error=1'));
    },

    'GET /tenants/{id}/edit' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $id = (int) ($params['id'] ?? 0);
        try { $pdo=PdoFactory::make($config['database']); $service=new TenantService(new TenantRepository($pdo)); $tenant=$service->findTenant($id); } catch (\Throwable) { $tenant=null; }
        if ($tenant === null) { http_response_code(404); header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'404 | Ecosistema Core Admin','contentView'=>'pages/tenants/index','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>['tenants'=>[],'errorMessage'=>'Tenant no encontrado.']]); return; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Editar tenant | Ecosistema Core Admin','contentView'=>'pages/tenants/edit','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>['tenant'=>$tenant]]);
    },

    'POST /tenants/{id}' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken)?$csrfToken:null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        $id=(int) ($params['id'] ?? 0);
        try { $pdo=PdoFactory::make($config['database']); $service=new TenantService(new TenantRepository($pdo)); if ($service->findTenant($id)===null) { http_response_code(404); echo 'Tenant no encontrado.'; return; } $ok=$service->updateTenant($id,$_POST);} catch (\Throwable) { $ok=false; }
        header('Location: '.($ok ? '/tenants?ok=2' : '/tenants/'.(string)$id.'/edit?error=1'));
    },

    'POST /tenants/{id}/status' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken)?$csrfToken:null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        $id=(int) ($params['id'] ?? 0);
        try { $pdo=PdoFactory::make($config['database']); $service=new TenantService(new TenantRepository($pdo)); $ok=$service->changeStatus($id,(string)($_POST['status'] ?? '')); } catch (\Throwable) { $ok=false; }
        header('Location: '.($ok ? '/tenants?ok=2' : '/tenants?error=1'));
    },


    'GET /modules' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $statusMessage = isset($_GET['ok']) ? (string) $_GET['ok'] : null; $errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;
        try { $pdo=PdoFactory::make($config['database']); $service=new ModuleService(new ModuleRepository($pdo)); $modules=$service->listModules(); } catch (\Throwable) { $modules=[]; $errorMessage='No se pudo guardar el módulo.'; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Módulos | Ecosistema Core Admin','contentView'=>'pages/modules/index','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('modules','statusMessage','errorMessage')]);
    },
    'GET /modules/create' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Crear módulo | Ecosistema Core Admin','contentView'=>'pages/modules/create','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('errorMessage')]);
    },
    'POST /modules' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken)?$csrfToken:null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        try { $pdo=PdoFactory::make($config['database']); $service=new ModuleService(new ModuleRepository($pdo)); $message=$service->createModule($_POST); } catch (\Throwable $e) { $message = str_contains(strtolower($e->getMessage()), 'duplicate') || str_contains(strtolower($e->getMessage()), 'unique') ? 'Ya existe un módulo con ese código.' : 'No se pudo guardar el módulo.'; }
        header('Location: '.($message==='Módulo creado correctamente.'?'/modules?ok='.urlencode($message):'/modules/create?error='.urlencode($message)));
    },
    'GET /modules/{id}/edit' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $id=(int)($params['id']??0); $errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;
        try { $pdo=PdoFactory::make($config['database']); $service=new ModuleService(new ModuleRepository($pdo)); $module=$service->findModule($id);} catch (\Throwable) { $module=null; }
        if($module===null){ http_response_code(404); echo 'Módulo no encontrado.'; return; }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Editar módulo | Ecosistema Core Admin','contentView'=>'pages/modules/edit','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('module','errorMessage')]);
    },
    'POST /modules/{id}' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken)?$csrfToken:null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        $id=(int)($params['id']??0);
        try { $pdo=PdoFactory::make($config['database']); $service=new ModuleService(new ModuleRepository($pdo)); $message=$service->updateModule($id,$_POST);} catch (\Throwable $e) { $message = str_contains(strtolower($e->getMessage()), 'duplicate') || str_contains(strtolower($e->getMessage()), 'unique') ? 'Ya existe un módulo con ese código.' : 'No se pudo guardar el módulo.'; }
        header('Location: '.($message==='Módulo actualizado correctamente.'?'/modules?ok='.urlencode($message):($message==='Módulo no encontrado.'?'/modules?error='.urlencode($message):'/modules/'.$id.'/edit?error='.urlencode($message))));
    },
    'POST /modules/{id}/status' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken)?$csrfToken:null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        $id=(int)($params['id']??0);
        try { $pdo=PdoFactory::make($config['database']); $service=new ModuleService(new ModuleRepository($pdo)); $message=$service->changeStatus($id,(string)($_POST['status']??'')); } catch (\Throwable) { $message='No se pudo guardar el módulo.'; }
        header('Location: '.($message==='Estado actualizado correctamente.'?'/modules?ok='.urlencode($message):'/modules?error='.urlencode($message)));
    },


    'GET /roles' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $statusMessage = isset($_GET['ok']) ? (string) $_GET['ok'] : null;
        $errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;
        try { $pdo = PdoFactory::make($config['database']); $service = new RoleService(new RoleRepository($pdo)); $roles = $service->listRoles(); } catch (\Throwable) { $roles = []; $errorMessage = 'No se pudo guardar el rol.'; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Roles | Ecosistema Core Admin','contentView'=>'pages/roles/index','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('roles','statusMessage','errorMessage')]);
    },
    'GET /roles/create' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        try { $pdo = PdoFactory::make($config['database']); $service = new RoleService(new RoleRepository($pdo)); $tenants = $service->listTenants(); } catch (\Throwable) { $tenants = []; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Crear rol | Ecosistema Core Admin','contentView'=>'pages/roles/create','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('tenants')]);
    },
    'POST /roles' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken) ? $csrfToken : null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        try { $pdo = PdoFactory::make($config['database']); $service = new RoleService(new RoleRepository($pdo)); $message = $service->createRole($_POST); } catch (\Throwable) { $message = 'No se pudo guardar el rol.'; }
        header('Location: '.($message==='Rol creado correctamente.' ? '/roles?ok='.urlencode($message) : '/roles/create?error='.urlencode($message)));
    },
    'GET /roles/{id}/edit' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $id = (int) ($params['id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $service = new RoleService(new RoleRepository($pdo)); $role = $service->findRole($id); $tenants = $service->listTenants(); } catch (\Throwable) { $role = null; $tenants = []; }
        if ($role === null) { http_response_code(404); echo 'Rol no encontrado.'; return; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Editar rol | Ecosistema Core Admin','contentView'=>'pages/roles/edit','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('role','tenants')]);
    },
    'POST /roles/{id}' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken) ? $csrfToken : null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        $id = (int) ($params['id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $service = new RoleService(new RoleRepository($pdo)); $message = $service->updateRole($id, $_POST); } catch (\Throwable) { $message = 'No se pudo guardar el rol.'; }
        header('Location: '.($message==='Rol actualizado correctamente.' ? '/roles?ok='.urlencode($message) : ($message==='Rol no encontrado.' ? '/roles?error='.urlencode($message) : '/roles/'.$id.'/edit?error='.urlencode($message))));
    },
    'POST /roles/{id}/status' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken) ? $csrfToken : null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        $id = (int) ($params['id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $service = new RoleService(new RoleRepository($pdo)); $message = $service->changeStatus($id, (string) ($_POST['status'] ?? '')); } catch (\Throwable) { $message = 'No se pudo guardar el rol.'; }
        header('Location: '.($message==='Estado actualizado correctamente.' ? '/roles?ok='.urlencode($message) : '/roles?error='.urlencode($message)));
    },


    'GET /permissions' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $statusMessage = isset($_GET['ok']) ? (string) $_GET['ok'] : null; $errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;
        try { $pdo = PdoFactory::make($config['database']); $service = new PermissionService(new PermissionRepository($pdo)); $permissions = $service->listPermissions(); } catch (\Throwable) { $permissions=[]; $errorMessage='No se pudo guardar el permiso.'; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title'=>'Permisos | Ecosistema Core Admin','contentView'=>'pages/permissions/index','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('permissions','statusMessage','errorMessage')]);
    },
    'GET /permissions/create' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        try { $pdo=PdoFactory::make($config['database']); $service=new PermissionService(new PermissionRepository($pdo)); $modules=$service->listModules(); } catch (\Throwable) { $modules=[]; }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Crear permiso | Ecosistema Core Admin','contentView'=>'pages/permissions/create','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('modules')]);
    },
    'POST /permissions' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken)?$csrfToken:null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        try { $pdo=PdoFactory::make($config['database']); $service=new PermissionService(new PermissionRepository($pdo)); $message=$service->createPermission($_POST);} catch (\Throwable) { $message='No se pudo guardar el permiso.'; }
        header('Location: '.($message==='Permiso creado correctamente.'?'/permissions?ok='.urlencode($message):'/permissions/create?error='.urlencode($message)));
    },
    'GET /permissions/{id}/edit' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $id=(int)($params['id']??0); try { $pdo=PdoFactory::make($config['database']); $service=new PermissionService(new PermissionRepository($pdo)); $permission=$service->findPermission($id); $modules=$service->listModules(); } catch (\Throwable) { $permission=null; $modules=[]; }
        if($permission===null){ http_response_code(404); echo 'Permiso no encontrado.'; return; }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Editar permiso | Ecosistema Core Admin','contentView'=>'pages/permissions/edit','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('permission','modules')]);
    },
    'POST /permissions/{id}' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken)?$csrfToken:null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        $id=(int)($params['id']??0); try { $pdo=PdoFactory::make($config['database']); $service=new PermissionService(new PermissionRepository($pdo)); $message=$service->updatePermission($id,$_POST);} catch (\Throwable) { $message='No se pudo guardar el permiso.'; }
        header('Location: '.($message==='Permiso actualizado correctamente.'?'/permissions?ok='.urlencode($message):($message==='Permiso no encontrado.'?'/permissions?error='.urlencode($message):'/permissions/'.$id.'/edit?error='.urlencode($message))));
    },
    'POST /permissions/{id}/status' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken)?$csrfToken:null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        $id=(int)($params['id']??0); try { $pdo=PdoFactory::make($config['database']); $service=new PermissionService(new PermissionRepository($pdo)); $message=$service->changeStatus($id,(string)($_POST['status']??''));} catch (\Throwable) { $message='No se pudo guardar el permiso.'; }
        header('Location: '.($message==='Estado actualizado correctamente.'?'/permissions?ok='.urlencode($message):'/permissions?error='.urlencode($message)));
    },
    'GET /roles/{id}/permissions' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $id=(int)($params['id']??0); try{ $pdo=PdoFactory::make($config['database']); $service=new RolePermissionService(new PermissionRepository($pdo)); $data=$service->getRolePermissionsScreen($id);} catch (\Throwable) { $data=['role'=>null,'permissions'=>[],'assigned'=>[]]; }
        if($data['role']===null){ http_response_code(404); echo 'Rol no encontrado.'; return; }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Permisos de rol | Ecosistema Core Admin','contentView'=>'pages/roles/permissions','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>$data]);
    },
    'POST /roles/{id}/permissions' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken)?$csrfToken:null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        $id=(int)($params['id']??0); try{ $pdo=PdoFactory::make($config['database']); $service=new RolePermissionService(new PermissionRepository($pdo)); $message=$service->replaceRolePermissions($id,(array)($_POST['permission_ids']??[])); } catch (\Throwable) { $message='No se pudo guardar el permiso.'; }
        header('Location: '.($message==='Permisos del rol actualizados correctamente.'?'/roles/'.$id.'/permissions?ok='.urlencode($message):'/roles/'.$id.'/permissions?error='.urlencode($message)));
    },

    'GET /users' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }

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
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
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
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken) ? $csrfToken : null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        try { $pdo = PdoFactory::make($config['database']); $service = new UserService(new CoreUserRepository($pdo)); $message = $service->createUser($_POST);} catch (\Throwable) { $message = 'No se pudo guardar el usuario.'; }
        header('Location: '.($message === 'Usuario creado correctamente.' ? '/users?ok='.urlencode($message) : '/users/create?error='.urlencode($message)));
    },

    'GET /users/{id}/edit' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $id = (int) ($params['id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $service = new UserService(new CoreUserRepository($pdo)); $user = $service->findUser($id); $tenants = $service->listTenants(); } catch (\Throwable) { $user = null; $tenants = []; }
        if ($user === null) { http_response_code(404); echo 'Usuario no encontrado.'; return; }
        header('Content-Type: text/html; charset=UTF-8');
        View::render('layouts.admin', ['title' => 'Editar usuario | Ecosistema Core Admin', 'contentView' => 'pages/users/edit', 'auth' => AuthSession::getAuth(), 'csrfToken' => AuthSession::getCsrfToken(), 'contentData' => compact('user','tenants')]);
    },

    'POST /users/{id}' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken) ? $csrfToken : null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        $id = (int) ($params['id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $service = new UserService(new CoreUserRepository($pdo)); $message = $service->updateUser($id, $_POST);} catch (\Throwable) { $message = 'No se pudo guardar el usuario.'; }
        header('Location: '.(($message === 'Usuario actualizado correctamente.') ? '/users?ok='.urlencode($message) : ($message === 'Usuario no encontrado.' ? '/users?error='.urlencode($message) : '/users/'.$id.'/edit?error='.urlencode($message))));
    },

    'POST /users/{id}/status' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken) ? $csrfToken : null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        $id = (int) ($params['id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $service = new UserService(new CoreUserRepository($pdo)); $message = $service->changeStatus($id, (string) ($_POST['status'] ?? '')); } catch (\Throwable) { $message = 'No se pudo guardar el usuario.'; }
        header('Location: '.($message === 'Estado actualizado correctamente.' ? '/users?ok='.urlencode($message) : '/users?error='.urlencode($message)));
    },

    'POST /users/{id}/password' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);
        if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken) ? $csrfToken : null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        $id = (int) ($params['id'] ?? 0);
        try { $pdo = PdoFactory::make($config['database']); $service = new UserService(new CoreUserRepository($pdo)); $message = $service->updatePassword($id, (string) ($_POST['password'] ?? '')); } catch (\Throwable) { $message = 'No se pudo guardar el usuario.'; }
        header('Location: '.($message === 'Contraseña actualizada correctamente.' ? '/users?ok='.urlencode($message) : '/users/'.$id.'/edit?error='.urlencode($message)));
    },


    'GET /mail' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $auth = AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0);
        $statusMessage = isset($_GET['ok']) ? (string) $_GET['ok'] : null; $errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;
        try { $pdo=PdoFactory::make($config['database']); $service=new MailService(new MailboxRepository($pdo), new MailMessageRepository($pdo)); $messages=$service->listMessages($tenantId,$userId);} catch (\Throwable) { $messages=[]; $errorMessage='Mensaje no encontrado.'; }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Mail | Ecosistema Core Admin','contentView'=>'pages/mail/index','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('messages','statusMessage','errorMessage')]);
    },
    'GET /mail/messages/{id}' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $id=(int)($params['id']??0);
        try { $pdo=PdoFactory::make($config['database']); $service=new MailService(new MailboxRepository($pdo), new MailMessageRepository($pdo)); $message=$service->findMessage($tenantId,$userId,$id);} catch (\Throwable) { $message=null; }
        if ($message===null) { http_response_code(404); }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Mail detalle | Ecosistema Core Admin','contentView'=>'pages/mail/show','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('message')]);
    },
    'GET /mail/compose' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $statusMessage = isset($_GET['ok']) ? (string) $_GET['ok'] : null; $errorMessage = isset($_GET['error']) ? (string) $_GET['error'] : null;
        try { $pdo=PdoFactory::make($config['database']); $service=new MailService(new MailboxRepository($pdo), new MailMessageRepository($pdo)); $mailboxes=$service->listActiveMailboxes($tenantId,$userId);} catch (\Throwable) { $mailboxes=[]; }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Compose | Ecosistema Core Admin','contentView'=>'pages/mail/compose','auth'=>$auth,'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('mailboxes','statusMessage','errorMessage')]);
    },
    'POST /mail/drafts' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken)?$csrfToken:null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0);
        try { $pdo=PdoFactory::make($config['database']); $service=new MailService(new MailboxRepository($pdo), new MailMessageRepository($pdo)); $message=$service->createDraft($tenantId,$userId,$_POST);} catch (\Throwable) { $message='No se pudo guardar el borrador.'; }
        header('Location: '.($message==='Borrador creado correctamente.'?'/mail?ok='.urlencode($message):'/mail/compose?error='.urlencode($message)));
    },
    'POST /mail/messages/{id}/read' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken)?$csrfToken:null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $id=(int)($params['id']??0);
        try { $pdo=PdoFactory::make($config['database']); $service=new MailService(new MailboxRepository($pdo), new MailMessageRepository($pdo)); $message=$service->updateRead($tenantId,$userId,$id);} catch (\Throwable) { $message='Mensaje no encontrado.'; }
        header('Location: /mail?'.(($message==='Mensaje actualizado correctamente.')?'ok=':'error=').urlencode($message));
    },
    'POST /mail/messages/{id}/star' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken)?$csrfToken:null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $id=(int)($params['id']??0);
        try { $pdo=PdoFactory::make($config['database']); $service=new MailService(new MailboxRepository($pdo), new MailMessageRepository($pdo)); $message=$service->updateStar($tenantId,$userId,$id);} catch (\Throwable) { $message='Mensaje no encontrado.'; }
        header('Location: /mail?'.(($message==='Mensaje actualizado correctamente.')?'ok=':'error=').urlencode($message));
    },
    'POST /mail/messages/{id}/trash' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken = $_POST['_csrf'] ?? null; if (!AuthSession::validateCsrfToken(is_string($csrfToken)?$csrfToken:null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        $auth=AuthSession::getAuth(); $tenantId=(int)($auth['tenant_id']??0); $userId=(int)($auth['user_id']??0); $id=(int)($params['id']??0);
        try { $pdo=PdoFactory::make($config['database']); $service=new MailService(new MailboxRepository($pdo), new MailMessageRepository($pdo)); $message=$service->trash($tenantId,$userId,$id);} catch (\Throwable) { $message='Mensaje no encontrado.'; }
        header('Location: /mail?'.(($message==='Mensaje enviado a papelera.')?'ok=':'error=').urlencode($message));
    },


    'GET /system/health' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $statusMessage = isset($_GET['ok']) ? (string)$_GET['ok'] : null; $errorMessage = isset($_GET['error']) ? (string)$_GET['error'] : null;
        try { $pdo=PdoFactory::make($config['database']); $service=new HealthService(new HealthRepository($pdo), new LogRepository($pdo), $pdo); $healthChecks=$service->listHealthChecks(); } catch (\Throwable) { $healthChecks=[]; $errorMessage='No se pudo ejecutar el health check.'; }
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Health | Ecosistema Core Admin','contentView'=>'pages/system/health','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('healthChecks','statusMessage','errorMessage')]);
    },
    'POST /system/health/{id}/run' => static function (array $config, array $params): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $csrfToken=$_POST['_csrf']??null; if (!AuthSession::validateCsrfToken(is_string($csrfToken)?$csrfToken:null)) { http_response_code(419); echo 'CSRF token inválido.'; return; }
        $id=(int)($params['id']??0); $message='No se pudo ejecutar el health check.';
        try { $pdo=PdoFactory::make($config['database']); $service=new HealthService(new HealthRepository($pdo), new LogRepository($pdo), $pdo); $auth=AuthSession::getAuth(); $message=$service->runHealthCheck($id, isset($auth['tenant_id'])?(int)$auth['tenant_id']:null, isset($auth['user_id'])?(int)$auth['user_id']:null, $_SERVER['REMOTE_ADDR']??null, $_SERVER['HTTP_USER_AGENT']??null);} catch (\Throwable) {}
        header('Location: /system/health?'.($message==='Health check ejecutado correctamente.'?'ok=':'error=').urlencode($message));
    },
    'GET /system/logs' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $errorMessage=null; try{$pdo=PdoFactory::make($config['database']); $logs=(new LogRepository($pdo))->listRecent(100);}catch(\Throwable){$logs=[]; $errorMessage='No se pudieron cargar los logs.';}
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Logs | Ecosistema Core Admin','contentView'=>'pages/system/logs','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('logs','errorMessage')]);
    },
    'GET /system/audit' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']); if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }
        $errorMessage=null; try{$pdo=PdoFactory::make($config['database']); $audits=(new AuditRepository($pdo))->listRecent(100);}catch(\Throwable){$audits=[]; $errorMessage='No se pudo cargar auditoría.';}
        header('Content-Type: text/html; charset=UTF-8'); View::render('layouts.admin',['title'=>'Auditoría | Ecosistema Core Admin','contentView'=>'pages/system/audit','auth'=>AuthSession::getAuth(),'csrfToken'=>AuthSession::getCsrfToken(),'contentData'=>compact('audits','errorMessage')]);
    },

    'GET /login' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);

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
            ],
        ]);
    },

    'POST /login' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);

        $csrfToken = $_POST['_csrf'] ?? null;
        if (!AuthSession::validateCsrfToken(is_string($csrfToken) ? $csrfToken : null)) {
            http_response_code(419);
            echo 'CSRF token inválido.';
            return;
        }

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

        AuthSession::setAuth($result);
        header('Location: /dashboard');
    },

    'POST /logout' => static function (array $config): void {
        AuthSession::start((string) $config['app']['session']['name'], (bool) $config['app']['session']['secure']);

        $csrfToken = $_POST['_csrf'] ?? null;
        if (!AuthSession::validateCsrfToken(is_string($csrfToken) ? $csrfToken : null)) {
            http_response_code(419);
            echo 'CSRF token inválido.';
            return;
        }

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
];
