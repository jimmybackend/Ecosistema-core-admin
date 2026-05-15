# ECOSISTEMA — Reports Schema Inventory (canónico `adbbmis1_eco`)

## Objetivo

Este documento inventaría **únicamente** el esquema real asociado a Reports en la base canónica `adbbmis1_eco`, sin activar funcionalidad nueva, sin escrituras y sin cambios de estructura.

Alcance de este PR (#137):
- Inventario documental read-only.
- Alineación con patrón incremental: inventario → dry-run → controlado por flags.
- Sin rutas funcionales nuevas.

## Fuente canónica y precedencia

1. **Canónico**: `adbbmis1_eco`.
2. `Ecosistema-bd`: referencia secundaria documental.
3. `mailit-click`: referencia legacy funcional (no canónica, no copiar código).

Si existe discrepancia entre fuentes, prevalece `adbbmis1_eco`.

## Tablas Reports inventariadas

### 1) `reports_dashboards`

Columnas reales:
- `id`
- `tenant_id`
- `owner_user_id`
- `dashboard_key`
- `name`
- `description`
- `visibility`
- `layout_json` *(sensible)*
- `is_active`
- `created_at`
- `updated_at`

Uso esperado (documental): contenedor de dashboards por tenant/owner.

### 2) `reports_widgets`

Columnas reales:
- `id`
- `tenant_id`
- `dashboard_id`
- `widget_key`
- `title`
- `widget_type`
- `config_json` *(sensible)*
- `sort_order`
- `created_at`
- `updated_at`

Uso esperado (documental): definición y orden de widgets de cada dashboard.

### 3) `reports_saved_queries`

Columnas reales:
- `id`
- `tenant_id`
- `owner_user_id`
- `query_key`
- `name`
- `description`
- `source_module`
- `query_sql` *(sensible)*
- `query_json` *(sensible)*
- `is_active`
- `created_at`
- `updated_at`

Uso esperado (documental): catálogo de consultas guardadas, con protección reforzada de SQL/JSON.

### 4) `reports_exports`

Columnas reales:
- `id`
- `tenant_id`
- `report_type`
- `source_id`
- `format`
- `status`
- `system_job_id`
- `file_id` *(sensible; referencia potencial a cloud files)*
- `requested_by_user_id`
- `requested_at`
- `completed_at`
- `metadata_json` *(sensible)*

Uso esperado (documental): trazabilidad de exportaciones y estado de ejecución.

## Fuentes de métricas relacionadas para reportes

Tablas identificadas como fuente de insumos analíticos:
- `browser_analytics_daily_rollups`
- `url_clicks`
- `landing_visits`
- `landing_form_submissions`
- `crm_leads`
- `crm_campaign_leads`
- `crm_lead_conversions`

Relación documental adicional:
- `cloud_files` sólo cuando `reports_exports.file_id` requiera trazabilidad del archivo exportado.

## Campos sensibles y reglas de exposición

Campos de alto cuidado:
- `reports_saved_queries.query_sql`
- `reports_saved_queries.query_json`
- `reports_dashboards.layout_json`
- `reports_widgets.config_json`
- `reports_exports.metadata_json`
- `reports_exports.file_id` y datos asociados en `cloud_files`
- PII en tablas de leads/submissions

Reglas para fases futuras (no implementadas en este PR):
- No exponer contenido crudo completo de JSON/SQL.
- Mostrar sólo previews/redacciones seguras cuando aplique.
- No imprimir correos/teléfonos/IP/User-Agent/payloads completos en vistas administrativas.

## Reglas técnicas obligatorias para implementación futura

- Tenant: usar tenant actual de sesión/contexto.
- **No** aceptar `tenant_id` desde request.
- IDs: validar enteros positivos.
- Acceso DB: PHP + PDO + prepared statements.
- Sin migraciones/seeds/tablas/campos nuevos.
- Si una columna no existe en canónico: bloquear flujo y documentar, sin inventar estructura.

## Estado de este PR

- Cambio documental exclusivamente.
- Sin escrituras a DB.
- Sin creación de rutas funcionales.
- Sin flags nuevas obligatorias.

## Siguientes pasos (fuera de este PR)

1. Definir read-only repositories/services para listado/detalle (sin write).
2. Añadir dry-run para export/query planning sin ejecución real.
3. Controlar cualquier write por flags en false por defecto.
