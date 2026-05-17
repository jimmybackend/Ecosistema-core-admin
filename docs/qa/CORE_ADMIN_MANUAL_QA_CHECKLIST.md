# Core Admin — Checklist manual QA end-to-end (demo privada)

## Objetivo
Validar manualmente Core Admin para demo privada sin promover capacidades fuera de su estado real (**estable**, **read-only**, **dry-run**, **controlled por flags apagadas**), usando solo datos de prueba y sin integraciones externas productivas.

## Fuentes de verdad usadas
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

## Reglas de ejecución
- Repositorio: `jimmybackend/Ecosistema-core-admin`.
- No usar datos reales, secretos ni clientes reales.
- Mantener desactivadas flags sensibles en entorno demo.
- No activar SMTP/AWS-S3/IA/workers productivos/billing real.
- Si falta DB, registrar el caso como **Pendiente por DB** y continuar con pruebas no destructivas de navegación/guardrails.

## Perfiles sugeridos para QA
- `qa_admin`: permisos de administración (tenants/users/roles/permissions/modules + system/audit).
- `qa_operator_ro`: permisos de solo consulta para módulos read-only.
- `qa_limited`: usuario autenticado sin permisos administrativos para validar 403/denegaciones.
- `public`: sin sesión para rutas públicas controladas.

## Matriz de checklist manual

