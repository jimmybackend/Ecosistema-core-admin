# ECOSISTEMA Mail & Notifications — Schema Inventory (PR #104)

## Propósito
Documentar el esquema canónico real del bloque Mail/Notifications y de plantillas de URL message antes de habilitar implementación funcional adicional en `Ecosistema-core-admin`.

## Fuentes revisadas
1. `jimmybackend/Ecosistema-core-admin` (README, docs, rutas y `scripts/smoke-check.php`).
2. `jimmybackend/Ecosistema-bd` como contraste documental (sin modificarlo).
3. `jimmybackend/mailit-click` sólo como referencia funcional legacy, cuando está disponible.
4. Catálogo/dump real `adbbmis1_eco` como **fuente canónica** de tablas y columnas.

## Regla de precedencia canónica
Cuando exista discrepancia entre documentación histórica, legacy (`mailit-click`) y SQL de referencia, prevalece la estructura real observada en `adbbmis1_eco`.

## Tabla `mail_messages`
Columnas canónicas:
`id`, `tenant_id`, `mailbox_id`, `folder_id`, `user_id`, `campaign_id`,
`source_module`, `source_table`, `source_id`,
`message_uuid`, `mailbox_message_no`, `mailbox_message_code`, `mailbox_direction_no`, `mailbox_direction_code`,
`thread_uuid`, `external_provider`, `external_message_id`, `in_reply_to_message_id`,
`direction`, `mail_scope`,
`from_address`, `to_addresses`, `cc_addresses`, `bcc_addresses`,
`subject`, `body_text`, `body_html`, `raw_headers`,
`has_attachments`, `is_read`, `read_at`, `read_count`,
`is_starred`, `is_draft`, `is_spam`, `is_deleted`, `spam_score`,
`received_at`, `sent_at`, `created_at`, `updated_at`.

## Tabla `mail_attachments`
Columnas canónicas:
`id`, `tenant_id`, `message_id`, `cloud_file_id`, `original_filename`, `content_id`, `disposition`,
`mime_type`, `size_bytes`, `open_count`, `download_count`, `created_at`.

## Tabla `mail_delivery_logs`
Columnas canónicas:
`id`, `tenant_id`, `message_id`, `smtp_account_id`, `provider`, `provider_message_id`,
`status`, `response_code`, `response_message`, `attempted_at`, `delivered_at`.

## Tabla `mail_smtp_accounts`
Columnas canónicas:
`id`, `tenant_id`, `mailbox_id`, `created_by_user_id`, `name`, `email_address`,
`host_in`, `host_out`, `port_in`, `port_out`, `ssl_in`, `ssl_out`,
`username`, `password_encrypted`,
`max_daily_email`, `enable_limit`, `available_to_everyone`,
`status`, `last_error`, `created_at`, `updated_at`.

## Tabla `mail_identities`
Tabla canónica esperada para identidades de envío/representación de remitente por tenant.

> Nota: en este PR se inventaría su existencia dentro del bloque `mail_*`; la implementación funcional futura debe verificar columnas exactas directamente contra `adbbmis1_eco` antes de codificar consultas.

## Tablas de estructura mailbox/folders
- `mail_mailboxes`
- `mail_folders`

Estas tablas son parte del contexto canónico de organización de mensajes y deben mantenerse alineadas al tenant activo por sesión.

## Tabla `notifications_queue`
Columnas canónicas:
`id`, `tenant_id`, `user_id`, `channel_id`, `template_id`,
`module_code`, `entity_table`, `entity_id`,
`title`, `body`, `payload_json`, `status`,
`scheduled_at`, `sent_at`, `failed_at`, `fail_reason`, `created_at`.

## Tabla `notifications_templates`
Columnas canónicas:
`id`, `tenant_id`, `channel_id`, `code`, `name`, `subject`, `body`, `variables_json`,
`is_active`, `created_at`, `updated_at`.

## Tablas `notifications_channels` y `notifications_preferences`
Tablas canónicas esperadas para:
- catálogo de canales (
`notifications_channels`),
- preferencias de notificación por usuario/tenant (`notifications_preferences`).

> Nota: columnas exactas deben reconfirmarse en `adbbmis1_eco` al implementar repositorios/servicios en PRs funcionales.

