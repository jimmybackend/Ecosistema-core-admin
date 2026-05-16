# PR164 — Alineación de flujo marketing (estado real)

## Alcance revisado
- CRM (`app/Core/Crm/*`), Campaigns (`app/Core/Campaigns/*`), Landing (`app/Core/Landing/*`), URL Locator (`app/Core/UrlLocator/*`), rutas públicas/admin en `routes/web.php`, vistas y banderas en `.env.example`.

## Flujo operativo real (campaña → short link → landing → submission → lead)
1. **Campaign creation (write controlado)**
   - Endpoint `POST /campaigns` protegido por sesión, CSRF y permisos.
   - El write depende de `ECOSISTEMA_CAMPAIGN_CREATION_WRITE` (default `false`).
   - Si el flag está en `false`, responde bloqueado con `feature_disabled`.

2. **Short link público (`/u/{slug}`)**
   - Redirección pública controlada por `ECOSISTEMA_URL_LOCATOR_ENABLED` + `ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECTS`.
   - Tenant público definido por configuración (`ECOSISTEMA_URL_LOCATOR_PUBLIC_TENANT_ID`), no por input libre.
   - Tracking separado por `ECOSISTEMA_URL_LOCATOR_TRACKING_ENABLED` y flags de captura (`COLLECT_IP`, `COLLECT_USER_AGENT`).

3. **Landing public render (`/l/{slug}`)**
   - Render público bloqueado por defecto con `ECOSISTEMA_LANDING_PUBLIC_RENDER_ENABLED=false`.
   - Tenant público se toma de configuración segura (`APP_DEFAULT_TENANT_ID`), no de request.

4. **Landing form submit (`POST /l/{slug}/forms/{id}/submit`)**
   - Submit público bloqueado por defecto con `ECOSISTEMA_LANDING_FORM_SUBMIT_ENABLED=false`.
   - Upload de archivos separado y bloqueado por defecto con `ECOSISTEMA_LANDING_FORM_FILE_UPLOADS=false`.
   - El submit guarda submission controlada y deja explícito `crm_lead_write=false`.

5. **Submission → Lead (CRM)**
   - La conversión de submission a lead se mantiene en flujo controlado por flags CRM write/dry-run, fuera del submit público.

## Tenant safety
- En rutas admin, el tenant proviene de `AuthSession` (`auth_tenant_id`).
- En rutas públicas de landing/url-locator, el tenant proviene de config/env cerrada.
- No se detectó tenant proveniente de query/body del request para este flujo.

## PII y exposición
- Listados CRM y Landing ya trabajan con previews/masking para email/teléfono.
- En esta alineación se endurece `contact_name` en listados de submissions (ahora masked preview).
- No se expone `raw_data_json`, contenido completo de values, ni IP completa.

## Compatibilidad de schema en repositories
Validado contra documentación canónica del proyecto (inventarios de schema y docs operativas):
- Campaigns: uso consistente con `crm_campaigns` y campos esperados de creación.
- URL Locator: uso consistente con `url_short_links`, `url_short_link_languages`, `url_clicks`.
- Landing: uso consistente con `landing_pages`, `landing_page_versions`, `landing_page_blocks`, `landing_forms`, `landing_form_fields`, `landing_submissions`, `landing_submission_values`.
- CRM: integración con leads/submissions sin exigir write automático desde submit público.

### Pendiente documentado
- Confirmar en ambiente productivo que todas las columnas opcionales de geolocalización (`country`, `region`, `city`, `latitude`, `longitude`) permanecen disponibles en `landing_submissions` en todos los tenants históricos.
