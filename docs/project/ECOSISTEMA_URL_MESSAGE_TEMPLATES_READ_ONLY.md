# URL Message Templates (read-only)

## Objetivo
Exponer en Core Admin una vista administrativa **read-only** de `url_message_templates` y `url_message_attachments` asociadas a URL Locator, usando `tenant_id` de sesión y base canónica `adbbmis1_eco`.

## Rutas
- `GET /mail-notifications/url-message-templates`
- `GET /mail-notifications/url-message-templates/{id}`

## Seguridad aplicada
- Tenant aislado desde sesión autenticada (`auth_tenant_id`), sin `tenant_id` por request.
- Consultas PDO con prepared statements.
- Sin `INSERT/UPDATE/DELETE` sobre `url_message_*`.
- Sin exposición de `file_path`, `s3_key`, correos completos ni `body_html` crudo.

## DTO read-only
- Template DTO: previews y banderas `*_present`, con `*_exposed=false` para campos sensibles.
- Attachment DTO: metadata segura (`filename`, `display_name`, `mime_type`, `size_bytes`, contadores) y banderas de presencia de `file_path/s3_key`.
- `mode=read-only` en respuesta de servicio.

## Adapter
`EcosistemaMailNotificationsAdapter` mantiene:
- `url_message_templates_read=true`
- `send_write=false`
- `smtp_connection=false`
- `db_writes=false`

## Notas
- No se implementan envíos de correo.
- No se implementan descargas de adjuntos.
- No se integra AWS/S3 en este alcance.
