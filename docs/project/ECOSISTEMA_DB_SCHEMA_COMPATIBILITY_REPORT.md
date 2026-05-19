# ECOSISTEMA DB Schema Compatibility Report (Core Admin vs BD canónica)

Fecha: 2026-05-16  
Repositorio evaluado: `jimmybackend/Ecosistema-core-admin`  
Base canónica esperada: `adbbmis1_eco`

## Alcance y método

- Se revisaron consultas SQL en `app/` y controles en `scripts/smoke-check.php`.
- Se cruzó contra documentación técnica ya existente en `docs/project/*_SCHEMA_INVENTORY.md`.
- Este reporte **no ejecuta escrituras**; sólo inventario estático de compatibilidad.
- El repositorio `jimmybackend/Ecosistema-bd` no está presente en este entorno, por lo que la verificación contra DDL canónico queda parcial y con columnas en estado `needs manual DB check` cuando aplique.

## Matriz de compatibilidad

| Repository PHP | Tabla usada | Columnas usadas (resumen) | Fuente SQL/documental | Estado |
|---|---|---|---|---|
| `app/Core/Auth/AuthorizationRepository.php` | `core_user_roles` | `tenant_id`, `user_id`, `role_id` | JOIN SQL en repo + smoke-check permisos | confirmed |
| `app/Core/Auth/AuthorizationRepository.php` | `core_role_permissions` | `tenant_id`, `role_id`, `permission_id` | JOIN SQL en repo + smoke-check permisos | confirmed |
| `app/Core/Auth/AuthorizationRepository.php` | `core_permissions` | `id`, `module_id`, `code`, `name` | filtro por `p.code` + docs de permisos | confirmed |
| `app/Core/Auth/AuthorizationRepository.php` | `core_roles` | `id`, `tenant_id`, `name`, `slug` | JOIN por rol activo | confirmed |
| `app/Core/Modules/ModuleRepository.php` | `core_modules` | `id`, `code`, `name`, `is_active` | SELECT de módulos + matrix módulos | confirmed |
| `app/Core/Session/*` | `core_sessions` | `id`, `tenant_id`, `user_id`, `session_token_hash`, `expires_at` | repositorio de sesión + smoke-check | confirmed |
| `app/Support/helpers.php` (audit) | `core_audit` | `tenant_id`, `user_id`, `action`, `entity_type`, `entity_id`, `old_values`, `new_values` | helper audit + rutas con `auditLog(...)` | confirmed |
| `app/Core/Cloud/*` | `cloud_files` | `id`, `tenant_id`, `user_id`, `original_name`, `stored_name`, `s3_key`, `status` | SQL repos cloud + `CLOUD_S3_DATABASE_MAPPING` | confirmed |
| `app/Core/Cloud/*` | `cloud_folders` | `id`, `tenant_id`, `user_id`, `name`, `status` | SQL repos cloud + docs cloud | confirmed |
| `app/Core/Mail/*` | `mail_messages` | `tenant_id`, `mailbox_id`, `folder_id`, `user_id`, `subject`, `body_*`, flags `is_*` | repos mail + smoke-check send | confirmed |
| `app/Core/Mail/*` | `mail_mailboxes`, `mail_folders`, `mail_delivery_logs`, `mail_smtp_accounts` | columnas de direccionamiento, estado, SMTP | repos mail + inventario mail/notifs | needs manual DB check |
| `app/Core/MailNotifications/*` | `notifications_queue` | `tenant_id`, `user_id`, `channel_id`, `template_id`, `status`, `created_at` | repos send/queue + docs notificaciones | confirmed |
| `app/Core/MailNotifications/*` | `notifications_templates`, `notifications_channels` | `subject`, `body`, `variables_json`, `is_active` | repos templates/send dry-run | confirmed |
| `app/Core/Url/*` | `url_short_links`, `url_clicks`, `url_message_templates`, `url_message_attachments`, `url_message_access_logs` | ids multi-tenant, tracking y metadata | SQL repos + `ECOSISTEMA_URL_LOCATOR_SCHEMA_INVENTORY.md` | confirmed |
| `app/Core/Landing/*` | `landing_pages`, `landing_forms`, `landing_form_submissions`, `landing_visits` | identificadores, slug, campaign_id, telemetry | repos + `ECOSISTEMA_LANDING_SCHEMA_INVENTORY.md` | confirmed |
| `app/Core/BrowserAnalytics/*` | `browser_analytics_events`, `browser_analytics_pageviews`, `browser_analytics_attribution` | `tenant_id`, `campaign_id`, eventos/atribución | repos + `ECOSISTEMA_BROWSER_ANALYTICS_SCHEMA_INVENTORY.md` | confirmed |
| `app/Core/Crm/*` | `crm_leads`, `crm_campaign_leads`, `crm_marketing_campaigns`, `crm_tasks`, `crm_customer_followups` | campos de lead/campaña/seguimiento | repos + `ECOSISTEMA_CRM_*` docs | confirmed |
| `app/Core/Workflow/*` | `workflow_rules`, `workflow_actions`, `workflow_runs`, `workflow_run_logs`, `module_workflow_links` | reglas, acciones, ejecuciones y logs | repos + `ECOSISTEMA_WORKFLOW_SCHEMA_INVENTORY.md` | confirmed |
| `app/Core/Reports/*` | `reports_*` (familia) | n/a (no SQL directa estable en este corte) | no evidencia concluyente en código actual | needs manual DB check |
| `app/Core/Security/*` | `security_*` (familia) | n/a (no SQL directa estable en este corte) | no evidencia concluyente en código actual | needs manual DB check |
| `app/Core/Ai/*` | `os_ai_proposals`, `os_human_responses`, `os_knowledge_packs`, `chat_threads`, `chat_messages` | proposals/responses/context chat | repos AI + `ECOSISTEMA_AI_ASSISTANCE_SCHEMA_INVENTORY.md` + smoke-check | confirmed |
| UI/DTO (sin SQL) | aliases como `landing_title`, `landing_slug`, `email_preview` | sólo payload de UI/servicio | servicios dry-run/controlled | alias/ui-only |

