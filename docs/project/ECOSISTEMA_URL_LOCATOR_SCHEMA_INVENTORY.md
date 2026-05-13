# Ecosistema URL Locator — Schema Inventory (PR #81)

## 1) Propósito del módulo URL Locator
Definir el inventario técnico **canónico y real** del esquema URL Locator para Ecosistema Core Admin antes de implementar rutas o lógica funcional. Este documento establece qué tablas/columnas se usarán como contrato base y qué campos deben tratarse como sensibles.

## 2) Fuentes revisadas
1. `jimmybackend/Ecosistema-core-admin` (este repositorio): README, documentación de proyecto, rutas y smoke checks.
2. `jimmybackend/Ecosistema-bd`: **no disponible en este entorno local** al momento de este PR.
3. `jimmybackend/mailit-click`: **no disponible en este entorno local** al momento de este PR (referencia legacy, no canónica).
4. Catálogo/dump real `adbbmis1_eco`: **fuente canónica declarada** para nombres finales de tablas y columnas.

## 3) Estado de acceso a mailit-click
- Estado: sin checkout local disponible en este entorno.
- Decisión: mantener `mailit-click` sólo como **referencia funcional legacy** y no como fuente de nombres canónicos.
- Regla aplicada: no copiar código legacy ni asumir nombres finales desde legacy.

## 4) Diferencia entre legacy mailit-click y esquema canónico eco
- `mailit-click`: referencia conceptual de flujos (short links, tracking, idioma, smart/ad/interstitials).
- `adbbmis1_eco`: fuente canónica para nombres de tablas y columnas definitivas.
- Criterio para Core Admin: ante cualquier discrepancia, prevalece `adbbmis1_eco` y se documenta diferencia.

## 5) Tabla canónica: `url_short_links`
Columnas a usar/validar en la implementación futura (sin asumir extras):
- `id`
- `tenant_id`
- `campaign_id`
- `landing_page_id`
- `created_by_user_id`
- `slug`
- `target_url`
- `original_url_after_ads`
- `default_language_code`
- `language_detection_enabled`
- `language_fallback_url`
- `language_query_param`
- `title`
- `description`
- `status`
- `smart_type`
- `requires_access_token`
- `access_token_hash`
- `expires_at`
- `max_clicks`
- `click_count`
- `utm_source`
- `utm_medium`
- `utm_campaign`
- `utm_term`
- `utm_content`
- `created_at`
- `updated_at`

## 6) Tabla canónica: `url_clicks`
Columnas a usar/validar en la implementación futura (sin asumir extras):
- `id`
- `tenant_id`
- `short_link_id`
- `campaign_id`
- `landing_page_id`
- `visitor_uuid`
- `ip_address`
- `user_agent`
- `accept_language_header`
- `detected_language`
- `selected_language`
- `language_redirect_log_id`
- `referer`
- `clicked_url`
- `country`
- `region`
- `city`
- `latitude`
- `longitude`
- `device_type`
- `browser_name`
- `os_name`
- `clicked_at`

## 7) Tablas multilenguaje
- `url_languages`
- `url_short_link_languages`
- `url_language_redirect_logs`

Uso esperado: definición de idiomas soportados, targets por idioma y trazabilidad de decisiones de redirección por idioma.

## 8) Tablas smart/mailit
- `url_smart_link_settings`
- `url_message_templates`
- `url_message_attachments`
- `url_message_access_logs`
- `url_attachment_access_logs`

Uso esperado: configuración smart link, templates de mensaje, adjuntos y logs de acceso relacionados.

## 9) Tablas ads/interstitials
- `url_ad_interstitials`
- `url_ad_impressions`
- `url_ad_clicks`

Uso esperado: soporte de interstitial/ad flow con trazabilidad de impresión y clic.

## 10) Relación con `landing_pages`
- Tabla relacionada: `landing_pages`.
- Relación esperada: `url_short_links.landing_page_id` y/o `url_clicks.landing_page_id`.
- Notas: mantener aislamiento por tenant desde sesión/contexto.

## 11) Relación con browser analytics
- Tablas relacionadas:
  - `browser_analytics_pageviews`
  - `browser_analytics_events`
- Uso esperado: correlación analítica agregada sin mezclar responsabilidades de tracking transaccional de `url_clicks`.

## 12) Relación con CRM/campaigns
- Tabla relacionada (si existe en entorno real): `crm_marketing_campaigns`.
- Relación esperada: `campaign_id` desde `url_short_links` / `url_clicks`.

## 13) Campos sensibles
Campos identificados como sensibles para tratamiento de seguridad/privacidad:
- `target_url`
- `original_url_after_ads`
- `access_token_hash`
- `ip_address`
- `user_agent`
- `referer`
- `clicked_url`
- `final_target_url`
- `metadata_json`
- `custom_js`
- `custom_css`
- `ad_html`
- `body_html`
- `file_path`
- `s3_key`
- `media_s3_key`

## 14) Exposición de campos en UI
### 14.1 Campos que pueden mostrarse completos sólo en admin protegido
- URLs y contenido potencialmente sensible: `target_url`, `original_url_after_ads`, `clicked_url`, `final_target_url`.
- Campos técnicos de seguridad/integridad: `access_token_hash` (preferiblemente no visible en claro), `metadata_json` (con redacción), `file_path`, `s3_key`, `media_s3_key`.

### 14.2 Campos que deben mostrarse como preview/presente/no expuesto
- Preview o truncado: `referer`, `user_agent`, `ad_html`, `body_html`, `custom_js`, `custom_css`.
- Presencia booleana sin valor crudo cuando aplique: `access_token_hash`, `metadata_json`, claves de storage.
- No exponer completo en listados generales ni vistas sin privilegio explícito.

## 15) Reglas de privacidad para clicks
- Aislamiento estricto por `tenant_id` derivado de sesión/contexto.
- No aceptar `tenant_id` desde request para consultas o escritura.
- Minimizar exposición de PII en vistas de listado (IP, user agent, referer).
- Evitar logs de aplicación con datos de click sensibles en claro.
- Definir retención y acceso por rol en PRs funcionales posteriores.

## 16) Estado funcional en este PR
- Este PR es **solo documental**.
- No crea rutas funcionales (`/u/{slug}` ni `/url/locator`), ni repositories/services URL Locator.
- No crea migraciones, seeds, tablas ni columnas.
- No ejecuta INSERT/UPDATE/DELETE sobre `url_short_links` o `url_clicks`.

## 17) Roadmap recomendado (PR #82 a #87)
- **PR #82:** contrato de rutas URL Locator (read-only interno + guards/permisos, sin redirección pública final).
- **PR #83:** repositories read-only para `url_short_links` y `url_clicks` (PDO/prepared statements, tenant por sesión).
- **PR #84:** servicios de listado/detalle admin con redacción de campos sensibles.
- **PR #85:** vistas administrativas URL Locator (inventario, filtros básicos, detalle protegido).
- **PR #86:** auditoría y políticas de privacidad/retención para clicks.
- **PR #87:** smoke checks ampliados + checklist operativo URL Locator para salida controlada.

## 18) Criterio de bloqueo por discrepancias
Si en validación técnica futura alguna tabla/columna no existe en `adbbmis1_eco`, se debe bloquear implementación funcional, documentar el hallazgo y evitar compensaciones inventando estructura.
