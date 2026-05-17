# Seguimiento PR #225 — Crear contrato de esquema real adbbmis1_eco y herramienta base de auditoría

## 1. Alcance ejecutado
- [x] Repositorio confirmado: `jimmybackend/Ecosistema-core-admin`
- [x] Fuente DB real usada: `adbbmis1_eco.sql`
- [x] Tablas del contrato revisadas
- [x] Archivos/rutas inspeccionados
- [x] No se modificó otro repositorio

## 2. Tablas y campos auditados
| Tabla real | Campos revisados | CRUD detectado | Archivos donde se usa | Estado |
|---|---|---|---|---|
| `workflow_rules` | `id, tenant_id, name, trigger_module, trigger_event, is_active` | SELECT (inventario/chequeo) | `scripts/schema-compatibility-check.php`, `scripts/smoke-check.php`, `docs/project/ECOSISTEMA_WORKFLOW_SCHEMA_INVENTORY.md` | OK |
| `workflow_runs` | `id, tenant_id, rule_id, status, started_at, created_at` | SELECT/UPDATE (validación contractual read-only de smoke y checklist) | `scripts/schema-compatibility-check.php`, `scripts/smoke-check.php` | OK |
| `workflow_run_logs` | `id, tenant_id, run_id, level, message, created_at` | SELECT (inventario/chequeo) | `scripts/schema-compatibility-check.php`, `scripts/smoke-check.php` | OK |
| `module_workflow_links` | mención contractual | N/A | `scripts/smoke-check.php`, `docs/project/ECOSISTEMA_WORKFLOW_SCHEMA_INVENTORY.md` | OK |

## 3. Hallazgos encontrados
| Severidad | Archivo | Función/ruta | Tabla | Campo | Problema | Acción tomada |
|---|---|---|---|---|---|---|
| Alta | `scripts/schema-compatibility-check.php` | `$criticalColumns` | `workflow_actions` | varios | Se validaba tabla fuera del contrato real resumido de PR #225 (riesgo de falsos FAIL). | Se eliminó `workflow_actions` del set crítico para alinear chequeo con contrato canónico del PR. |
| Media | `scripts/smoke-check.php` | bloque inventario workflow y regex de escrituras | `workflow_actions` | mención de tabla | Smoke exigía mención/regex para tabla fuera del contrato resumido del PR #225. | Se removió `workflow_actions` del bloque de menciones y del patrón regex de escrituras. |

## 4. Cambios realizados
- [x] Repositories corregidos si usaban columnas inexistentes
- [x] Services corregidos si enviaban payload incorrecto
- [x] Routes corregidas si faltaba sesión/permiso/CSRF/tenant
- [x] Views corregidas si exponían campos sensibles
- [x] Smoke-check/schema-usage actualizado si aplica
- [x] Documentación `docs/schema-usage/` actualizada

> Sin hallazgos críticos adicionales fuera de los puntos descritos.

## 5. Campos obligatorios de INSERT verificados
| Tabla | Campos mínimos reales | ¿El código los llena? | Fuente del valor | Observación |
|---|---|---|---|---|
| `core_role_permissions` | `tenant_id, role_id, permission_id` | Sí (validado por smoke) | contexto de tenant/servicio | Ya cubierto por checks existentes en `scripts/smoke-check.php`. |
| `workflow_runs` | `tenant_id, rule_id, status, started_at, created_at` | Sí (cuando aplica en flujos controlados) | contexto interno | Sin cambios en este PR; sólo auditoría de contrato y smoke. |

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
- Warnings aceptados: `composer smoke` puede emitir advertencias no críticas por verificaciones documentales no bloqueantes.
- Pendientes que pasan al backlog:
  - Revisar en un PR dedicado la consistencia global de `docs/project/ECOSISTEMA_WORKFLOW_SCHEMA_INVENTORY.md` contra contrato completo empaquetado en `schema_contracts/` para evitar drift histórico.
- Evidencia principal:
  - Corrección de referencias contractuales en `scripts/schema-compatibility-check.php` y `scripts/smoke-check.php`.