## Hallazgos clave

1. El nombre canónico `adbbmis1_eco` está alineado en documentación y validaciones internas de smoke-check.
2. `tenant_id` se consume desde sesión en rutas controladas para operaciones críticas; no se observan patrones de “tenant_id directo desde request” en los flujos auditados de permisos/CRM/URL/workflow.
3. Familias `reports_*` y `security_*` requieren contraste manual con DDL del repo `Ecosistema-bd` porque no se encontró evidencia SQL suficiente aquí para confirmar columnas canónicas.

## Checklist manual pendiente (cuando haya conexión DB + repo BD)

- [ ] Ejecutar `scripts/schema-compatibility-check.php` contra una instancia real de `adbbmis1_eco`.
- [ ] Contrastar tablas `reports_*` del código con DDL del repo `Ecosistema-bd`.
- [ ] Contrastar tablas `security_*` del código con DDL del repo `Ecosistema-bd`.
- [ ] Adjuntar evidencia de `INFORMATION_SCHEMA.COLUMNS` para tablas marcadas `needs manual DB check`.

## Notas de seguridad/validación

- Verificación en modo read-only (sin INSERT/UPDATE/DELETE durante comprobación).
- No se agregaron migraciones ni seeds.
- No se inventaron rutas ni módulos nuevos; se documentó sólo lo presente en el código/documentación actual.

## Actualización de validación real en VM (2026-05-19)

Ejecución real de `composer schema:usage` conectada a DB remota detectó **5 incompatibilidades pendientes**:
- `mail_messages.status`
- `os_ai_proposals.id`
- `os_ai_proposals.module_code`
- `os_ai_proposals.entity_table`
- `os_ai_proposals.entity_id`

Estas incompatibilidades deben tratarse como pendiente técnico de alineación código/checker vs DB real (sin crear migraciones a ciegas ni inventar columnas).

Importante: el login funcional en VM (`POST /login => 302` a `/dashboard`) **no** quedó bloqueado por este punto.
