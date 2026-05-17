# ECOSISTEMA — Manual QA End-to-End (demo + seguridad)

## Objetivo
Checklist manual para validar que el sistema está listo para demo en entorno local/VM, sin datos reales y con defaults seguros por flags.

## Alcance y reglas de ejecución
- Repositorio: `jimmybackend/Ecosistema-core-admin`.
- No ejecutar pruebas destructivas.
- No activar SMTP/S3/IA real por defecto.
- Ejecutar siempre con usuario de prueba y tenant de prueba.
- Si no hay conexión DB, registrar cada caso como **pendiente por DB**.

---

## Formato de evidencia por prueba
En cada prueba registrar:
- **Precondición**
- **Ruta**
- **Usuario/permiso requerido**
- **Resultado esperado**
- **Resultado si falta DB**
- **Riesgo si falla**
- **Captura opcional/manual**

---

## 1) Login
- **Precondición:** usuario demo válido, sesión limpia.
- **Ruta:** `GET /login`, `POST /login`.
- **Usuario/permiso requerido:** usuario activo.
- **Resultado esperado:** login exitoso redirige a `/dashboard`; login inválido muestra error sin filtrar secretos.
- **Resultado si falta DB:** login no autentica; dejar “no verificado por falta de conexión DB”.
- **Riesgo si falla:** demo bloqueada desde el inicio.
- **Captura opcional/manual:** formulario login y redirección.

## 2) Dashboard
- **Precondición:** sesión autenticada.
- **Ruta:** `GET /dashboard`.
- **Usuario/permiso requerido:** usuario autenticado.
- **Resultado esperado:** dashboard carga sin error 500.
- **Resultado si falta DB:** vista parcial o error controlado; marcar pendiente DB.
- **Riesgo si falla:** no se puede mostrar estado general de plataforma.
- **Captura opcional/manual:** widgets principales.

## 3) Tenants
- **Precondición:** sesión admin.
- **Ruta:** `GET /tenants`.
- **Usuario/permiso requerido:** permisos de administración de tenants.
- **Resultado esperado:** listado visible y navegación estable.
- **Resultado si falta DB:** listado vacío/error controlado; pendiente DB.
- **Riesgo si falla:** aislamiento multi-tenant no demostrable.
- **Captura opcional/manual:** listado tenants.

## 4) Users
- **Precondición:** sesión admin.
- **Ruta:** `GET /users`.
- **Usuario/permiso requerido:** permisos de usuarios.
- **Resultado esperado:** listado/consulta de usuarios sin exponer datos sensibles.
- **Resultado si falta DB:** pendiente DB.
- **Riesgo si falla:** gobierno de acceso no demostrable.
- **Captura opcional/manual:** tabla users.

## 5) Roles
- **Precondición:** sesión admin.
- **Ruta:** `GET /roles`.
- **Usuario/permiso requerido:** permisos de roles.
- **Resultado esperado:** catálogo de roles visible.
- **Resultado si falta DB:** pendiente DB.
- **Riesgo si falla:** no se puede explicar modelo RBAC.
- **Captura opcional/manual:** listado roles.

## 6) Permissions
- **Precondición:** sesión admin.
- **Ruta:** `GET /permissions`.
- **Usuario/permiso requerido:** permisos de seguridad.
- **Resultado esperado:** matriz/listado de permisos accesible.
- **Resultado si falta DB:** pendiente DB.
- **Riesgo si falla:** auditoría de autorización incompleta.
- **Captura opcional/manual:** vista de permisos.

## 7) Modules
- **Precondición:** sesión admin.
- **Ruta:** `GET /modules`.
- **Usuario/permiso requerido:** permisos de módulos.
- **Resultado esperado:** módulos visibles con su estado.
- **Resultado si falta DB:** pendiente DB.
- **Riesgo si falla:** no se puede demostrar estado real por módulo.
- **Captura opcional/manual:** listado módulos.

## 8) Audit
- **Precondición:** sesión admin, acciones previas en sistema.
- **Ruta:** `GET /audit`.
- **Usuario/permiso requerido:** permisos de auditoría.
- **Resultado esperado:** eventos auditables visibles.
- **Resultado si falta DB:** pendiente DB.
- **Riesgo si falla:** sin trazabilidad operativa.
- **Captura opcional/manual:** tabla de auditoría.

## 9) System health
- **Precondición:** entorno levantado.
- **Ruta:** `GET /system/health`.
- **Usuario/permiso requerido:** usuario autenticado con acceso a health.
- **Resultado esperado:** estado de salud visible (incluyendo señal de DB cuando aplique).
- **Resultado si falta DB:** indicador degradado o error controlado, documentado.
- **Riesgo si falla:** no hay señal de readiness operativa.
- **Captura opcional/manual:** panel de health.

