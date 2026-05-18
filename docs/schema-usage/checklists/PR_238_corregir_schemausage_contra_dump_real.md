# Seguimiento PR #238 — Corregir `schema:usage` contra dump real `adbbmis1_eco.sql`

## 1) Alcance ejecutado
- [x] Revisión del gate `composer schema:usage` y wrapper `scripts/schema-usage-check.php`.
- [x] Revisión y corrección de columnas críticas en `scripts/schema-compatibility-check.php`.
- [x] Validación de checks de smoke relacionados a `schema:usage`.
- [x] Ejecución de validaciones técnicas solicitadas.

## 2) Tablas y campos auditados (`$criticalColumns`)
- `core_users`
- `core_sessions`
- `core_tenants`
- `core_roles`
- `core_permissions`
- `core_role_permissions`
- `core_user_roles`
- `core_modules`
- `core_audit`
- `cloud_files`
- `cloud_folders`
- `mail_messages`
- `notifications_queue`
- `crm_leads`
- `crm_marketing_campaigns`
- `workflow_rules`
- `workflow_runs`
- `workflow_run_logs`
- `os_ai_proposals`

## 3) Columnas corregidas
1. `core_audit.entity_type` ➜ `core_audit.entity_table`.
2. `cloud_folders.status` ➜ set real de columnas críticas de `cloud_folders`:
   - `bucket_id`, `root_id`, `parent_folder_id`, `prefix`, `prefix_hash`, `folder_type`, `access_type`, `password_hash`, `secure_hint`, `found_in_s3`, `is_system`, `is_deleted`, `deleted_at`, `created_at`, `updated_at` (además de `id`, `tenant_id`, `user_id`, `name`).

## 4) Columnas inexistentes detectadas
- `core_audit.entity_type` (inexistente en contrato real; reemplazada).
- `cloud_folders.status` (inexistente en contrato real; reemplazada).

## 5) Equivalencias reales usadas
- `entity_type` equivalente real aplicado: `entity_table`.
- `status` (en `cloud_folders`) no tiene equivalente único directo; se sustituyó por columnas estructurales/estado reales (`access_type`, `found_in_s3`, `is_system`, `is_deleted`, `deleted_at`) y de jerarquía/metadata.

## 6) Archivos revisados
- `scripts/schema-compatibility-check.php`
- `scripts/schema-usage-check.php`
- `scripts/smoke-check.php`
- `composer.json`
- `docs/project/CORE_ADMIN_SCHEMA_ALIGNMENT_FINAL.md`
- `docs/schema-usage/checklists/PR_238_corregir_schemausage_contra_dump_real.md`

## 7) Hallazgos
- El gate `schema:usage` ya estaba integrado correctamente vía wrapper.
- El desalineamiento principal estaba en el listado de columnas críticas del checker base.
- El comportamiento read-only y de warning por DB no disponible se mantiene.

## 8) Cambios hechos
- Se actualizó `$criticalColumns` para alinear `core_audit` y `cloud_folders` al contrato real esperado.
- Se mantuvo el enfoque read-only (`INFORMATION_SCHEMA` / `SELECT`) sin escrituras.

## 9) Reglas tenant/user revisadas
- [x] Aplican en los checks críticos (`tenant_id`, `user_id`) y se preservan en tablas core/cloud/mail/notifications/crm/workflow/os.

## 10) Campos sensibles revisados
- [x] `password_hash`, `secure_hint`, `ip_address`, `user_agent`, `old_values`, `new_values` quedan en validación de presencia de esquema (sin exponer datos).

## 11) Validaciones ejecutadas
- [x] `composer dump-autoload`
- [x] `php -l scripts/schema-compatibility-check.php`
- [x] `php -l scripts/schema-usage-check.php`
- [x] `php -l scripts/smoke-check.php`
- [x] `php -l routes/web.php`
- [x] `composer smoke`
- [x] `composer schema:usage`

## 12) Resultado
- **Go con advertencias**.
- Advertencia aceptada: en entornos sin DB disponible, `schema:usage` retorna warning controlado (sin fatal), por diseño read-only seguro.

## 13) Pendientes para backlog
- Confirmar periódicamente (cuando el dump canónico esté disponible en el árbol o pipeline) que cada entrada de `$criticalColumns` siga 100% trazable al `adbbmis1_eco.sql` versionado.
- Opcional: automatizar comparación contra artefacto de contrato SQL versionado para evitar regresiones manuales.
