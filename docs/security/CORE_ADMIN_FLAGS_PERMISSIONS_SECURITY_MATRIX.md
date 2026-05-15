# CORE ADMIN — Flags, permisos y matriz de seguridad operativa

Objetivo: centralizar una vista auditable de módulos sensibles en Core Admin, indicando qué controla su activación, qué permisos/controles HTTP aplican y qué riesgos existen al habilitarlos en producción.

## Alcance y fuentes

- `.env.example` (defaults y toggles operativos).
- `config/app.php`, `config/cloud.php`, `config/mail.php`, `config/ecosistema_drive.php`, `config/url_locator.php` (defaults efectivos en runtime).
- `routes/web.php` (permisos de rutas, uso de CSRF y extracción de `tenant_id` desde sesión).
- `scripts/smoke-check.php` (cobertura mínima de existencia/consistencia documental y técnica).

> **Nota de auditoría:** este documento no cambia lógica ni flags; sólo describe estado actual y riesgos.

## Leyenda

- **Escritura**: si el módulo puede producir cambios persistentes o efectos externos (DB, SMTP, S3, integraciones).
- **CSRF requerido**: aplica a rutas POST administrativas con sesión; rutas públicas sin sesión no aplican token CSRF.
- **Tenant desde sesión**:
  - **Sí (admin)**: la operación usa `AuthSession` (`auth_tenant_id`/`auth_user_id`).
  - **No (público/default)**: usa tenant público/default por config/env para endpoints públicos.

## Matriz prioritaria

