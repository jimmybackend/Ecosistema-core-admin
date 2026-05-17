# CORE_ADMIN_CONTROLLED_ROUTES_PERMISSION_AUDIT

Fecha de auditoría: 2026-05-17  
Alcance: rutas `POST` y rutas `controlled` en `routes/web.php`, verificando sesión, permiso/autorización, CSRF, tenant scoping y flags según documentación técnica vigente.

## Fuentes revisadas

- `README.md`
- `docs/project/CORE_ADMIN_MODULE_STATUS.md`
- `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`
- `docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md`
- `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`
- `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md`
- `docs/ops/WORKERS_CRON_CURRENT_STATE.md`
- `routes/web.php`
- `scripts/smoke-check.php`
- `.env.example`

## Resultado ejecutivo

- Las rutas administrativas write/controladas auditadas (tenants, users, roles, permissions, modules, mail send, report export, campaign creation, CRM writes, workflow execution, drive uploads/downloads administrativos y rate-limit enforce) requieren sesión autenticada y control de permiso (`requirePermission` o `AuthorizationService->can(...)`).
- En rutas `POST` administrativas auditadas se valida CSRF con `ensureValidCsrfToken(...)`.
- Las operaciones sensibles externas o con write crítico están sujetas a flags de seguridad/capacidad en servicios o configuración (`*_ENABLED`, modos `dry-run`/`controlled`).
- No se identificaron writes sensibles que tomen tenant libre de request; predominan `auth_tenant_id` de sesión y/o validaciones por entidad con tenant scoping.
- **No se requirieron cambios de código** en esta auditoría.

## Inventario de rutas POST/controladas (resumen)

### Core RBAC/Admin writes
- Tenants: `POST /tenants`, `POST /tenants/{id}`, `POST /tenants/{id}/status`.
- Modules: `POST /modules`, `POST /modules/{id}`, `POST /modules/{id}/status`.
- Roles/Permissions: `POST /roles`, `POST /roles/{id}`, `POST /roles/{id}/permissions`, `POST /permissions`, `POST /permissions/{id}`.
- Users: `POST /users`, `POST /users/{id}`, `POST /users/{id}/status`, `POST /users/{id}/password`, `POST /users/{id}/roles`.

### Mail y notificaciones
- Mail compose/send lifecycle: rutas `POST /mail/...` (drafts, read, attachments, prepare-send, send).
- Notificaciones: `POST /mail-notifications/send-dry-run`, `POST /mail-notifications/send`, previews dry-run.

### Reporting / campaign / CRM / workflow / security
- Report export: rutas `POST` de export dry-run/controlado.
- Campaign creation: rutas `POST` de creación dry-run/controlled.
- CRM writes: lead status y submission-to-lead controlado/dry-run.
- Workflow: `POST /workflow/templates/{key}/install-dry-run`, `POST /workflow/templates/{key}/install`, `POST /workflow/rules/{id}/dry-run`, `POST /workflow/dry-run`, `POST /workflow/rules/{id}/execute`, `POST /workflow/events/execute`.
- Security rate-limit: `POST /security/rate-limit/dry-run`, `POST /security/rate-limit/enforce`.

### Drive / AI / operaciones externas
- Drive: uploads/downloads/control endpoints bajo modo controlado con contracts/adapters y flags ambientales.
- AI assist: rutas/controladores de asistencia sujetas a permisos del módulo y flags de habilitación.

## Verificación de controles

## 1) Sesión autenticada
Patrón observado en rutas administrativas/controladas:
- `startAuthSession($config);`
- `if (!AuthSession::isAuthenticated()) { header('Location: /login'); return; }`

Cumple para rutas write críticas revisadas.

## 2) Permisos / autorización
Patrones observados:
- `requirePermission($config, '<module>.manage|view')`
- `AuthorizationService(...)->can($userId, $tenantId, 'workflow.manage|view' ...)`

Cumple para writes críticos (manage) y ejecuciones controladas.

## 3) CSRF en POST
Patrón observado en rutas POST administrativas:
- `$csrfToken = $_POST['_csrf'] ?? null;`
- `ensureValidCsrfToken($config, $csrfToken)`

Cumple en las rutas POST sensibles auditadas.

## 4) Tenant scoping
Patrón observado:
- Tenant obtenido de sesión: `(int)($auth['auth_tenant_id'] ?? 0)`.
- Operaciones por entidad con filtros `tenant_id` en repositorios/queries.

No se detectó dependencia de tenant libre desde request para writes sensibles.

## 5) Flags en operaciones sensibles/externas
Conforme matriz y defaults seguros:
- Capacidades externas y writes controlados dependen de flags `*_ENABLED` y/o modos `dry-run`/`controlled`.
- El comportamiento por defecto se mantiene seguro (`disabled`/`read-only`/`dry-run` hasta habilitación explícita).

## Hallazgos

- Sin hallazgos críticos de rutas write sin sesión/permiso/CSRF en el alcance auditado.
- Sin cambios requeridos en `CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md` (sin cambios de permisos ni rutas en esta auditoría).

## Validación mínima ejecutada

- `composer dump-autoload`
- `php -l routes/web.php`
- `php -l scripts/smoke-check.php`
- `composer smoke`

Resultado: validaciones ejecutadas y sin errores de sintaxis/regresión en el alcance.
