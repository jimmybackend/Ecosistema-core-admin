# ECOSISTEMA Browser Analytics — Schema Inventory (PR #93)

## 1) Propósito del módulo
Este documento inventaría el esquema **real/canónico** de Browser Analytics en `adbbmis1_eco` como paso previo a cualquier UI o lógica funcional.

Alcance de este PR:
- Sólo documentación técnica.
- Sin collectors, sin rutas públicas nuevas y sin escrituras SQL.
- Sin migraciones/seeds y sin cambios de estructura.

## 2) Fuentes revisadas
1. `jimmybackend/Ecosistema-core-admin` (este repositorio): README, rutas, smoke checks, patrones en módulos URL Locator y Landing.
2. `jimmybackend/Ecosistema-bd`: **no disponible** en el entorno local de este PR, por lo que no se pudo validar SQL externo aquí.
3. `jimmybackend/mailit-click` (legacy): **no disponible** en el entorno local de este PR; se mantiene sólo como referencia funcional legacy.
4. Catálogo/dump canónico `adbbmis1_eco`: fuente de verdad para tablas y columnas listadas abajo.

> Regla canónica aplicada: ante discrepancias, prevalece `adbbmis1_eco`.

## 3) Tabla `browser_analytics_sessions`
Columnas inventariadas:
`id`, `tenant_id`, `user_id`, `core_session_id`, `browser_session_uuid`, `visitor_uuid`, `started_at`, `ended_at`, `last_activity_at`, `entry_url`, `exit_url`, `referrer_url`, `ip_address`, `user_agent`, `device_type`, `browser_name`, `browser_version`, `os_name`, `os_version`, `country`, `region`, `city`, `latitude`, `longitude`, `utm_source`, `utm_medium`, `utm_campaign`, `utm_term`, `utm_content`, `consent_status`, `created_at`, `updated_at`.

Notas:
- Entidad raíz de sesión de navegación por tenant.
- `tenant_id` debe resolverse por sesión/contexto del sistema (no desde request público).

## 4) Tabla `browser_analytics_pageviews`
Columnas inventariadas:
`id`, `tenant_id`, `session_id`, `user_id`, `campaign_id`, `landing_page_id`, `short_link_id`, `crm_lead_id`, `page_url`, `page_title`, `referrer_url`, `path`, `query_string`, `hash_fragment`, `viewed_at`, `duration_ms`, `scroll_depth_percent`, `is_landing_view`, `is_campaign_view`, `meta_json`.

Notas:
- Relación natural hacia `browser_analytics_sessions` por `session_id`.
- Relación cross-módulo hacia URL Locator/Landing/CRM por IDs de negocio.

## 5) Tabla `browser_analytics_events`
Columnas inventariadas:
`id`, `tenant_id`, `session_id`, `pageview_id`, `user_id`, `campaign_id`, `landing_page_id`, `short_link_id`, `crm_lead_id`, `event_type`, `event_name`, `element_id`, `element_text`, `element_url`, `value_numeric`, `value_text`, `score_points`, `metadata_json`, `occurred_at`.

Notas:
- Eventos de interacción asociados opcionalmente a sesión, pageview y contexto comercial.

## 6) Tabla `browser_analytics_attribution`
Columnas inventariadas:
`id`, `tenant_id`, `user_id`, `crm_lead_id`, `session_id`, `campaign_id`, `landing_page_id`, `short_link_id`, `conversion_module`, `conversion_table`, `conversion_id`, `attribution_model`, `first_touch_at`, `last_touch_at`, `conversion_at`, `metadata_json`.

Notas:
- Tabla de atribución multi-touch orientada a conversión.
- `conversion_table`/`conversion_id` requieren validación estricta en futuros PR funcionales.

## 7) Tabla `browser_analytics_campaign_clicks`
Columnas inventariadas:
`id`, `tenant_id`, `session_id`, `impression_id`, `pageview_id`, `user_id`, `campaign_id`, `landing_page_id`, `short_link_id`, `crm_lead_id`, `clicked_url`, `element_id`, `element_text`, `placement`, `occurred_at`.

