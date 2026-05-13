# Ecosistema URL Locator — Read-only Links (PR #82)

Este PR agrega UI administrativa en modo **read-only** para short links de URL Locator, tomando `adbbmis1_eco` como fuente canónica (continuación de PR #81).

## Tablas usadas
- `url_short_links` (principal).
- `landing_pages` (título seguro si hay relación).
- `crm_marketing_campaigns` (nombre seguro si hay relación).
- `core_users` (display_name/email seguro del creador).

## Qué se muestra
- Resumen por `status` y `smart_type`.
- Listado con metadatos operativos (id, slug, title, status, smart_type, campaña, landing, expiración, clicks).
- Banderas de presencia para campos sensibles (`target_url_present`, `access_token_hash_present`).

## Qué se oculta
- No se expone `target_url` completo.
- No se expone `original_url_after_ads` completo.
- No se expone `access_token_hash`.
- No se muestran UTM crudos; sólo bandera `utm_present`.

## Comportamiento de seguridad
- Sólo `SELECT` con PDO/prepared statements.
- Tenant aplicado desde sesión/contexto; no se acepta `tenant_id` por request.
- No crea links, no redirige público, no registra clicks y no escribe DB.

## Relación con PR #81
- PR #81 inventarió el esquema canónico.
- PR #82 materializa la primera superficie administrativa read-only de short links sobre ese inventario.
