# Ecosistema Message Preview Dry-Run

PR #107 agrega preview controlado para `notifications_templates` y `url_message_templates`.

## Rutas
- `GET /mail-notifications/templates/{id}/preview-dry-run`
- `POST /mail-notifications/templates/{id}/preview-dry-run`
- `GET /mail-notifications/url-message-templates/{id}/preview-dry-run`
- `POST /mail-notifications/url-message-templates/{id}/preview-dry-run`

## Garantías de seguridad
- `mode=dry-run`
- `preview_generated=true`
- `send_executed=false`
- `queue_created=false`
- `smtp_connection=false`
- No se insertan registros en `notifications_queue` ni `mail_messages`.
- No se usan `mail()`, `sendmail`, `curl` o conexiones SMTP.
- `tenant_id` siempre se toma desde sesión (`auth_tenant_id`), nunca desde request.

## Variables
- Se aceptan únicamente variables permitidas por `variables_json` y placeholders presentes (`{{variable}}`).
- Los valores se sanitizan (`strip_tags`, compactación de espacios y truncado seguro).
- Variables no permitidas se descartan y se reportan en `warnings`.
