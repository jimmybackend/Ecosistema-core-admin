# Ecosistema Core Admin

Este repositorio contiene la aplicación operativa/admin **Ecosistema Core Admin**.

## Estado actual

Este PR corresponde a **Crear dashboard**.

## Incluye

- Login real con `POST /login` usando únicamente `core_users` (usuario activo por email + `password_verify`).
- Registro de sesión en `core_sessions` en cada login exitoso.
- Actualización de `core_users.last_login_at = NOW()` en login exitoso.
- Sesión PHP segura con nombre configurable (`SESSION_NAME`) y `session_regenerate_id(true)`.
- CSRF básico para formularios `POST /login` y `POST /logout`.
- Logout real (`POST /logout`) que revoca `core_sessions.revoked_at` y destruye sesión PHP.
- Dashboard autenticado en `GET /dashboard` con métricas mínimas de solo lectura sobre tablas core reales.
- Redirecciones base:
  - `GET /` redirige a `/login` si no hay sesión.
  - `GET /` redirige a `/dashboard` si hay sesión.
  - `GET /dashboard` requiere login y redirige a `/login` sin sesión.

## Dashboard (alcance actual)

Métricas mínimas implementadas (sin joins complejos):

- Tenant actual por `auth_tenant_id` desde `core_tenants`.
- Conteo de usuarios activos por tenant desde `core_users`.
- Conteo de módulos activos desde `core_modules`.
- Conteo de sesiones activas del usuario desde `core_sessions`.
- Lista corta de módulos activos (`LIMIT 8`) desde `core_modules`.

Tablas reales consultadas en este paso:

- `core_users`
- `core_tenants`
- `core_modules`
- `core_sessions`

## Datos de sesión PHP guardados

Sólo se guardan estos campos mínimos en `$_SESSION`:

- `auth_user_id`
- `auth_tenant_id`
- `auth_email`
- `auth_display_name`
- `auth_core_session_id`
- `csrf_token` (para protección CSRF)

No se guarda `password_hash` ni contraseñas en sesión.

## Reglas operativas

- El usuario **debe existir previamente** en `core_users`.
- La contraseña en `core_users.password_hash` debe estar generada con `password_hash()`.
- Este PR **NO crea usuarios** ni seeds.
- Este PR **NO implementa** roles/permisos (quedan para PR posterior).
- Este PR **NO implementa** CRUD de tenants o usuarios.
- Este PR **NO implementa** Mail ni Cloud.
- Este PR **NO implementa** API separada ni workers.
- Este PR **NO implementa** autorización por permisos.

## Logout

- La salida se hace por `POST /logout`.
- Si existe `auth_core_session_id`, se marca `revoked_at = NOW()` en `core_sessions`.
- Luego se destruye la sesión PHP y redirige a `/login`.
- No se eliminan registros históricos de `core_sessions`.

## Ejecución local rápida

```bash
composer dump-autoload
php -S 127.0.0.1:8000 -t public
```

Rutas:

- Home con redirect por sesión: <http://127.0.0.1:8000/>
- Dashboard autenticado: <http://127.0.0.1:8000/dashboard>
- Login real: <http://127.0.0.1:8000/login>
- Health PDO técnico: <http://127.0.0.1:8000/health/db>