## 8) Tabla `browser_analytics_campaign_impressions`
Columnas inventariadas:
`id`, `tenant_id`, `session_id`, `pageview_id`, `user_id`, `campaign_id`, `landing_page_id`, `crm_lead_id`, `placement`, `creative_id`, `impression_url`, `viewed_percent`, `visible_ms`, `occurred_at`.

## 9) Tabla `browser_analytics_daily_rollups`
Columnas inventariadas:
`id`, `tenant_id`, `rollup_date`, `campaign_id`, `landing_page_id`, `page_url_hash`, `page_url`, `sessions_count`, `users_count`, `pageviews_count`, `events_count`, `campaign_impressions_count`, `campaign_clicks_count`, `form_starts_count`, `form_submits_count`, `conversions_count`, `avg_duration_ms`, `avg_scroll_depth_percent`, `created_at`, `updated_at`.

## 10) Tabla `browser_analytics_searches` (si existe)
Estado en este PR:
- Se incluye en el inventario como tabla esperada del dominio Browser Analytics.
- No se listan columnas adicionales no confirmadas aquí para evitar inventar estructura.
- Si en verificación posterior de `adbbmis1_eco` no existe o difiere, debe documentarse explícitamente en PR funcional subsiguiente.

## 11) Relaciones con otros módulos
### 11.1 URL Locator
- Relación clave: `short_link_id`.
- Uso esperado: enlazar pageviews/events/clicks/attribution con short links existentes sin crear nuevas tablas.

### 11.2 Landing
- Relación clave: `landing_page_id`.
- Uso esperado: correlación de comportamiento browser con páginas y formularios en modo controlado/read-only en etapas iniciales.

### 11.3 CRM
- Relaciones clave: `crm_lead_id` y `campaign_id`.
- Uso esperado: atribución de interacción/conversión sin habilitar escrituras CRM desde este PR.

## 12) Campos sensibles y reglas de privacidad
Campos de cuidado:
- Identificadores: `browser_session_uuid`, `visitor_uuid`, `core_session_id`.
- URLs/fragmentos: `entry_url`, `exit_url`, `referrer_url`, `page_url`, `query_string`, `hash_fragment`, `clicked_url`, `impression_url`, `element_url`.
- Red/dispositivo/geodatos: `ip_address`, `user_agent`, `latitude`, `longitude`.
- Payloads libres: `meta_json`, `metadata_json`, `value_text`.

Reglas mínimas para PRs funcionales futuros:
- En vistas admin, preferir previews/mascarado y evitar render crudo de campos sensibles.
- Mantener aislamiento por `tenant_id` desde sesión/contexto.
- Nunca aceptar `tenant_id` desde request público.
- Sin exponer secretos de infraestructura ni datos personales innecesarios.

## 13) Roadmap propuesto (PR #94–#98)
- **PR #94:** Adapter/repositories read-only de Browser Analytics (`sessions`, `pageviews`) con filtros por tenant y paginación segura.
- **PR #95:** Read-only de `events` + vistas administrativas con sanitización estricta de campos sensibles.
- **PR #96:** Read-only de `attribution` + cruces controlados con `campaign_id`, `landing_page_id`, `crm_lead_id`.
- **PR #97:** Read-only de `campaign_clicks`/`campaign_impressions` + panel comparativo básico.
- **PR #98:** Read-only de `daily_rollups` (+ `browser_analytics_searches` sólo si se confirma en `adbbmis1_eco`) y checklist de hardening.

## 14) Guardrails explícitos de este PR
- No se crean rutas funcionales `/browser/analytics`.
- No se agregan collectors ni servicios de escritura.
- No se ejecutan `INSERT/UPDATE/DELETE` sobre `browser_analytics_*`.
- No se crean migraciones/seeds.
