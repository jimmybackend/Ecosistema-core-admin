# ECOSISTEMA Browser Analytics Events Read-only

## Objetivo
Habilitar vistas administrativas read-only para consultar `browser_analytics_events` por tenant de sesión, sin escritura en base de datos y sin exponer campos sensibles completos.

## Alcance
- Rutas:
  - `GET /browser/analytics/events`
  - `GET /browser/analytics/pageviews/{id}/events`
- Componentes:
  - `EcosistemaBrowserAnalyticsEventRepository`
  - `EcosistemaBrowserAnalyticsEventService`
  - Vistas `events.php` y `pageview-events.php`

## Seguridad aplicada
- Tenant aislado desde sesión autenticada (`auth_tenant_id`).
- Uso de PDO + prepared statements.
- Modo explícito `read-only` y `db_write=false`.
- No se expone `metadata_json` crudo.
- No se expone `element_url` completo ni `value_text` completo: solo previews sanitizados.

## Diferencias documentadas
- La vista y DTO muestran solo indicadores (`*_present`) y previews para campos sensibles.
- No se implementa collector ni endpoints de escritura.
