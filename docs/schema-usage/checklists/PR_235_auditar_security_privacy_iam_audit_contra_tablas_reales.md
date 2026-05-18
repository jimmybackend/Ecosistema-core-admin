# Seguimiento PR #235 — Auditar Security/Privacy/IAM/Audit contra tablas reales

## 1. Alcance ejecutado
- [x] Repositorio confirmado: `jimmybackend/Ecosistema-core-admin`
- [x] Fuente DB real usada: `adbbmis1_eco.sql`
- [x] Tablas del contrato revisadas
- [x] Archivos/rutas inspeccionados
- [x] No se modificó otro repositorio

## 2. Tablas y campos auditados
| Tabla real | Campos revisados | CRUD detectado | Archivos donde se usa | Estado |
|---|---|---|---|---|
| `security_login_attempts` | `tenant_id`, `ip_address`, `status`, `attempted_at` | R | `app/Core/Security/EcosistemaRateLimitRepository.php`, `app/Core/Security/EcosistemaRateLimitDryRunRepository.php` | OK |
| `security_blocked_ips` | `tenant_id`, `ip_address`, `reason`, `blocked_by_user_id`, `blocked_at`, `expires_at` | C | `app/Core/Security/EcosistemaRateLimitRepository.php` | OK |
| `security_incidents` | `tenant_id`, `reported_by_user_id`, `title`, `description`, `severity`, `status`, `source_module`, `source_table`, `source_id`, `detected_at`, `created_at`, `updated_at` | C | `app/Core/Security/EcosistemaRateLimitRepository.php`, `app/Core/Security/EcosistemaRateLimitService.php` | Corregido |
| `audit_entity_changes` | `id`, `module_code`, `entity_table`, `entity_id`, `change_type`, `changed_by_user_id`, `changed_at`, `before_json`, `after_json`, `tenant_id`, `audit_id` | R | `app/Core/Audit/EcosistemaUnifiedAuditRepository.php` | OK |
| `privacy_consents` | Revisión contractual (sin query en alcance auditado) | N/A | `docs/security/ECOSISTEMA_PRODUCTION_HARDENING_CHECKLIST.md` (referencia textual) | Sin uso SQL en alcance |
| `privacy_tracking_preferences` | Revisión contractual (sin query en alcance auditado) | N/A | `docs/security/ECOSISTEMA_PRODUCTION_HARDENING_CHECKLIST.md` (referencia textual) | Sin uso SQL en alcance |
| Resto de tablas objetivo (`audit_compliance_reports`, `compliance_*`, `iam_*`, `privacy_*`, `security_api_keys`, `security_mfa_devices`, `security_trusted_devices`) | Revisión contractual (sin query en alcance auditado) | N/A | No se detectó uso SQL en paths mínimos | Sin uso SQL en alcance |

## 3. Hallazgos encontrados
| Severidad | Archivo | Función/ruta | Tabla | Campo | Problema | Acción tomada |
|---|---|---|---|---|---|---|
| Media | `app/Core/Security/EcosistemaRateLimitService.php` / `app/Core/Security/EcosistemaRateLimitRepository.php` | `enforce` / `insertIncident` | `security_incidents` | `source_id` | Se enviaba `path` string a campo `bigint` nullable | Se cambió a `NULL` y bind tipado `?int` en repository |
| Info | `app/Core/Audit/EcosistemaUnifiedAuditService.php` | `safeChange` | `audit_entity_changes` | `before_json`, `after_json` | Riesgo potencial de exposición de JSON sensible | Verificado: ya sólo expone flags `*_present` |

## 4. Cambios realizados
- [x] Repositories corregidos si usaban columnas inexistentes
- [x] Services corregidos si enviaban payload incorrecto
- [x] Routes corregidas si faltaba sesión/permiso/CSRF/tenant
- [x] Views corregidas si exponían campos sensibles
- [ ] Smoke-check/schema-usage actualizado si aplica
- [x] Documentación `docs/schema-usage/` actualizada

## 5. Campos obligatorios de INSERT verificados
| Tabla | Campos mínimos reales | ¿El código los llena? | Fuente del valor | Observación |
|---|---|---|---|---|
| `security_blocked_ips` | `ip_address`, `reason` | Sí | Valores sanitizados (`payload`) + contexto de ejecución | Además se llena `tenant_id` y `blocked_by_user_id` desde sesión/contexto |
| `security_incidents` | `title` | Sí | Mensaje controlado en servicio | `tenant_id` y `reported_by_user_id` desde sesión/contexto; `source_id` ahora `NULL` tipado |

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
- Warnings aceptados: `composer schema:usage` no se ejecuta al no existir script/comando dedicado en `composer.json`.
- Pendientes que pasan al backlog:
  - Incorporar cobertura funcional (repositorios/servicios) para tablas IAM/Privacy/Compliance aún no utilizadas en los paths auditados.
- Evidencia principal:
  - `docs/schema-usage/PR_235_security_privacy_iam_audit_schema_usage.md`