## Tabla `url_message_templates`
Columnas canónicas:
`id`, `tenant_id`, `short_link_id`, `campaign_id`, `landing_page_id`, `created_by_user_id`,
`template_name`, `subject`, `from_name`, `from_email`, `reply_to_email`,
`header_html`, `body_html`, `footer_html`, `plain_text`,
`language_code`, `status`, `view_count`, `unique_view_count`,
`created_at`, `updated_at`.

## Tabla `url_message_attachments`
Columnas canónicas:
`id`, `tenant_id`, `message_template_id`, `short_link_id`, `uploaded_by_user_id`,
`filename`, `display_name`, `file_path`, `s3_key`, `mime_type`, `size_bytes`,
`sort_order`, `is_downloadable`, `requires_token`, `open_count`, `download_count`, `created_at`.

## Tabla `url_message_access_logs`
Tabla canónica esperada para telemetría de acceso a mensajes URL (aperturas/consultas por enlace, contexto de canal y trazabilidad por tenant).

## Tabla `url_attachment_access_logs`
Tabla canónica esperada para telemetría de acceso/descarga de adjuntos URL message.

## Riesgos y datos sensibles
Campos de mayor cuidado operativo y de privacidad:
- SMTP/infra: `password_encrypted`, `username`, `host_in`, `host_out`, `last_error`.
- Mensajería/encabezados: `from_address`, `to_addresses`, `cc_addresses`, `bcc_addresses`, `body_text`, `body_html`, `raw_headers`.
- Entrega/proveedor: `response_message`.
- Notificaciones: `payload_json`, `variables_json`.
- Plantillas URL: `header_html`, `body_html`, `footer_html`, `plain_text`.
- Archivos: `file_path`, `s3_key`.

Lineamientos de seguridad para PRs funcionales:
- Aislamiento estricto por `tenant_id` usando sesión/contexto (no request).
- Consultas con PDO + prepared statements.
- Evitar exposición de PII/contenido sensible en listados administrativos.
- Evitar imprimir JSON crudo o secretos en vistas/logs.

## Roadmap recomendado PR #105–#110
- **PR #105**: Read-only de `mail_messages` + `mail_attachments` con sanitización de destinatarios y cuerpos.
- **PR #106**: Read-only de `mail_delivery_logs` + `mail_smtp_accounts` + `mail_identities` con redacción de secretos.
- **PR #107**: Read-only de `notifications_queue` + `notifications_templates` + `notifications_channels` + `notifications_preferences`.
- **PR #108**: Read-only de `url_message_templates` + `url_message_attachments` con protección de `file_path`/`s3_key`.
- **PR #109**: Read-only analítico de `url_message_access_logs` + `url_attachment_access_logs` (sin tracking write nuevo).
- **PR #110**: contrato técnico consolidado Mail/Notifications/URL Message para futura ejecución controlada (sin envíos reales ni colas activas).

## Alcance fuera de este PR
- No se implementan rutas funcionales nuevas.
- No se crean migraciones, seeds, tablas ni columnas.
- No se agregan escrituras SQL (`INSERT/UPDATE/DELETE`) sobre `mail_*`, `notifications_*` o `url_message_*`.
- No se habilita envío de correos ni procesamiento de cola.

## SMTP por usuario/mailbox: administrado vs propio
- SMTP global por `.env` se mantiene como fallback controlado.
- SMTP por mailbox/usuario se persiste en `mail_smtp_accounts`.
- `password_encrypted` se cifra y nunca se muestra en UI ni logs.
- `core_users.password_hash` no se reutiliza para SMTP.
- El correo administrado `username+id@dominio` requiere provisión real en servidor SMTP externo/cPanel.

## SMTP por usuario/mailbox: administrado vs propio
- SMTP global por `.env` se mantiene como fallback controlado.
- SMTP por mailbox/usuario se persiste en `mail_smtp_accounts`.
- `password_encrypted` se cifra y nunca se muestra en UI ni logs.
- `core_users.password_hash` no se reutiliza para SMTP.
- El correo administrado `username+id@dominio` requiere provisión real en servidor SMTP externo/cPanel.

- Update 2026-05-19: `mail_smtp_accounts` ahora es editable desde UI controlada (`/mail/smtp-accounts*`) solo para usuarios autenticados con `mail.manage`; no se insertan datos por PR, password SMTP cifrada en `password_encrypted` (independiente del password del panel) y envío real sigue bloqueado por `MAIL_SEND_ENABLED` + `MAIL_ALLOW_TEST_SEND` en `false`.
