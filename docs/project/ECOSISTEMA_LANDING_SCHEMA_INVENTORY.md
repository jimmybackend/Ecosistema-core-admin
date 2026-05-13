# ECOSISTEMA Landing Pages — Schema Inventory (PR #88)

## Propósito del módulo Landing Pages
Documentar el esquema canónico real para Landing Pages antes de implementar UI, servicios o rutas funcionales en `Ecosistema-core-admin`.

Este inventario define:
- tablas y columnas canónicas,
- relaciones con URL Locator, CRM y analítica,
- campos sensibles y reglas de exposición,
- límites de alcance para siguientes PRs.

## Fuentes revisadas
1. `jimmybackend/Ecosistema-core-admin` (README, docs, rutas, smoke-check, patrones Core).
2. `jimmybackend/Ecosistema-bd` como fuente de contraste documental SQL/mapeo (si disponible en entorno local).
3. `jimmybackend/mailit-click` sólo como referencia funcional legacy (si disponible en entorno local).
4. Catálogo/dump real `adbbmis1_eco` como fuente canónica de tablas/campos.

## Estado de acceso a mailit-click
En este entorno de trabajo local no se encontró checkout de `jimmybackend/mailit-click`. Por lo tanto, su estado se considera **no disponible localmente** para inspección de código directa en este PR.

## Diferencia entre legacy mailit-click y esquema canónico eco
- `mailit-click` sólo se usa como referencia funcional legacy (ideas de páginas públicas, visitas, formularios y tracking).
- La definición canónica de tablas/campos para implementación en Core Admin se toma exclusivamente de `adbbmis1_eco`.
- En caso de discrepancias entre legado, documentación histórica y DB real, prevalece `adbbmis1_eco`.

## Tabla `landing_pages`
Columnas canónicas:
`id`, `tenant_id`, `campaign_id`, `template_id`, `owner_user_id`, `created_by_user_id`, `title`, `slug`, `description`, `status`, `page_type`, `public_url`, `seo_title`, `seo_description`, `custom_head_html`, `custom_body_html`, `published_at`, `unpublished_at`, `created_at`, `updated_at`.

Uso funcional esperado:
- Entidad principal de una landing.
- `tenant_id` obligatorio por contexto de sesión (nunca desde request).
- Relación con campaña CRM por `campaign_id`.

## Tabla `landing_templates`
Columnas canónicas:
`id`, `tenant_id`, `created_by_user_id`, `name`, `slug`, `description`, `category`, `preview_image_path`, `template_json`, `is_global`, `is_active`, `created_at`, `updated_at`.

Uso funcional esperado:
- Catálogo de plantillas reutilizables por tenant/global.
- Configuración estructural en `template_json`.

## Tabla `landing_page_versions`
Columnas canónicas:
`id`, `tenant_id`, `landing_page_id`, `version_no`, `title`, `layout_json`, `custom_css`, `custom_js`, `created_by_user_id`, `is_published`, `created_at`.

Uso funcional esperado:
- Versionado editorial/controlado de una landing.
- Publicación lógica por `is_published`.

## Tabla `landing_page_blocks`
Columnas canónicas:
`id`, `tenant_id`, `landing_page_id`, `version_id`, `parent_block_id`, `block_type`, `name`, `sort_order`, `settings_json`, `content_json`, `is_active`, `created_at`, `updated_at`.

Uso funcional esperado:
- Árbol/composición de bloques por versión.
- Ordenado y anidamiento vía `sort_order` + `parent_block_id`.

## Tabla `landing_forms`
Columnas canónicas:
`id`, `tenant_id`, `landing_page_id`, `campaign_id`, `name`, `description`, `submit_button_text`, `success_message`, `redirect_url`, `creates_crm_lead`, `default_lead_source_id`, `default_funnel_stage_id`, `default_assigned_user_id`, `score_on_submit`, `is_active`, `created_at`, `updated_at`.

Uso funcional esperado:
- Definición de formularios embebidos por landing.
- Posible integración CRM condicionada por `creates_crm_lead`.

## Tabla `landing_form_fields`
Columnas canónicas:
`id`, `tenant_id`, `form_id`, `field_key`, `label`, `field_type`, `placeholder`, `default_value`, `options_json`, `validation_json`, `crm_target_table`, `crm_target_field`, `is_required`, `is_active`, `sort_order`, `created_at`, `updated_at`.

Uso funcional esperado:
- Definición dinámica de campos por formulario.
- Reglas y opciones en JSON controlado.

