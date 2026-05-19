# CORE Admin — Demo DB Minimum Checklist

Checklist mínimo para levantar una demo local/controlada de Core Admin **sin datos reales** y sin habilitar operación productiva.

> Este documento cubre únicamente datos base de demo para validación técnica/funcional interna.

## 1) Objetivo y guardrails

- Definir tablas y registros mínimos para demo local.
- Mantener módulos `read-only`, `dry-run`, `controlled` y `roadmap` en su estado real documentado.
- Prohibido cargar datos reales de clientes, secretos o dumps productivos.
- Prohibido commitear contraseñas reales o hashes reales.

## 2) Tablas mínimas núcleo (obligatorias)

Para navegación básica del Core Admin (auth, sesión, RBAC, auditoría y catálogo base):

- `core_tenants`
- `core_users`
- `core_roles`
- `core_permissions`
- `core_role_permissions`
- `core_user_roles`
- `core_sessions`
- `core_audit`

## 3) Tablas de módulos en demo read-only (opcionales por alcance)

Agregar únicamente si el recorrido de demo va a mostrar esas vistas en modo consulta/simulación:

- **Cloud/Drive:** `cloud_files`, `cloud_folders`, `cloud_buckets`, `cloud_user_roots`.
- **Mail/Notifications (sin envío real):** `mail_messages`, `mail_mailboxes`, `mail_attachments`, `mail_smtp_accounts`, `notifications_queue`, `notifications_templates`, `url_message_templates`.
- **URL Locator:** `url_short_links`, `url_clicks`.
- **Landing:** `landing_pages`, `landing_forms`, `landing_visits`, `landing_form_submissions`, `landing_form_submission_values`.
- **Browser Analytics:** `browser_analytics_pageviews`, `browser_analytics_events`, `browser_analytics_event_params`.
- **Onboarding/System:** `onboarding_flows`, `onboarding_runs`, `onboarding_run_steps`, `onboarding_run_logs`, `system_logs`, `system_health_check_results` (y tablas `system_health_check_*` presentes en el esquema local).

> Si un módulo no se va a presentar, no es necesario poblar sus tablas para esta demo mínima.

## 4) Datos demo mínimos (sin PII real)

### 4.1 Tenant demo

- 1 tenant técnico de demo (ej. código `demo-tenant`).
- Nombre claramente ficticio (ej. `Tenant Demo Core Admin`).
- Estado activo para permitir login y navegación.

### 4.2 Usuario admin demo

- 1 usuario admin de demo asociado al tenant demo.
- Email ficticio reservado para ejemplos (ej. `admin-demo@example.com`).
- Usuario marcado activo.
- **Contraseña**: generarla localmente en cada entorno; no guardar ni commitear la contraseña en texto plano.

### 4.3 Roles demo

- 1 rol administrativo global de demo (ej. `demo_admin`).
- Opcional: 1 rol de solo lectura (ej. `demo_viewer`) para mostrar diferencia entre permisos.

### 4.4 Permisos demo

Asignar al menos permisos para recorrer panel base:

- `tenants.view`, `users.view`, `roles.view`, `permissions.view`
- `modules.view`, `system.view`, `audit.view`

Agregar permisos `*.manage` solo si el guion de demo requiere pantallas de edición/simulación controlada.

## 5) Reglas de credenciales y secretos

- No commitear contraseñas ni hashes reales de usuarios humanos.
- No commitear credenciales reales de DB/SMTP/AWS/IA.
- Mantener placeholders tipo `change-me` en `.env` y usar secretos reales solo en entorno local privado.
- En la demo, conservar en `false` todas las flags de ejecución externa/escritura sensible (mail real, S3 real, IA proveedor, workers productivos, exports con PII, etc.).

## 6) Validaciones SQL sugeridas (solo lectura)

> Ejecutar contra la base **local de demo**. Son consultas read-only de conteo y consistencia básica.

```sql
-- Núcleo mínimo presente
SELECT COUNT(*) AS tenants FROM core_tenants;
SELECT COUNT(*) AS users FROM core_users;
SELECT COUNT(*) AS roles FROM core_roles;
SELECT COUNT(*) AS permissions FROM core_permissions;
SELECT COUNT(*) AS role_permissions FROM core_role_permissions;
SELECT COUNT(*) AS user_roles FROM core_user_roles;

-- Integridad funcional mínima de demo
SELECT COUNT(*) AS active_demo_admins
FROM core_users u
WHERE u.status = 'active';

SELECT COUNT(*) AS audit_rows
FROM core_audit;
```

Consultas opcionales por módulo (si se presenta en demo):

```sql
SELECT COUNT(*) AS landing_pages FROM landing_pages;
SELECT COUNT(*) AS short_links FROM url_short_links;
SELECT COUNT(*) AS analytics_events FROM browser_analytics_events;
SELECT COUNT(*) AS drive_files FROM cloud_files;
```

## 7) Checklist operativo rápido (antes de mostrar demo)

- [ ] DB local apunta a entorno de demo (no producción).
- [ ] Existe tenant demo y usuario admin demo ficticios.
- [ ] RBAC demo cargado (`roles`, `permissions`, pivotes).
- [ ] No hay datos reales ni PII de clientes.
- [ ] Flags sensibles siguen en `false` por default seguro.
- [ ] `composer smoke` y lint de rutas/scripts en verde.

## 8) Referencias cruzadas

- Runbook de verificación local: `docs/project/CORE_ADMIN_LOCAL_VERIFICATION_RUNBOOK.md`
- Estado por módulo: `docs/project/CORE_ADMIN_MODULE_STATUS.md`
- Matriz extendida de estado: `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`
- Mapa ruta/servicio/tabla: `docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md`
- Matriz de seguridad/flags: `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`
- Defaults seguros: `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md`
- Estado actual workers/cron: `docs/ops/WORKERS_CRON_CURRENT_STATE.md`


## Actualización de ejecución real en VM controlada (2026-05-19)

- Repo actualizado y limpio en `main` (commit `836d0db`, PR #257).
- Nginx y PHP-FPM operativos (`fastcgi_pass unix:/run/php/php8.5-fpm.sock`).
- `GET /login` validado en local y público con `HTTP 200`.
- `POST /login` validado con `HTTP 302 Found` y `Location: /dashboard`.
- Dashboard confirmado visible en navegador.
- DB remota `adbbmis1_eco` autorizada por IP pública de la VM en Remote MySQL / Manage Access Hosts.
- Causa raíz del fallo inicial: `.env` ilegible para `www-data` por `chmod 600`.
- Corrección aplicada: owner deploy user + group `www-data` + `chmod 640` para `.env`.
- Pendiente obligatorio preprod/prod: rotar `DB_PASSWORD`, `APP_KEY` y `CORE_REGISTRATION_INVITE_CODE`.
- `composer schema:usage` en validación real reporta 5 incompatibilidades pendientes (`mail_messages.status`, `os_ai_proposals.id`, `os_ai_proposals.module_code`, `os_ai_proposals.entity_table`, `os_ai_proposals.entity_id`) sin bloquear login.
