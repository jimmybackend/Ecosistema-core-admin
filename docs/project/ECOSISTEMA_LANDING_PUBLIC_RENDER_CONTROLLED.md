# ECOSISTEMA_LANDING_PUBLIC_RENDER_CONTROLLED

Render público controlado de landing por slug (`GET /l/{slug}`), con default apagado por flag y sin escrituras en DB.

## Flag
- `.env.example`: `ECOSISTEMA_LANDING_PUBLIC_RENDER_ENABLED=false`

## Reglas aplicadas
- Tenant aplicado por contexto/configuración del sistema (no se acepta `tenant_id` en request).
- Sólo renderiza cuando:
  1. Flag habilitada.
  2. Existe `landing_pages` para tenant actual y `slug` solicitado.
  3. `landing_pages.status = published`.
  4. Ventana de publicación válida (`published_at`/`unpublished_at`).
  5. Existe versión publicada en `landing_page_versions`.
- Sin tracking (`landing_visits`) y sin procesamiento de formularios.
- No se exponen `layout_json`, `custom_css`, `custom_js`, `settings_json`, `content_json`, ni HTML custom crudo.

## Respuesta bloqueada
- Si no cumple reglas, muestra vista genérica `public-page-blocked` sin detalles sensibles.

## Diferencias con legacy
- Referencias legacy (mailit-click) sólo informativas.
- Fuente canónica respetada: `adbbmis1_eco`.