## 10) Drive read-only
- **Precondición:** sesión autenticada.
- **Ruta:** `GET /cloud/drive`, `GET /cloud/drive/files/{id}/versions`.
- **Usuario/permiso requerido:** acceso drive lectura.
- **Resultado esperado:** inventario/consulta en modo lectura; sin exponer `s3_key`.
- **Resultado si falta DB:** pendiente DB.
- **Riesgo si falla:** exposición de metadata sensible o demo incompleta.
- **Captura opcional/manual:** listado y detalle de archivo.

## 11) Drive upload/download bloqueados por flags
- **Precondición:** flags de upload/download remotos en `false`.
- **Ruta:** `POST /cloud/drive/upload`, `GET /cloud/drive/download-contract`, `GET /cloud/drive/files/{id}/download-contract`.
- **Usuario/permiso requerido:** acceso drive.
- **Resultado esperado:** flujo bloqueado/contractual seguro; sin operación remota real.
- **Resultado si falta DB:** bloqueo por flags sigue verificable; registrar pendiente DB para validación completa.
- **Riesgo si falla:** escrituras/descargas remotas no autorizadas.
- **Captura opcional/manual:** pantalla de bloqueo y reason code.

## 12) Mail preview/send bloqueado por flags
- **Precondición:** `MAIL_SEND_ENABLED=false`, `MAIL_ALLOW_TEST_SEND=false`.
- **Ruta:** `POST /mail/messages/{id}/prepare-send`, `POST /mail-notifications/{id}/send`.
- **Usuario/permiso requerido:** permiso de mail.
- **Resultado esperado:** preview disponible; envío real bloqueado en modo seguro.
- **Resultado si falta DB:** preview/listado puede quedar pendiente DB; bloqueo por flags debe persistir.
- **Riesgo si falla:** envío accidental de correos.
- **Captura opcional/manual:** preview + bloqueo de send.

## 13) URL Locator links/clicks/redirect dry-run
- **Precondición:** sesión admin para vistas internas; flags públicas según entorno demo (por defecto bloqueado).
- **Ruta:** `GET /url/locator/*`, `GET /u/{slug}`.
- **Usuario/permiso requerido:** permisos URL Locator.
- **Resultado esperado:** links/clicks visibles en admin; redirect público bloqueado si flags off o controlado si on.
- **Resultado si falta DB:** pending DB para inventario/clicks; bloqueo público puede verificarse.
- **Riesgo si falla:** riesgo de open redirect o trazabilidad incompleta.
- **Captura opcional/manual:** detalle de link y pantalla blocked redirect.

## 14) Landing read-only/public render bloqueado por flags
- **Precondición:** flags públicas de landing en `false`.
- **Ruta:** `GET /landing/*`, `GET /l/{slug}`, `POST /l/{slug}/forms/{id}/submit`.
- **Usuario/permiso requerido:** admin para vistas; público para ruta `/l/*`.
- **Resultado esperado:** admin read-only usable; render/submit público bloqueado por flags.
- **Resultado si falta DB:** admin y render pueden quedar pendientes DB; bloqueo por flags verificable.
- **Riesgo si falla:** exposición pública no autorizada / ingesta de PII.
- **Captura opcional/manual:** bloqueo de render/submit.

## 15) Browser Analytics dashboard/read-only
- **Precondición:** sesión autenticada.
- **Ruta:** `GET /browser/analytics`.
- **Usuario/permiso requerido:** permisos analytics lectura.
- **Resultado esperado:** dashboard y consultas read-only visibles.
- **Resultado si falta DB:** pendiente DB.
- **Riesgo si falla:** no hay evidencia de métricas demo.
- **Captura opcional/manual:** tablero analytics.

## 16) CRM leads/read-only
- **Precondición:** sesión autenticada.
- **Ruta:** `GET /crm/leads`, `GET /crm/leads/{id}`.
- **Usuario/permiso requerido:** permisos CRM lectura.
- **Resultado esperado:** listado/detalle visibles en modo seguro.
- **Resultado si falta DB:** pendiente DB.
- **Riesgo si falla:** pipeline comercial no demostrable.
- **Captura opcional/manual:** lista de leads.

## 17) Campaign cockpit
- **Precondición:** sesión autenticada.
- **Ruta:** `GET /campaigns/cockpit`.
- **Usuario/permiso requerido:** permisos campaigns.
- **Resultado esperado:** cockpit carga; sin acciones destructivas por defecto.
- **Resultado si falta DB:** pendiente DB.
- **Riesgo si falla:** narrativa de operación comercial incompleta.
- **Captura opcional/manual:** vista cockpit.