| ID | Categoría/Estado | Ruta | Precondición | Usuario/permiso | Pasos | Esperado | Evidencia |
|---|---|---|---|---|---|---|---|
| QA-01 | Auth (estable) | `GET /login` | Sesión cerrada | `public` | Abrir `/login`. | Formulario de login visible, sin error 500. | Captura pantalla login. |
| QA-02 | Auth (estable) | `POST /login` | Usuario demo válido | `public` | Ingresar credenciales válidas y enviar. | Redirige a `/dashboard`; sesión iniciada. | Captura dashboard con usuario autenticado. |
| QA-03 | Auth (estable) | `POST /login` | Usuario demo inválido/no existente | `public` | Enviar credenciales erróneas. | Error controlado de autenticación; no filtra secretos ni stacktrace. | Captura mensaje de error. |
| QA-04 | Auth (estable) | `POST /logout` | Sesión iniciada | Usuario autenticado | Ejecutar logout desde UI. | Sesión finalizada; navegación vuelve a login. | Captura confirmando logout. |
| QA-05 | Auth (estable) | `GET /dashboard` | Sin sesión | `public` | Ir directo a `/dashboard`. | Redirección a `/login` o acceso denegado controlado. | Captura redirección/denegación. |
| QA-06 | Auth (estable) | Cierre por inactividad | `SESSION_IDLE_TIMEOUT` configurado | Usuario autenticado | Esperar timeout y refrescar. | Sesión expira y requiere login nuevamente. | Registro hora inicio/expiración + captura. |
| QA-07 | Core CRUD (estable/controlled por permiso) | `GET /tenants`, `POST /tenants` | Datos demo de tenant | `qa_admin` (`tenants.view`/`tenants.manage`) | Ver listado; crear/editar tenant de prueba no destructivo (o actualizar estado en tenant demo). | Listado funcional; operación de administración responde sin romper RBAC. | Captura listado + resultado operación. |
| QA-08 | Core CRUD (estable/controlled por permiso) | `GET /users`, `POST /users/{id}/password` | Usuario demo objetivo | `qa_admin` (`users.view`/`users.manage`) | Abrir listado users; ejecutar cambio de password sobre usuario de QA. | Listado visible; cambio aplica solo a usuario demo; sin error 500. | Captura listado + mensaje éxito. |
| QA-09 | Core CRUD (estable/controlled por permiso) | `GET /roles`, `POST /roles`, `POST /roles/{id}/permissions` | Rol demo disponible | `qa_admin` (`roles.view`/`roles.manage`) | Crear/editar rol demo y asignar permiso permitido. | CRUD responde correctamente; asociación rol-permiso persistida en entorno demo. | Captura rol creado/editado. |
| QA-10 | Core CRUD (estable/controlled por permiso) | `GET /permissions`, `POST /permissions/{id}/status` | Permiso demo disponible | `qa_admin` (`permissions.view`/`permissions.manage`) | Listar permisos; activar/desactivar permiso de prueba. | Estado cambia de forma controlada y auditable. | Captura antes/después. |
| QA-11 | Core CRUD (estable/controlled por permiso) | `GET /modules`, `POST /modules/{id}/status` | Módulo demo existente | `qa_admin` (`modules.view`/`modules.manage`) | Listar módulos; cambiar estado de módulo de prueba. | UI y persistencia coherentes con operación admin. | Captura módulo actualizado. |
| QA-12 | Seguridad RBAC (estable) | Rutas core admin anteriores | Usuario sin permisos admin | `qa_limited` | Repetir acceso a `/tenants`, `/users`, `/roles`, `/permissions`, `/modules`. | Respuesta 403/controlada; no acceso a operaciones administrativas. | Captura 403/control por permiso. |
| QA-13 | Read-only | `GET /system/health`, `/system/logs`, `/system/audit`, `/audit/events` | Sesión admin | `qa_admin` con permisos system/audit | Abrir cada vista. | Vistas accesibles en consulta; sin acciones destructivas por defecto. | Capturas de cada vista. |
| QA-14 | Read-only | `GET /cloud`, `/cloud/drive/*` (metadata) | Flags cloud/s3 en `false` | `qa_operator_ro` con permisos cloud.view | Navegar vistas de archivos/carpetas/buckets/summary/access logs. | Consulta de metadata disponible; sin upload/download remoto real habilitado. | Capturas de vistas y flags actuales. |
| QA-15 | Read-only | `GET /reports/*` (funnel, lead performance, exports listado) | Sesión con reportes | `qa_operator_ro` (`reports.view`) | Abrir reportes existentes de lectura. | Datos/listados visibles sin export productivo. | Captura de cada reporte abierto. |
| QA-16 | Read-only | `GET /crm/*` (leads/followups/campaigns read views) | Sesión con CRM lectura | `qa_operator_ro` (`crm.view`) | Abrir leads, followups y vistas de campañas solo consulta. | Navegación estable en lectura; sin afirmar operación productiva total. | Captura vistas CRM. |
| QA-17 | Read-only | `GET /landing/*`, `/url/locator/*`, `/browser/analytics/*` | Sesión con permisos de lectura | `qa_operator_ro` | Navegar listados/detalles admin de landing, links/clicks y analytics. | Consultas visibles; sin cambios destructivos automáticos. | Capturas por módulo. |
| QA-18 | Dry-run | `POST/GET /workflow/*dry-run*` o simulaciones workflow | Flags dry-run del módulo según entorno | `qa_admin` (`workflow.*`) | Ejecutar dry-run de workflow/rules/events disponible. | Simulación con salida informativa; sin efectos externos/escritura real sensible. | Captura resultado dry-run. |
| QA-19 | Dry-run | `GET/POST /reports/exports/dry-run` | `ECOSISTEMA_REPORT_EXPORT_DRY_RUN` según entorno demo | `qa_admin` (`reports.manage`) | Ejecutar export dry-run. | Respuesta de simulación; no archivo productivo ni exfiltración PII real. | Captura resultado dry-run export. |
| QA-20 | Dry-run | `GET /campaigns/new/dry-run` (si aplica) | Flags campaigns write en `false` | `qa_admin` (`campaigns.manage`) | Ejecutar flujo de creación dry-run. | Simula creación sin escritura final ni side effects externos. | Captura pantalla resultado. |
| QA-21 | Dry-run | `POST /l/{slug}/forms/{id}/submit` con modo dry-run | `ECOSISTEMA_LANDING_FORM_SUBMIT_DRY_RUN=true` y write en `false` | `public` | Enviar formulario de demo controlado. | Respuesta simulada/controlada; no envío real productivo. | Captura respuesta submit dry-run. |
| QA-22 | Dry-run | `POST /ai/assist`, `/ai/leads/{id}/summary-dry-run`, `/ai/campaigns/{id}/insight-dry-run` | Flags IA apagadas por defecto | `qa_admin` (`ai.*`) | Ejecutar endpoints dry-run disponibles. | Bloqueado o simulado sin llamadas reales a proveedor externo. | Captura respuesta bloqueada/dry-run. |
| QA-23 | Controlled (flags off) | `POST /mail*` y `/mail-notifications/send*` | `MAIL_SEND_ENABLED=false`, `ECOSISTEMA_MAIL_SEND_ENABLED=false`, SMTP off | `qa_admin` | Intentar envío controlado desde UI. | No envía correo real; mensaje de bloqueo/guardrail claro. | Captura bloqueo + flags en `.env`. |
| QA-24 | Controlled (flags off) | `POST /cloud/drive/upload`, descargas remotas, signed URLs | Flags cloud/drive remotos en `false` | `qa_admin` | Intentar upload/download remoto y URL firmada. | Operación real bloqueada; sin llamadas AWS/S3 productivas. | Captura bloqueo contractual/dry-run. |
| QA-25 | Controlled (flags off) | `GET /u/{slug}` | `ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECTS=false` | `public` | Acceder a slug de prueba. | Redirect público bloqueado o respuesta controlada; sin redirección real abierta. | Captura pantalla blocked redirect. |
| QA-26 | Controlled (flags off) | `GET /l/{slug}` y submit público | `ECOSISTEMA_LANDING_PUBLIC_RENDER_ENABLED=false`, `ECOSISTEMA_LANDING_FORM_SUBMIT_ENABLED=false` | `public` | Abrir landing pública y probar submit. | Render/submit bloqueados de forma explícita y segura. | Captura pantallas de bloqueo. |
| QA-27 | Controlled (flags off) | `POST /browser/analytics/collect` | `ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_WRITE=false` | `public` técnico | Enviar evento de prueba. | Evento no se escribe en modo write-off; respuesta controlada/dry-run según configuración. | Captura request/response. |
| QA-28 | Controlled (flags off) | `POST /workflow/*/execute`, `POST /workflow/templates/install` | `ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED=false`, template write off | `qa_admin` | Intentar ejecución real/install template. | Bloqueo por flags; sin acciones externas ni escritura operacional productiva. | Captura bloqueo de ejecución. |
| QA-29 | Controlled (flags off) | `POST /reports/exports` | `ECOSISTEMA_REPORT_EXPORT_WRITE=false` | `qa_admin` | Intentar export real. | Export real bloqueado o degradado a simulación segura. | Captura bloqueo/resultado. |
| QA-30 | Controlled (flags off) | Workers/cron (no productivo) | Estado workers según docs | `qa_admin`/ops | Validar que no haya ejecución productiva no autorizada en demo. | Coherencia con estado actual documentado de workers/cron; sin jobs productivos activos. | Evidencia de revisión en bitácora QA. |

## Validación mínima técnica (no destructiva)
Ejecutar en rama del PR:

```bash
composer dump-autoload
php -l routes/web.php
php -l scripts/smoke-check.php
composer smoke
```

Si algo no corre por entorno (por ejemplo DB ausente), documentar explícitamente: **Pendiente por limitación de entorno**.

## Criterio de salida (Go / No-Go demo privada)
- **Go:** auth estable + navegación core + guardrails por flags off verificados + dry-run verificable + sin integraciones externas reales.
- **No-Go:** cualquier envío real externo no autorizado, escritura fuera de alcance en módulos controlled con flags off, o falla de auth base/dashboard.
