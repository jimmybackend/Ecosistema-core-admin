# Seguimiento PR #237 — Agregar gate final schema:usage y reporte de alineación código↔DB real

## 1. Alcance ejecutado
- [x] Repositorio confirmado: `jimmybackend/Ecosistema-core-admin`
- [x] Fuente DB real usada: `adbbmis1_eco.sql`
- [x] Tablas del contrato revisadas
- [x] Archivos/rutas inspeccionados
- [x] No se modificó otro repositorio

## 2. Tablas y campos auditados
| Tabla real | Campos revisados | CRUD detectado | Archivos donde se usa | Estado |
|---|---|---|---|---|
| `core_users` | `id, tenant_id, email, username, password_hash, status` | SELECT | `scripts/schema-compatibility-check.php`, `scripts/schema-usage-check.php` | OK |
| `core_sessions` | `id, tenant_id, user_id, session_token_hash, expires_at` | SELECT | `scripts/schema-compatibility-check.php`, `scripts/schema-usage-check.php` | OK |
| `notifications_queue` | `id, tenant_id, user_id, channel_id, status, created_at` | SELECT | `scripts/schema-compatibility-check.php`, `scripts/schema-usage-check.php` | OK |
| `cloud_files` | `id, tenant_id, user_id, original_name, stored_name, s3_key, status` | SELECT | `scripts/schema-compatibility-check.php`, `scripts/schema-usage-check.php` | OK |

## 3. Hallazgos encontrados
| Severidad | Archivo | Función/ruta | Tabla | Campo | Problema | Acción tomada |
|---|---|---|---|---|---|---|
| Media | `composer.json` | scripts | N/A | N/A | No existía gate explícito `schema:usage` para el flujo del PR | Se agregó script `schema:usage` apuntando a `scripts/schema-usage-check.php` |
| Baja | `docs/project/` | reporte final | N/A | N/A | No existía `CORE_ADMIN_SCHEMA_ALIGNMENT_FINAL.md` | Se creó reporte final con estado y advertencias |
| Baja | `docs/schema-usage/checklists/` | seguimiento PR | N/A | N/A | No existía checklist PR #237 | Se creó checklist completo de seguimiento |

## 4. Cambios realizados
- [x] Repositories corregidos si usaban columnas inexistentes
- [x] Services corregidos si enviaban payload incorrecto
- [x] Routes corregidas si faltaba sesión/permiso/CSRF/tenant
- [x] Views corregidas si exponían campos sensibles
- [x] Smoke-check/schema-usage actualizado si aplica
- [x] Documentación `docs/schema-usage/` actualizada

Sin cambios funcionales adicionales en repositories/services/routes/views para este PR; **sin hallazgos críticos** en el alcance auditado.

## 5. Campos obligatorios de INSERT verificados
| Tabla | Campos mínimos reales | ¿El código los llena? | Fuente del valor | Observación |
|---|---|---|---|---|
| N/A (PR de gate/documentación) | N/A | N/A | N/A | Este PR no introduce nuevas escrituras SQL |

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
- [x] `composer schema:usage` si existe

## 9. Resultado
- Estado: `Go con advertencias`
- Warnings aceptados:
  - Entorno local sin conexión DB real puede devolver salida de warning/skip en checks de INFORMATION_SCHEMA.
  - `schema_contracts/` y `adbbmis1_eco.sql` no están versionados en este árbol (ver backlog).
- Pendientes que pasan al backlog:
  - Versionar/adjuntar contrato `schema_contracts/` en Core Admin o automatizar su fetch controlado en CI para trazabilidad completa del gate.
- Evidencia principal:
  - `composer schema:usage` + `scripts/schema-usage-check.php`.
  - `docs/project/CORE_ADMIN_SCHEMA_ALIGNMENT_FINAL.md`.
