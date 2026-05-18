# PR #235 — Auditoría Security/Privacy/IAM/Audit contra `adbbmis1_eco.sql`

## Evidencia de revisión

| Archivo | Función/query | Tabla | Columnas usadas | Resultado | Acción |
|---|---|---|---|---|---|
| `app/Core/Security/EcosistemaRateLimitRepository.php` | `countRecentFailedLoginsByIp` (`SELECT COUNT(*)`) | `security_login_attempts` | `tenant_id`, `ip_address`, `status`, `attempted_at` | Todas existen en contrato real | Sin cambios funcionales |
| `app/Core/Security/EcosistemaRateLimitRepository.php` | `insertBlockedIp` (`INSERT`) | `security_blocked_ips` | `tenant_id`, `ip_address`, `reason`, `blocked_by_user_id`, `blocked_at`, `expires_at` | Todas existen y cubre mínimos `ip_address`,`reason` | Sin cambios funcionales |
| `app/Core/Security/EcosistemaRateLimitRepository.php` | `insertIncident` (`INSERT`) | `security_incidents` | `tenant_id`, `reported_by_user_id`, `title`, `description`, `severity`, `status`, `source_module`, `source_table`, `source_id`, `detected_at`, `created_at`, `updated_at` | Todas existen; `source_id` real es `bigint` nullable | Ajuste para bind tipado entero/null en `source_id` |
| `app/Core/Security/EcosistemaRateLimitDryRunRepository.php` | `countRecentFailedLoginsByIp` (`SELECT COUNT(*)`) | `security_login_attempts` | `tenant_id`, `ip_address`, `status`, `attempted_at` | Todas existen | Sin cambios |
| `app/Core/Audit/EcosistemaUnifiedAuditRepository.php` | `listEvents` (`LEFT JOIN`) | `audit_entity_changes` | `id`, `audit_id`, `tenant_id` | Todas existen en contrato | Sin cambios |
| `app/Core/Audit/EcosistemaUnifiedAuditRepository.php` | `listEntityChangesForAudit` (`SELECT`) | `audit_entity_changes` | `id`, `module_code`, `entity_table`, `entity_id`, `change_type`, `changed_by_user_id`, `changed_at`, `before_json`, `after_json`, filtros `tenant_id`,`audit_id` | Todas existen en contrato | Sin cambios |
| `app/Core/Security/EcosistemaRateLimitService.php` | `enforce` | `security_incidents` (a través de repo) | `source_id` | Evita valor no numérico para columna `bigint` | Cambiado argumento a `null` |
| `routes/web.php` | `POST /security/rate-limit/dry-run`, `POST /security/rate-limit/enforce` | (escritura indirecta en tablas security) | sesión, permisos, CSRF, tenant de sesión | Cumple controles de auth/permiso/csrf; tenant no viene de request | Sin cambios |
| `resources/views/pages/security/*` y `resources/views/pages/audit/*` | Render de resultados/auditoría | `audit_entity_changes` (campos JSON sensibles) | `before_json`, `after_json`, `metadata_json` | En servicio se exponen sólo flags `*_present` y previews | Sin cambios |

## Hallazgos

1. **Compatibilidad de tipo en `security_incidents.source_id`**: se estaba enviando `path` (string) al registrar incidente en enforcement. Se corrigió para usar `NULL` y bind tipado en repo (`?int`), alineado con el contrato real (`bigint unsigned nullable`).
2. **Sin hallazgos críticos adicionales** en tablas objetivo realmente referenciadas por el código/rutas auditadas en este alcance.

## Notas de alcance

- En las rutas/archivos auditados no se detectaron consultas directas a la mayoría de tablas objetivo del contrato (IAM/Privacy/Compliance) dentro de este repositorio; se deja evidencia explícita en el checklist de PR.