## 18) Workflow runs/templates/dry-run
- **Precondición:** flags de ejecución/escritura en `false` por defecto.
- **Ruta:** `GET /workflow/runs`, `GET /workflow/templates`, `POST /workflow/runs/{id}/execute`, `POST /workflow/templates/install`.
- **Usuario/permiso requerido:** permisos workflow.
- **Resultado esperado:** vistas de runs/templates accesibles; execute/install bloqueado o dry-run seguro.
- **Resultado si falta DB:** vistas pendientes DB; bloqueo por flags verificable.
- **Riesgo si falla:** side effects masivos por ejecución no controlada.
- **Captura opcional/manual:** estado dry-run/bloqueo.

## 19) Reports funnel/lead performance/export dry-run
- **Precondición:** flags de export write en `false`.
- **Ruta:** `GET /reports/funnel`, `GET /reports/lead-performance`, `POST /reports/*/export`.
- **Usuario/permiso requerido:** permisos reportes.
- **Resultado esperado:** reportes consultables; exportación bloqueada/dry-run.
- **Resultado si falta DB:** consultas pendientes DB; bloqueo de export verificable.
- **Riesgo si falla:** exfiltración de datos por export no controlado.
- **Captura opcional/manual:** panel + resultado export dry-run.

## 20) Security permissions audit/rate-limit dry-run
- **Precondición:** sesión admin seguridad; flags de enforcement en `false` por defecto.
- **Ruta:** `GET /security/permissions-audit`, `POST /security/rate-limit/enforce`.
- **Usuario/permiso requerido:** permisos de seguridad.
- **Resultado esperado:** auditoría visible; rate-limit en modo dry-run/blocked sin escrituras sensibles con flags off.
- **Resultado si falta DB:** auditoría pendiente DB; dry-run bloqueado verificable.
- **Riesgo si falla:** endurecimiento de seguridad no demostrable.
- **Captura opcional/manual:** resultado audit/rate-limit.

## 21) AI dry-run / assist blocked unless flags enabled
- **Precondición:** `ECOSISTEMA_AI_ENABLED=false`, `ECOSISTEMA_AI_PROVIDER_ENABLED=false`, `ECOSISTEMA_AI_WRITE_PROPOSALS=false`.
- **Ruta:** `POST /ai/assist`.
- **Usuario/permiso requerido:** permisos AI.
- **Resultado esperado:** asistencia bloqueada o dry-run seguro; sin proveedor externo ni escrituras.
- **Resultado si falta DB:** puede quedar pendiente DB para trazas; bloqueo por flags verificable.
- **Riesgo si falla:** fuga de datos a proveedor externo o escrituras no autorizadas.
- **Captura opcional/manual:** respuesta bloqueada/dry-run.

---

## Qué probar antes de demo
1. Login + dashboard + navegación básica de módulos core.
2. Validar que flags sensibles siguen en `false` en `.env`/`.env.example`.
3. Verificar bloqueos seguros en Drive remoto, Mail send, Landing público, URL redirect público, AI provider/write.
4. Ejecutar smoke y sintaxis mínima (ver sección de validaciones técnicas).
5. Registrar pendientes explícitos si DB no está disponible.

## Qué no probar en demo
- Pruebas de carga/estrés.
- Flujos destructivos de escritura masiva.
- Integraciones productivas reales con SMTP/S3/IA externa.
- Escenarios con datos personales reales.

## Qué no activar
- `MAIL_SEND_ENABLED=true`
- `MAIL_ALLOW_TEST_SEND=true`
- `S3_DRIVE_ALLOW_REMOTE_UPLOADS=true`
- `S3_DRIVE_ALLOW_REMOTE_DOWNLOADS=true`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS=true`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS=true`
- `ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECTS=true` (salvo ventana controlada y acotada)
- `ECOSISTEMA_LANDING_PUBLIC_RENDER_ENABLED=true`
- `ECOSISTEMA_LANDING_FORM_SUBMIT_ENABLED=true`
- `ECOSISTEMA_LANDING_FORM_FILE_UPLOADS=true`
- `ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_WRITE=true`
- `ECOSISTEMA_CRM_SUBMISSION_TO_LEAD_WRITE=true`
- `ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED=true`
- `ECOSISTEMA_WORKFLOW_TEMPLATE_INSTALL_WRITE=true`
- `ECOSISTEMA_REPORT_EXPORT_WRITE=true`
- `ECOSISTEMA_AI_PROVIDER_ENABLED=true`
- `ECOSISTEMA_AI_WRITE_PROPOSALS=true`

---

## Validaciones técnicas mínimas (no destructivas)
Ejecutar:
- `composer dump-autoload`
- `php -l routes/web.php`
- `php -l scripts/smoke-check.php`
- `composer smoke`

Si alguna verificación depende de DB y no hay conexión, registrar:
> **No verificado por falta de conexión DB**

y mantener el pendiente en este checklist.
