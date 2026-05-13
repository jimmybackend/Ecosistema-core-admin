# Ecosistema URL Locator — Link Detail Read-only (PR #83)

Se agrega la vista administrativa segura de detalle de short link:

- Ruta protegida: `GET /url/locator/links/{id}`.
- Requiere sesión autenticada y permiso `modules.view` (igual al listado).
- Tenant siempre derivado de sesión/contexto.
- Si no existe o no pertenece al tenant, responde 404 seguro.

## Superficie mostrada
- Metadata segura del short link.
- Idiomas relacionados (`url_short_link_languages`) con banderas de presencia.
- Smart settings (`url_smart_link_settings`) sin exponer `custom_css/custom_js`.
- Resumen de message templates (`url_message_templates`) sin exponer `body_html`.
- Resumen de ad interstitials (`url_ad_interstitials`) sin exponer `ad_html` ni `media_s3_key`.

## Seguridad
- Sólo lecturas `SELECT` con PDO prepared statements.
- Sin INSERT/UPDATE/DELETE.
- Sin redirección pública.
- Sin tracking de clicks.
- Sin exponer hashes ni URLs sensibles completas.
