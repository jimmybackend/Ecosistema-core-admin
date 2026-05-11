# Ecosistema Core Admin

Este repositorio contiene la aplicación operativa/admin **Ecosistema Core Admin**.

## Estado actual

Este PR corresponde a **Conectar login real con `core_users`**.

## Incluye

- Login real con `POST /login` usando únicamente `core_users` (usuario activo por email + `password_verify`).
- Registro de sesión en `core_sessions` en cada login exitoso.
- Actualización de `core_users.last_login_at = NOW()` en login exitoso.
- Sesión PHP segura con nombre configurable (`SESSION_NAME`) y `session_regenerate_id(true)`.
- CSRF básico para formularios `POST /login` y `POST /logout`.
- Logout real (`POST /logout`) que revoca `core_sessions.revoked_at` y destruye sesión PHP.
- Protección mínima de `/`: redirige a `/login` si no hay sesión.

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
- Este PR **NO implementa** remember-me persistente.

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

- Home protegido: <http://127.0.0.1:8000/>
- Login real: <http://127.0.0.1:8000/login>
- Health PDO técnico: <http://127.0.0.1:8000/health/db>
