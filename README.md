# Ecosistema Core Admin

Este repositorio contiene la aplicación operativa/admin **Ecosistema Core Admin**.

## Estado actual

Este PR corresponde a **Crear tenants**.

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


## Tenants (alcance actual)

Rutas protegidas por sesión autenticada (sin roles/permisos aún):

- `GET /tenants`
- `GET /tenants/create`
- `POST /tenants`
- `GET /tenants/{id}/edit`
- `POST /tenants/{id}`
- `POST /tenants/{id}/status`

Este módulo usa exclusivamente la tabla real `core_tenants` y estos campos:

- `id`, `name`, `slug`, `legal_name`, `domain`, `plan_code`, `status`, `timezone`, `locale`, `created_at`, `updated_at`

Pendiente para PR posterior:

- Autorización fina por roles/permisos.
- Creación automática de usuarios, roles o módulos al crear tenant (no implementado).

## Usuarios (alcance actual)

Rutas protegidas por sesión autenticada (sin roles/permisos aún):

- `GET /users`
- `GET /users/create`
- `POST /users`
- `GET /users/{id}/edit`
- `POST /users/{id}`
- `POST /users/{id}/status`
- `POST /users/{id}/password`

Este módulo usa la tabla real `core_users` con los campos:

- `id`, `tenant_id`, `email`, `username`, `password_hash`, `display_name`, `first_name`, `last_name`, `phone`, `user_type`, `status`, `email_verified_at`, `phone_verified_at`, `last_login_at`, `created_at`, `updated_at`

Uso limitado de `core_tenants`:

- Solo para seleccionar tenant en formularios y mostrar nombre/slug del tenant en listado de usuarios.

No implementado todavía:

- Roles/permisos y autorización fina.
- Integraciones de correo (Mail), Cloud, invitaciones o recuperación de contraseña.
- API JSON o workers.

## Módulo Roles (básico)

Rutas protegidas por sesión (si no hay sesión redirige a `/login`):
- `GET /roles`
- `GET /roles/create`
- `POST /roles`
- `GET /roles/{id}/edit`
- `POST /roles/{id}`
- `POST /roles/{id}/status`

Este módulo usa únicamente la tabla real `core_roles` para CRUD básico y cambio lógico de estado.

Uso de `core_tenants`: sólo para selección y visualización del tenant en listado/formularios.

Pendiente para PR posterior:
- Permisos y autorización fina por rol.
- Asignación de permisos a roles.
- Asignación de usuarios a roles.
