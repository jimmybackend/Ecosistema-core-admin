# Seguimiento PR #232 — Auditar Browser Analytics contra tablas browser_analytics_* reales

## 1. Alcance ejecutado
- [x] Repositorio confirmado: `jimmybackend/Ecosistema-core-admin`
- [x] Fuente DB real usada: `adbbmis1_eco.sql`
- [x] Tablas del contrato revisadas
- [x] Archivos/rutas inspeccionados
- [x] No se modificó otro repositorio

## 2. Tablas y campos auditados
| Tabla real | Campos revisados | CRUD detectado | Archivos donde se usa | Estado |
|---|---|---|---|---|
| `browser_analytics_sessions` | `tenant_id`, `browser_session_uuid`, `core_session_id`, `entry_url`, `exit_url`, `last_activity_at`, `consent_status` | SELECT/INSERT | `EcosistemaBrowserAnalyticsCollectorRepository.php` | OK |
| `browser_analytics_pageviews` | `tenant_id`, `session_id`, `page_url`, `query_string`, `hash_fragment`, `meta_json`, `viewed_at` | SELECT/INSERT | `EcosistemaBrowserAnalyticsPageviewRepository.php`, `EcosistemaBrowserAnalyticsCollectorRepository.php` | OK |
| `browser_analytics_events` | `tenant_id`, `session_id`, `event_type`, `metadata_json`, `value_text`, `occurred_at` | SELECT/INSERT | `EcosistemaBrowserAnalyticsEventRepository.php`, `EcosistemaBrowserAnalyticsCollectorRepository.php` | OK |
| `browser_analytics_daily_rollups` | `tenant_id`, `rollup_date`, `sessions_count`, `pageviews_count`, `events_count`, `conversions_count` | SELECT | `EcosistemaBrowserAnalyticsDashboardRepository.php` | OK |
| `browser_analytics_attribution` | presencia contractual/documental | Sin CRUD directo | `docs/project/ECOSISTEMA_BROWSER_ANALYTICS_SCHEMA_INVENTORY.md`, `scripts/smoke-check.php` | OK |
| `browser_analytics_campaign_clicks` | presencia contractual/documental | Sin CRUD directo | `docs/project/ECOSISTEMA_BROWSER_ANALYTICS_SCHEMA_INVENTORY.md`, `scripts/smoke-check.php` | OK |
| `browser_analytics_campaign_impressions` | presencia contractual/documental | Sin CRUD directo | `docs/project/ECOSISTEMA_BROWSER_ANALYTICS_SCHEMA_INVENTORY.md`, `scripts/smoke-check.php` | OK |
| `browser_analytics_searches` | presencia contractual/documental | Sin CRUD directo | `docs/project/ECOSISTEMA_BROWSER_ANALYTICS_SCHEMA_INVENTORY.md`, `scripts/smoke-check.php` | OK |

## 3. Hallazgos encontrados
| Severidad | Archivo | Función/ruta | Tabla | Campo | Problema | Acción tomada |
|---|---|---|---|---|---|---|
| Media | `scripts/smoke-check.php` | Inventario Browser Analytics | `browser_analytics_*` | cobertura de tablas | El check validaba solo 4 tablas del contrato y no las 8 tablas objetivo del PR. | Se amplió el check para exigir las 8 tablas reales en inventario/documentación. |
| Informativo | `app/Core/BrowserAnalytics/*` y vistas | repositorios/servicios/rutas Browser Analytics | varias | varias | Sin hallazgos críticos de columnas inexistentes o filtros tenant ausentes en lecturas administrativas. | Se documentó evidencia en `docs/schema-usage/browser_analytics_pr232_audit.md`. |

## 4. Cambios realizados
- [x] Repositories corregidos si usaban columnas inexistentes
- [x] Services corregidos si enviaban payload incorrecto
- [x] Routes corregidas si faltaba sesión/permiso/CSRF/tenant
- [x] Views corregidas si exponían campos sensibles
- [x] Smoke-check/schema-usage actualizado si aplica
- [x] Documentación `docs/schema-usage/` actualizada

> Nota: en esta auditoría no fue necesario cambiar repositories/services/routes/views porque ya cumplían el contrato revisado; la corrección real fue de cobertura de validación (`smoke-check`) y evidencia documental.

## 5. Campos obligatorios de INSERT verificados
| Tabla | Campos mínimos reales | ¿El código los llena? | Fuente del valor | Observación |
|---|---|---|---|---|
| `browser_analytics_sessions` | `tenant_id`, `browser_session_uuid` | Sí | `tenant_id` desde parámetro de servicio; `browser_session_uuid` desde payload validado | Si falta `browser_session_uuid`, se lanza error y no escribe. |
| `browser_analytics_pageviews` | `tenant_id`, `session_id`, `page_url` | Sí | `tenant_id` de servicio, `session_id` resuelto por UUID, `page_url` saneado | `page_url` inválido se convierte en null y depende del payload válido. |
| `browser_analytics_events` | `tenant_id`, `session_id`, `event_type` | Sí | `tenant_id` de servicio, `session_id` resuelto por UUID, `event_type` del evento | Flujo collector exige estructura permitida y valida payload. |

## 6. Reglas tenant/user verificadas
- [x] `tenant_id` se toma de sesión/contexto validado cuando aplica
- [x] `user_id`/`owner_user_id`/`created_by_user_id` no se aceptan libremente desde request cuando aplica
- [x] Lecturas administrativas filtran por tenant cuando la tabla es tenant-aware
- [x] Escrituras administrativas llenan tenant desde contexto seguro

## 7. Campos sensibles revisados
- [x] No se imprimen hashes completos
- [x] No se imprimen tokens completos
- [x] No se imprime `s3_key`, rutas internas o secretos
- [x] JSON sensible se muestra como preview, máscara o `*_present`

## 8. Validaciones ejecutadas
- [x] `composer dump-autoload`
- [x] `php -l routes/web.php`
- [x] `php -l scripts/smoke-check.php`
- [x] `composer smoke`
- [ ] `composer schema:usage` si existe

## 9. Resultado
- Estado: `Go con advertencias`
- Warnings aceptados: `composer schema:usage` no está definido en este repositorio.
- Pendientes que pasan al backlog:
  - Endurecer validación de longitud/tipo exacto por columna en collector service (actualmente usa truncado/saneado genérico), aunque no rompe contrato actual.
- Evidencia principal:
  - `docs/schema-usage/browser_analytics_pr232_audit.md`
  - `scripts/smoke-check.php`