## Tabla `landing_visits`
Columnas canónicas:
`id`, `tenant_id`, `landing_page_id`, `campaign_id`, `short_link_id`, `visitor_uuid`, `session_uuid`, `ip_address`, `user_agent`, `referer`, `full_url`, `utm_source`, `utm_medium`, `utm_campaign`, `utm_term`, `utm_content`, `country`, `region`, `city`, `latitude`, `longitude`, `device_type`, `browser_name`, `os_name`, `visited_at`.

Uso funcional esperado:
- Registro analítico de visitas.
- Vínculo opcional con URL Locator vía `short_link_id`.

## Tabla `landing_form_submissions`
Columnas canónicas:
`id`, `tenant_id`, `form_id`, `landing_page_id`, `campaign_id`, `visit_id`, `crm_lead_id`, `submitted_by_user_id`, `contact_name`, `email`, `phone`, `company_name`, `interest`, `message`, `raw_data_json`, `ip_address`, `user_agent`, `country`, `region`, `city`, `latitude`, `longitude`, `status`, `spam_score`, `submitted_at`, `processed_at`.

Uso funcional esperado:
- Cabecera de una submission.
- Vínculo a CRM y visita de origen cuando aplique.

## Tabla `landing_form_submission_values`
Columnas canónicas:
`id`, `tenant_id`, `submission_id`, `field_id`, `field_key`, `field_label`, `value_text`, `value_json`, `file_path`, `s3_key`, `created_at`.

Uso funcional esperado:
- Valores detallados por campo para cada submission.
- Soporte texto/JSON/archivo según tipo de campo.

## Tabla `landing_conversions`
Columnas canónicas:
`id`, `tenant_id`, `landing_page_id`, `campaign_id`, `visit_id`, `submission_id`, `crm_lead_id`, `conversion_type`, `conversion_value`, `currency`, `occurred_at`.

Uso funcional esperado:
- Registro de eventos de conversión atribuibles a visitas/forms.

## Relación con URL Locator
Relaciones canónicas de integración:
- `url_short_links.landing_page_id` → referencia de short link hacia landing.
- `landing_visits.short_link_id` → traza de visita originada por short link.

Lineamiento de implementación futura:
- Mantener aislamiento por tenant usando sesión/autenticación actual.
- No aceptar `tenant_id` desde request público/administrativo.

## Relación con CRM
Claves de integración canónica:
- `campaign_id` en landing/pages/forms/visits/submissions/conversions.
- `crm_lead_id` en submissions y conversions.

## Relación futura con Browser Analytics
`landing_visits` y metadatos (dispositivo, navegador, OS, UTM, geodatos) preparan la base para consolidación futura con analítica browser, sin activar tracking nuevo en este PR.

## Campos sensibles y reglas de exposición
Campos sensibles prioritarios:
- HTML/JS/CSS/JSON: `custom_head_html`, `custom_body_html`, `template_json`, `layout_json`, `custom_css`, `custom_js`, `settings_json`, `content_json`, `raw_data_json`, `value_json`.
- URLs/rutas: `redirect_url`, `public_url`, `full_url`, `referer`, `file_path`, `s3_key`, `preview_image_path`.
- PII/contacto: `contact_name`, `email`, `phone`, `company_name`, `message`, `value_text`.
- Telemetría: `ip_address`, `user_agent`, `visitor_uuid`, `session_uuid`, `latitude`, `longitude`.

Reglas para próximos PRs:
- Mínima exposición por defecto (listas resumidas + enmascarado).
- Escape/sanitización estricta en vistas para contenido HTML/JS.
- Nunca exponer secretos ni rutas internas de storage.
- Tenant isolation estricto vía contexto de sesión.

## Roadmap recomendado PR #89 a #92
- **PR #89**: Read-only admin de `landing_pages` + `landing_templates` + `landing_page_versions` (sin escritura, sin rutas públicas).
- **PR #90**: Read-only admin de `landing_forms` + `landing_form_fields` + `landing_form_submissions` + `landing_form_submission_values` con controles de privacidad.
- **PR #91**: Read-only analítico de `landing_visits` + `landing_conversions` + relación `short_link_id` (sin tracking write).
- **PR #92**: Contrato técnico de integración controlada Landing ↔ CRM ↔ Browser Analytics (sin mutaciones DB y sin formularios públicos activos).

## Alcance explícitamente fuera de este PR
- No se implementan rutas funcionales `/landing`.
- No se crean migraciones, seeds, tablas o columnas.
- No se agregan escrituras SQL (`INSERT/UPDATE/DELETE`) sobre `landing_*`.
- No se activa tracking real ni formularios públicos.
