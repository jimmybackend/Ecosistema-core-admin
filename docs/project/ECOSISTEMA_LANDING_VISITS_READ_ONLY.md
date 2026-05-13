# ECOSISTEMA LANDING VISITS READ ONLY

Este módulo agrega consulta administrativa **read-only** para la tabla canónica `landing_visits` (DB `adbbmis1_eco`).

## Alcance
- Lectura de visitas existentes por tenant.
- Resumen por país, dispositivo y campaña.
- Vista general y vista por landing page.

## Privacidad aplicada
- No se expone `ip_address` completo; sólo `ip_address_preview`.
- No se exponen `visitor_uuid`/`session_uuid`, sólo flags `*_present`.
- No se expone `user_agent`, `referer`, `full_url` completos; sólo previews.
- No se exponen coordenadas completas (`coordinates_exposed=false`).

## Garantías de seguridad
- Sólo `SELECT` con PDO prepared statements.
- Filtro estricto por `tenant_id` desde sesión.
- No registra visitas nuevas.
- No implementa tracker público.
- No renderiza landing pública.
