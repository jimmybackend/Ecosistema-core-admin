# ECOSISTEMA Browser Analytics Collector Dry-Run

## Objetivo
Habilitar simulación de collector para Browser Analytics sin escrituras en base de datos y usando tenant/usuario de sesión autenticada.

## Rutas
- `GET /browser/analytics/collector-dry-run`: formulario interno admin para simular payload.
- `POST /browser/analytics/collector-dry-run`: valida payload y devuelve DTO de simulación.

## Reglas de seguridad
- No acepta `tenant_id` ni `user_id` del request (se ignoran con warning).
- No ejecuta `INSERT`, `UPDATE` ni `DELETE` sobre `browser_analytics_*`.
- `ip_address` y `user_agent` se exponen en forma enmascarada.
- Endpoint bloqueado cuando flags `ECOSISTEMA_BROWSER_ANALYTICS_ENABLED` o `ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_DRY_RUN` están en `false`.

## DTO de respuesta
- `mode=dry-run`
- `collector_write=false`
- `would_create_session`
- `would_create_pageview`
- `would_create_event`
- `validation_status`
- `warnings`
- `sanitized_payload`
