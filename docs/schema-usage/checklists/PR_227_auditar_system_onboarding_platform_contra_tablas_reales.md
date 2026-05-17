# Seguimiento PR #227 — Auditar System/Onboarding/Platform contra tablas reales

## 1. Alcance ejecutado
- [x] Repositorio confirmado: `jimmybackend/Ecosistema-core-admin`
- [x] Fuente DB real usada: `adbbmis1_eco.sql`
- [x] Tablas del contrato revisadas
- [x] Archivos/rutas inspeccionados
- [x] No se modificó otro repositorio

## 2. Tablas y campos auditados
| Tabla real | Campos revisados | CRUD detectado | Archivos donde se usa | Estado |
|---|---|---|---|---|
| `system_health_check_definitions` | `check_code`, `name`, `module_code`, `check_type`, `severity`, `expected_signal`, `is_active` | SELECT | `app/Core/System/HealthRepository.php`, `resources/views/pages/system/health.php` | Corregido |
| `system_logs` | `tenant_id`, `user_id`, `level`, `module_code`, `channel`, `message`, `context_json`, `ip_address`, `user_agent` | SELECT/INSERT | `app/Core/System/LogRepository.php`, `app/Core/System/HealthService.php`, `routes/web.php`, `resources/views/pages/system/logs.php` | Corregido |
| `onboarding_runs` / `onboarding_run_steps` / `onboarding_run_logs` / `onboarding_flows` / `onboarding_steps` | columnas usadas en queries de lectura/escritura | SELECT/INSERT/UPDATE | `app/Core/Onboarding/*`, `routes/web.php`, `resources/views/pages/onboarding/*` | Sin hallazgos críticos |
| `system_workers` | `status`, `last_heartbeat_at` | SELECT | `app/Core/Platform/EcosistemaPlatformHealthRepository.php` | Sin hallazgos críticos |

## 3. Hallazgos encontrados
| Severidad | Archivo | Función/ruta | Tabla | Campo | Problema | Acción tomada |
|---|---|---|---|---|---|---|
| Alta | `app/Core/System/HealthRepository.php` | `listDefinitionsWithLastResult` / `findDefinitionById` | `system_health_check_definitions` | `code`, `target`, `interval_seconds`, `timeout_seconds`, `status` | Se usaban columnas no existentes en contrato real | Reescritura a columnas reales (`check_code`, `module_code`, `severity`, `expected_signal`, `is_active`) y join con `system_health_check_runs` |
| Alta | `app/Core/System/HealthRepository.php` / `HealthService.php` | `insertResult` | `system_health_check_results` | tabla/columnas fuera de contrato objetivo | Escritura en estructura no alineada al contrato | Se eliminó inserción de resultados y se conservó logging seguro en `system_logs` |
| Alta | `app/Core/System/LogRepository.php` | `listRecent` / `insert` | `system_logs` | `context` | Columna inexistente (`context` vs `context_json`) | Se corrigió SELECT/INSERT a `context_json` |
| Media | `routes/web.php` | `/system/health`, `/system/logs` | `system_logs` / health tables | `tenant_id` de contexto | No se forzaba filtro tenant en listado de logs y health | Se cambió a `auth_tenant_id` de sesión para lectura |

## 4. Cambios realizados
- [x] Repositories corregidos si usaban columnas inexistentes
- [x] Services corregidos si enviaban payload incorrecto
- [x] Routes corregidas si faltaba sesión/permiso/CSRF/tenant
- [x] Views corregidas si exponían campos sensibles
- [x] Smoke-check/schema-usage actualizado si aplica
- [x] Documentación `docs/schema-usage/` actualizada

## 5. Campos obligatorios de INSERT verificados
| Tabla | Campos mínimos reales | ¿El código los llena? | Fuente del valor | Observación |
|---|---|---|---|---|
| `onboarding_runs` | `tenant_id`, `flow_id` | Sí | sesión (`auth_tenant_id`) + flujo validado en repositorio/service | Correcto |
| `onboarding_run_steps` | `run_id`, `step_id` | Sí | run recién creado + pasos activos del flujo | Correcto |
| `onboarding_run_logs` | `run_id`, `message` | Sí | contexto interno del sistema y acciones controladas | Correcto |
| `system_logs` | `message` | Sí | servicio interno (`HealthService`) | Corregido a `context_json` + tenant de sesión |

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
- Warnings aceptados: `composer schema:usage` no está declarado en `composer.json`.
- Pendientes que pasan al backlog: revisar unificación de estados/resultados entre `system_health_check_runs` y módulo System/Health para UX avanzada de ejecución.
- Evidencia principal: correcciones en `HealthRepository`, `HealthService`, `LogRepository`, `routes/web.php`, vistas de system y esta checklist.
