# ECOSISTEMA_LANDING_PUBLIC_RENDER_DRY_RUN

## Objetivo
Simular el render público de una landing page desde admin sin publicar, sin registrar visitas y sin procesar formularios.

## Ruta
- `GET /landing/pages/{id}/public-render-dry-run`

## Seguridad aplicada
- Requiere sesión autenticada y permiso existente `modules.view`.
- Aplica `tenant_id` desde sesión (`auth_tenant_id`), no desde request.
- Requiere `id` entero positivo.
- No ejecuta INSERT/UPDATE/DELETE.
- No registra `landing_visits`.
- No procesa formularios.
- No expone payloads sensibles completos (`template_json`, `layout_json`, `custom_css`, `custom_js`, `settings_json`, `content_json`, `public_url`).

## Flag
- `.env.example`: `ECOSISTEMA_LANDING_PUBLIC_RENDER_DRY_RUN=false`
- Si la flag está apagada, la ruta responde en estado bloqueado seguro.

## Reglas de bloqueo
1. Flag deshabilitada.
2. Landing inexistente para tenant actual.
3. `landing_pages.status` distinto de `published`.
4. No existe versión publicada en `landing_page_versions`.

## Salida
DTO seguro con:
- `allowed`, `reason`, `mode=dry-run`
- `db_write=false`, `visit_tracking_write=false`, `form_processing_write=false`
- metadata segura de page, versión publicada y bloques.
