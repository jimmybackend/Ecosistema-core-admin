# Seguimiento PR #228 — Auditar Cloud/Ecosistema Drive contra tablas cloud_* reales

## 1. Alcance ejecutado
- [x] Repositorio confirmado: `jimmybackend/Ecosistema-core-admin`
- [x] Fuente DB real usada: `adbbmis1_eco.sql`
- [x] Tablas del contrato revisadas
- [x] Archivos/rutas inspeccionados
- [x] No se modificó otro repositorio

## 2. Tablas y campos auditados
| Tabla real | Campos revisados | CRUD detectado | Archivos donde se usa | Estado |
|---|---|---|---|---|
| `cloud_buckets` | `id`, `tenant_id`, `bucket_name`, `provider`, `region`, `is_default`, `status`, `created_at`, `updated_at` | R | `EcosistemaDriveBucketRepository`, `EcosistemaDriveRepairJobRepository`, `EcosistemaDriveStorageUsageRepository` | Corregido (`name`->`bucket_name`) |
| `cloud_files` | `tenant_id`, `user_id`, `bucket_id`, `original_name`, `stored_name`, `s3_key`, `status`, `access_type`, `found_in_s3`, `uploaded_at` y otros de lectura | R/U/C | `CloudFileRepository`, `CloudUploadService`, `EcosistemaDriveS3UploadService`, repos read-only | Corregido (`INSERT` incluye `bucket_id`; enum válido) |
| `cloud_folders` | `tenant_id`, `user_id`, `bucket_id`, `root_id`, `parent_folder_id`, `name`, `prefix`, `folder_type`, `access_type`, `is_deleted` | R/U/C | `CloudFolderRepository`, `CloudService`, repos read-only | OK |
| `cloud_file_versions` | `tenant_id`, `file_id`, `bucket_id`, `version_no`, `s3_key`, `size_bytes` | R | `EcosistemaDriveFileVersionRepository` | OK |
| `cloud_file_access_logs` | `tenant_id`, `file_id`, `action`, `session_id`, `metadata_json` | R | `EcosistemaDriveAccessLogRepository/Service` | OK (read-only y saneado) |
| `cloud_repair_jobs` / `cloud_repair_logs` | columnas de listado y detalle | R | `EcosistemaDriveRepairJobRepository/Service` | Corregido alias de bucket |
| `cloud_storage_usage_daily` | `tenant_id`, `usage_date`, métricas de bytes/count | R | `EcosistemaDriveStorageUsageRepository/Service` | OK |
| `cloud_user_roots` | `tenant_id`, `user_id`, `bucket_id`, `root_prefix`, `status` | R | `CloudRootRepository`, `EcosistemaDriveRootRepository` | OK |

## 3. Hallazgos encontrados
| Severidad | Archivo | Función/ruta | Tabla | Campo | Problema | Acción tomada |
|---|---|---|---|---|---|---|
| Alta | `app/Core/Cloud/CloudFileRepository.php` | `createUploaded` | `cloud_files` | `bucket_id` | `INSERT` omitía campo mínimo obligatorio del esquema real | Se agregó `bucket_id` y resolución segura desde `cloud_user_roots`/`cloud_buckets` por `tenant_id`+`user_id` |
| Media | `app/Core/Cloud/EcosistemaDriveBucketRepository.php` | `listVisible` | `cloud_buckets` | `name` | Se consultaba columna inexistente (`name`) | Se reemplazó por `bucket_name` |
| Media | `app/Core/Cloud/EcosistemaDriveRepairJobRepository.php` | `listRecentJobs`/`findJob` | `cloud_buckets` | `name` | Join con alias de columna inexistente | Se cambió a `b.bucket_name AS bucket_name` |
| Media | `app/Core/Cloud/EcosistemaDriveStorageUsageRepository.php` | `summarizeByBucket` | `cloud_buckets` | `name` | Agregación y `GROUP BY` con columna inexistente | Se cambió a `b.bucket_name` |
| Media | `app/Core/Cloud/EcosistemaDriveS3UploadService.php` | `upload` | `cloud_files` | `access_type`, `bucket_id` | Valor enum no válido (`private`) y bucket fijo hardcodeado (`1`) | Se usa enum válido `normal` y bucket derivado de contexto seguro |

## 4. Cambios realizados
- [x] Repositories corregidos si usaban columnas inexistentes
- [x] Services corregidos si enviaban payload incorrecto
- [x] Routes corregidas si faltaba sesión/permiso/CSRF/tenant
- [ ] Views corregidas si exponían campos sensibles
- [ ] Smoke-check/schema-usage actualizado si aplica
- [x] Documentación `docs/schema-usage/` actualizada

## 5. Campos obligatorios de INSERT verificados
| Tabla | Campos mínimos reales | ¿El código los llena? | Fuente del valor | Observación |
|---|---|---|---|---|
| `cloud_files` (`CloudFileRepository`) | `tenant_id`, `user_id`, `bucket_id`, `original_name`, `stored_name`, `s3_key` | Sí | sesión/contexto + resolución de bucket por tenant/user | Antes faltaba `bucket_id`; corregido |
| `cloud_files` (`EcosistemaDriveS3UploadService`) | `tenant_id`, `user_id`, `bucket_id`, `original_name`, `stored_name`, `s3_key` | Sí | `sessionContext` validado + resolución de bucket por tenant/user | `access_type` corregido a enum real |
| `cloud_folders` | `tenant_id`, `user_id`, `bucket_id`, `name`, `prefix` | Sí | sesión/contexto + root validado | Sin cambios |

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
- Warnings aceptados: no se añadió `composer schema:usage` porque no existe en `composer.json`.
- Pendientes que pasan al backlog: reforzar smoke-check para bloquear uso futuro de `cloud_buckets.name` en nuevas consultas.
- Evidencia principal: diff de repos/services Cloud y este checklist.
