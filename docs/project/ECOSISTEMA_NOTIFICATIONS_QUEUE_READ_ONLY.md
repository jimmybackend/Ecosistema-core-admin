# ECOSISTEMA Notifications Queue Read-only

## Objetivo
Exponer la cola `notifications_queue` en modo **read-only**, respetando tenant de sesión.

## Endpoints
- `GET /mail-notifications/queue`
- `GET /mail-notifications/queue/{id}`

## Seguridad y alcance
- Sin writes (`INSERT/UPDATE/DELETE`) sobre `notifications_queue`.
- Sin procesamiento, reintentos ni envíos.
- `payload_json` no se expone en crudo (`payload_json_exposed=false`).
- `body` y `fail_reason` sólo se muestran como preview.
- `tenant_id` se toma desde sesión (`auth_tenant_id`).

## Adapter
`EcosistemaMailNotificationsAdapter` mantiene:
- `queue_read=true`
- `send_write=false`
- `db_writes=false`

## Smoke-check
Se validan archivos/rutas/docs, no mutaciones SQL en cola y no exposición de `payload_json` crudo en vistas.
