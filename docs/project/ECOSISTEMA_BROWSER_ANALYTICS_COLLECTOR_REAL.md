# ECOSISTEMA Browser Analytics Collector Real

## Objetivo
Habilitar escritura mínima real en `browser_analytics_sessions`, `browser_analytics_pageviews` y `browser_analytics_events` sólo cuando flags activas.

## Flags
- `ECOSISTEMA_BROWSER_ANALYTICS_ENABLED=true`
- `ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_WRITE=true`
- Privacidad:
  - `ECOSISTEMA_BROWSER_ANALYTICS_COLLECT_IP=false` por defecto.
  - `ECOSISTEMA_BROWSER_ANALYTICS_COLLECT_USER_AGENT=false` por defecto.
  - `ECOSISTEMA_BROWSER_ANALYTICS_COOKIE_ENABLED=false` por defecto.

## Seguridad
- Tenant NO se recibe desde request; se resuelve por configuración (`ECOSISTEMA_BROWSER_ANALYTICS_TENANT_ID`).
- Payload con contrato cerrado (`session`, `pageview`, `event`) y rechazo de claves arbitrarias.
- URLs se validan con `FILTER_VALIDATE_URL`.
- `meta_json` y `metadata_json` se sanitizan y recortan.
- Respuestas JSON seguras sin exponer errores internos.