| Módulo | Flags principales | Default esperado | ¿Permite escritura? | Permiso requerido | ¿CSRF? | ¿Tenant desde sesión? | Datos sensibles que NO deben exponerse | Riesgo si se activa en producción |
|---|---|---|---|---|---|---|---|---|
| Drive/S3 | `CLOUD_S3_ENABLED`, `CLOUD_ALLOW_UPLOADS`, `CLOUD_ALLOW_DOWNLOADS`, `ECOSISTEMA_DRIVE_ENABLED`, `ECOSISTEMA_DRIVE_AWS_ENABLED`, `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS`, `ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS`, `ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS`, `ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS` | Todos en `false` | Sí (uploads/downloads remotos, signed URLs) cuando se habilitan flags | Admin UI: `modules.view`/`modules.manage` según ruta | Sí en POST admin; no aplica en accesos públicos no autenticados | Sí en UI admin; no para flujos públicos/contractuales | `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `ECOSISTEMA_DRIVE_AWS_*`, tokens de sesión, rutas de objetos con query firmada | Exfiltración de archivos, abuso de costos S3, acceso no autorizado por URL firmada mal gobernada |
| Mail/SMTP | `MAIL_SEND_ENABLED`, `MAIL_ALLOW_TEST_SEND`, `ECOSISTEMA_MAIL_SEND_ENABLED`, `ECOSISTEMA_SMTP_ENABLED` | `false` | Sí (envío real) cuando flags habilitados | `mail.view` (lectura), `mail.manage` (escritura/envío) | Sí para POST (`/mail/*`, `/mail-notifications/*`) | Sí (rutas administrativas con `startAuthSession`) | `MAIL_USERNAME`, `MAIL_PASSWORD`, contenido de mensajes/adjuntos, direcciones email | Envío no autorizado, fuga de PII por correo, spam/reputación/domino |
| Landing público | `ECOSISTEMA_LANDING_PUBLIC_RENDER_ENABLED`, `ECOSISTEMA_LANDING_FORM_SUBMIT_ENABLED`, `ECOSISTEMA_LANDING_FORM_FILE_UPLOADS` | `false` | Sí para submissions si `FORM_SUBMIT_ENABLED=true` | Público (sin `requirePermission`) para `GET /l/{slug}` y `POST /l/{slug}/forms/{id}/submit` | No en endpoint público de submit | **No** (usa tenant default/config en ruta pública) | PII de formularios (nombre, email, teléfono, mensaje), IP y user-agent | Ingesta no controlada, spam/bot traffic, recolección de PII sin controles operativos |
| URL redirect público | `ECOSISTEMA_URL_LOCATOR_ENABLED`, `ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECTS`, `ECOSISTEMA_URL_LOCATOR_TRACKING_ENABLED`, `ECOSISTEMA_URL_LOCATOR_COLLECT_IP`, `ECOSISTEMA_URL_LOCATOR_COLLECT_USER_AGENT`, `ECOSISTEMA_URL_LOCATOR_LANGUAGE_REDIRECTS` | Enabled/redirect/tracking/IP en `false`; `COLLECT_USER_AGENT=true` | Escritura indirecta de tracking/log si habilitado tracking | Público en `GET /u/{slug}`; admin usa `modules.view/manage` | No para redirect público; sí para POST admin de edición | Público: no (tenant público/config); admin: sí | IP, user-agent, referer, slug mapping interno | Open redirect/tracking abuse, privacidad (telemetría) y correlación de navegación |
| Browser Analytics collector | `ECOSISTEMA_BROWSER_ANALYTICS_ENABLED`, `ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_DRY_RUN`, `ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_WRITE`, `ECOSISTEMA_BROWSER_ANALYTICS_COLLECT_IP`, `ECOSISTEMA_BROWSER_ANALYTICS_COLLECT_USER_AGENT` | `false` | Sí si collector write se habilita | Admin de consulta: `modules.view`; collector depende de ruta pública/controlador | CSRF en POST admin; collector público típicamente no | Panel admin: sí; collector público: no/según implementación endpoint | IP, user-agent, eventos de navegación, identificadores de sesión de analítica | Recolección excesiva de PII/metadatos y riesgo regulatorio de tracking |
| CRM writes | `ECOSISTEMA_CRM_ENABLED`, `ECOSISTEMA_CRM_SUBMISSION_TO_LEAD_WRITE`, `ECOSISTEMA_CRM_FOLLOWUP_TASK_WRITE`, `ECOSISTEMA_CRM_LEAD_STATUS_WRITE` | `false` | Sí | En UI de módulo: permisos de módulo (`modules.view/manage`) + reglas internas del servicio | Sí en POST admin | Sí en admin; puede recibir entrada de canales públicos integrados | Datos de lead (contacto, estado, notas internas) | Escritura de leads falsa/maliciosa, contaminación de pipeline comercial |
| Campaign creation | `ECOSISTEMA_CAMPAIGN_CREATION_DRY_RUN`, `ECOSISTEMA_CAMPAIGN_CREATION_WRITE`, `ECOSISTEMA_CAMPAIGN_CREATE_LANDING_DRAFT`, `ECOSISTEMA_CAMPAIGN_CREATE_SHORT_LINK` | `false` | Sí si `*_WRITE=true` | Módulo administrativo (lectura/escritura por permisos de módulo) | Sí en POST admin | Sí | Segmentos, presupuesto, metadata campaña, enlaces de tracking | Campañas no autorizadas, errores de segmentación, fuga de estrategia comercial |
| Workflow execution | `ECOSISTEMA_WORKFLOW_ENABLED`, `ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED`, `ECOSISTEMA_WORKFLOW_ACTION_*`, `ECOSISTEMA_WORKFLOW_TEMPLATE_INSTALL_WRITE` | `false` | Sí (acciones con side effects) | Módulo admin por permisos | Sí en POST admin | Sí | Payloads de acciones, endpoints webhook, datos de negocio en contexto | Automatizaciones destructivas o envío masivo accidental |
| Reports export | `ECOSISTEMA_REPORT_EXPORT_DRY_RUN`, `ECOSISTEMA_REPORT_EXPORT_WRITE`, `ECOSISTEMA_REPORT_EXPORT_INCLUDE_PII` | `false` | Sí si `*_WRITE=true` | Módulo reports (permisos admin) | Sí en POST admin | Sí | Exportables con PII (emails, teléfonos, rendimiento individual) | Exfiltración de dataset y fuga de información sensible |
| Rate limit enforcement | `ECOSISTEMA_RATE_LIMIT_ENABLED`, `ECOSISTEMA_RATE_LIMIT_DRY_RUN`, `ECOSISTEMA_RATE_LIMIT_WRITE_BLOCKS` | `false` | Sí para persistir bloqueos (`WRITE_BLOCKS`) | Config operativa interna (no sólo UI) | N/A (policy transversal) | Depende del contexto de evaluación (login/público/admin) | IPs bloqueadas, patrones de abuso, metadata de intentos | Bloqueos agresivos (falsos positivos) o protección insuficiente (falsos negativos) |
| AI provider / write proposals | `ECOSISTEMA_AI_ENABLED`, `ECOSISTEMA_AI_PROVIDER_ENABLED`, `ECOSISTEMA_AI_WRITE_PROPOSALS`, `ECOSISTEMA_AI_*_DRY_RUN` | `false` | Sí cuando escribe propuestas/recomendaciones | Endpoint admin (`POST /ai/assist`) bajo permisos del módulo | Sí en POST admin autenticado | Sí | Prompts con PII, respuestas con datos sensibles, claves/provider config | Fuga de datos a proveedor externo, alucinaciones con impacto operativo |

## Hallazgos de contradicción / pendientes

1. `ECOSISTEMA_URL_LOCATOR_COLLECT_USER_AGENT` está en `true` en `.env.example`; aunque no habilita por sí mismo redirects/tracking, sí supone recolección de UA si el flujo queda activo. Se deja como **pendiente de revisión de privacidad** para decidir default más conservador (`false`) o justificación explícita.
2. En rutas públicas (`/l/{slug}`, `/l/{slug}/forms/{id}/submit`) el tenant se toma de default config/env en vez de sesión; esto es esperado por diseño público, pero debe quedar explícito en threat model y hardening.

## Recomendaciones operativas rápidas

- Mantener `*_ENABLED=false`, `*_WRITE=false` y `*_ALLOW_REMOTE_*=false` hasta completar pruebas de seguridad, observabilidad y runbook.
- Para cualquier habilitación en producción: registrar change request, ventana de activación, rollback y evidencia de smoke + pruebas funcionales por módulo.
- Si se habilita export/reporting con PII, activar minimización de campos y controles de retención antes del go-live.
