# Ecosistema Notification Templates Read-only

## Objetivo
Exponer en Core Admin un panel **read-only** para `notifications_templates` y su canal asociado (`notifications_channels`) usando `tenant_id` desde sesión.

## Alcance
- Lectura de listado y detalle de plantillas.
- Join con canal para mostrar `channel_code` y `channel_name`.
- Sin envíos, sin cola, sin conexión SMTP y sin escrituras.

## Rutas
- `GET /mail-notifications`
- `GET /mail-notifications/templates`
- `GET /mail-notifications/templates/{id}`

## Seguridad de datos
- No se expone `body` completo; sólo `body_preview` y `body_present`.
- No se expone `variables_json` crudo; sólo `variables_json_present`.
- Tenant aplicado desde `auth_tenant_id` de sesión.

## Capacidades
- `notification_templates_read=true`
- `url_message_templates_read=false`
- `queue_read=false`
- `preview_dry_run=false`
- `send_dry_run=false`
- `send_write=false`
- `smtp_connection=false`
- `db_writes=false`
- `mode=read-only`
