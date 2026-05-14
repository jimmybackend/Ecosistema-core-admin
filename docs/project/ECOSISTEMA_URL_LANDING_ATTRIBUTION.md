# Ecosistema URL→Landing Attribution Dry-run (PR #123)

Se agrega un puente de atribución **read-only/dry-run** entre `url_clicks`, `landing_visits` y `browser_analytics_sessions`.

## Rutas
- `GET /attribution/url-landing/dry-run`
- `POST /attribution/url-landing/dry-run`

## Seguridad y alcance
- Usa `tenant_id` de sesión/auth context (no request).
- Requiere login + permiso existente `modules.view`.
- No ejecuta `INSERT/UPDATE/DELETE`.
- No expone valores completos sensibles (`ip_address`, `user_agent`, `referer`, `clicked_url`, `full_url`).
- `ECOSISTEMA_ATTRIBUTION_ENABLED=false` por defecto.
- `ECOSISTEMA_ATTRIBUTION_WRITE=false` por defecto.

## Reglas de matching (potencial)
1. Cargar click (`url_clicks.id`) para tenant actual.
2. Buscar visitas por igualdad de: `short_link_id`, `landing_page_id`, `campaign_id`, `visitor_uuid`.
3. Buscar sesiones analytics por `visitor_uuid`.
4. Mostrar conteos y previews seguras.

## Fuente canónica
Se implementa contra columnas reales de `adbbmis1_eco`. Si faltaran datos clave, el flujo queda bloqueado con `blocked_reason` y no inventa estructura.
