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

## Módulo Permisos (básico)

Este módulo agrega gestión básica de permisos y asignación de permisos a roles.

Rutas:
- `GET /permissions`
- `GET /permissions/create`
- `POST /permissions`
- `GET /permissions/{id}/edit`
- `POST /permissions/{id}`
- `POST /permissions/{id}/status`
- `GET /roles/{id}/permissions`
- `POST /roles/{id}/permissions`

Tablas usadas:
- `core_permissions` (CRUD lógico)
- `core_role_permissions` (asignación por rol)
- `core_modules` (sólo selección/visualización de módulo)
- `core_roles` (validación/lectura básica de rol)

Notas:
- Todas las rutas del módulo requieren sesión autenticada.
- Aún **no** se implementa enforcement global de autorización fina por permiso.
- Aún **no** se asignan roles a usuarios desde este módulo.


## Módulo Módulos (básico)

Rutas protegidas por sesión (si no hay sesión redirige a `/login`):
- `GET /modules`
- `GET /modules/create`
- `POST /modules`
- `GET /modules/{id}/edit`
- `POST /modules/{id}`
- `POST /modules/{id}/status`

Este módulo usa exclusivamente `core_modules` y solo los campos reales:
- `id`, `code`, `name`, `description`, `table_prefix`, `is_billable`, `is_core`, `status`, `created_at`, `updated_at`

Consultas SQL usadas:
- `SELECT ... FROM core_modules ORDER BY id ASC LIMIT 100`
- `SELECT ... FROM core_modules WHERE id = :id LIMIT 1`
- `INSERT INTO core_modules (code, name, description, table_prefix, is_billable, is_core, status) VALUES (...)`
- `UPDATE core_modules SET code = :code, name = :name, description = :description, table_prefix = :table_prefix, is_billable = :is_billable, is_core = :is_core, status = :status, updated_at = NOW() WHERE id = :id`
- `UPDATE core_modules SET status = :status, updated_at = NOW() WHERE id = :id`

No implementado todavía:
- Enforcement global de autorización fina por permisos (pendiente para PR posterior).
- Creación automática de permisos por módulo.
- Creación de tablas internas del módulo o funcionalidades internas de Mail/Cloud/Health.


## Módulo System (health/logs/auditoría)

Rutas protegidas por sesión:
- `GET /system/health`
- `POST /system/health/{id}/run`
- `GET /system/logs`
- `GET /system/audit`

Tablas usadas (solo lectura excepto inserciones de resultados/log de ejecución manual):
- `system_health_check_definitions`
- `system_health_check_results`
- `system_logs`
- `core_audit`

Notas:
- Sin workers ni jobs programados por ahora.
- Sin checks HTTP externos (fuera de alcance de este PR).
- Logs y auditoría son de solo lectura en UI; solo se inserta log mínimo al ejecutar manualmente un health check.
- El enforcement real de permisos finos queda para un PR posterior.


## Mail mínimo (administrativo)

Rutas protegidas por sesión:
- `/mail`
- `/mail/messages/{id}`
- `/mail/compose`

Tablas usadas:
- `mail_messages`
- `mail_mailboxes`
- `mail_folders`

Este alcance mínimo solo permite listar mensajes, ver detalle y crear borradores.

**No implementado todavía:**
- envío real de correos
- SMTP
- IMAP/POP
- workers/cron
- attachments
- integración Cloud

Los adjuntos quedan para un PR posterior de Cloud/Mail.

## Cloud mínimo administrativo

Rutas:
- `/cloud`
- `/cloud/files/{id}`
- `/cloud/folders`
- `/cloud/folders/create`

Tablas usadas:
- `cloud_files`
- `cloud_folders`
- `cloud_buckets` (relación por `bucket_id`)
- `cloud_user_roots`

Alcance actual:
- Lista archivos existentes del usuario autenticado.
- Muestra detalle administrativo del archivo.
- Permite archivar y enviar a papelera (lógico) registros de `cloud_files`.
- Lista carpetas y crea carpetas lógicas en `cloud_folders`.

Pendiente (no implementado en este PR):
- No sube archivos reales.
- No descarga archivos desde S3.
- No integra AWS SDK/S3 real.
- No crea archivos físicos en servidor (`public/` o `storage/`).
- Enforcement fino de permisos queda para un PR posterior.

## Onboarding base administrativo
- Rutas: `/onboarding`, `/onboarding/flows`, `/onboarding/runs/create`, `/onboarding/runs/{id}`.
- Tablas usadas: `onboarding_flows`, `onboarding_steps`, `onboarding_runs`, `onboarding_run_steps`, `onboarding_run_logs`.
- Esta base solo crea corridas, pasos y seguimiento.
- NO ejecuta aprovisionamiento real.
- NO crea mailboxes.
- NO crea cloud roots ni carpetas cloud.
- NO crea agenda ni browser profile.
- NO envía correos de bienvenida.
- Workers/cron/jobs quedan para PR futuro.
