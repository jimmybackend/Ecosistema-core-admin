# ECOSISTEMA Browser Analytics Pageviews (read-only)

## Objetivo
Exponer consulta de `browser_analytics_pageviews` en modo read-only, usando tenant desde sesión autenticada.

## Alcance
- Repository y service de solo lectura.
- Rutas:
  - `GET /browser/analytics/pageviews`
  - `GET /browser/analytics/sessions/{id}/pageviews`
- Vistas protegidas que **no muestran** `query_string` crudo, `hash_fragment` crudo, ni `meta_json` crudo.
- Adapter actualizado con `pageviews_read=true` y `collector_write=false`.

## Seguridad de datos
DTO de salida con banderas `_present` y `_exposed=false` para campos sensibles.
No hay inserciones/actualizaciones/eliminaciones sobre `browser_analytics_pageviews`.
