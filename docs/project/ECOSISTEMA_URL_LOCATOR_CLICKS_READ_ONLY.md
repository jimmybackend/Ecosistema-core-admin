# Ecosistema URL Locator — Clicks Read-only (PR #84)

## Alcance
Se habilita consulta administrativa **read-only** de clicks existentes en URL Locator, sin tracking write, sin redirects y sin incremento de `click_count`.

## Fuente canónica
- DB canónica: `adbbmis1_eco`.
- Tabla principal: `url_clicks`.
- Relación usada: `url_short_links` para `slug/title`.
- Tablas auxiliares (`url_language_redirect_logs`, `url_message_access_logs`, `url_ad_impressions`, `url_ad_clicks`) solo quedan como referencia de resumen futuro.

## Campos mostrados (seguros)
- `clicked_at`, `short_link_slug`, `detected_language`, `selected_language`.
- Geografía parcial: `country`, `region`, `city`.
- Device/browser/os.
- Indicadores y preview: `ip_address_present/ip_address_preview`, `user_agent_preview`, `referer_preview`, `clicked_url_preview`.

## Privacidad aplicada
- `visitor_uuid` no expuesto (`visitor_uuid_present` + `visitor_uuid_exposed=false`).
- `ip_address` enmascarada (`ip_address_preview`), no completa.
- `user_agent`, `referer`, `clicked_url` solo truncados.
- Coordenadas marcadas como presencia (`coordinates_present`) sin exponer valor.

## Garantías de no escritura
- Repository de clicks usa solo `SELECT` con PDO prepared statements.
- Rutas nuevas son `GET` protegidas por login + permiso de lectura URL Locator (`modules.view`, patrón actual).
- `EcosistemaUrlLocatorAdapter` mantiene:
  - `clicks_read => true`
  - `click_tracking_write => false`
  - `public_redirects => false`

## Rutas nuevas
- `GET /url/locator/clicks`
- `GET /url/locator/links/{id}/clicks`

## Notas
- Si no hay datos, la UI muestra estado vacío seguro.
- Este PR no crea migraciones, seeds, tablas ni columnas.
