<?php

declare(strict_types=1);

use App\Core\Auth\AuthService;
use App\Core\Auth\AuthSession;
use App\Core\Auth\SessionRepository;
use App\Core\Auth\UserRepository;
use App\Core\Dashboard\DashboardService;
use App\Core\Tenants\TenantRepository;
use App\Core\Tenants\TenantService;
use App\Core\Database\PdoFactory;
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
