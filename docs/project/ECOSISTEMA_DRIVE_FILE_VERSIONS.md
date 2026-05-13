# Ecosistema Drive: Versiones de archivo (read-only)

## Objetivo
Agregar consulta segura **read-only** de versiones por archivo en `/cloud/drive/files/{id}/versions`, usando tabla real `cloud_file_versions`.

## Tabla real usada
- `cloud_file_versions`: `id`, `tenant_id`, `file_id`, `bucket_id`, `version_no`, `s3_key`, `s3_version_id`, `size_bytes`, `checksum_sha256`, `created_by_user_id`, `created_at`.
- Solo lectura con PDO prepared statements.

## Campos seguros mostrados
- `id`, `file_id`, `bucket_id`, `version_no`, `size_bytes`, `size_human`
- `checksum_sha256_present`, `checksum_sha256_prefix`
- `has_s3_key`, `s3_key_shape_status`
- `has_s3_version_id`, `s3_version_id_exposed=false`
- `created_by_user_id`, `created_at`
- Banderas operativas: `mode=read-only`, `aws_connection=false`, `download_enabled=false`, `restore_enabled=false`, `upload_enabled=false`

## Campos bloqueados
- No se expone `s3_key` cruda.
- No se expone `s3_version_id` crudo.
- No se exponen secretos, `.env`, keys AWS, ni rutas internas.

## Relación con PR #68 y #69
- PR #68 mantiene descarga controlada bloqueada por defecto.
- PR #69 mantiene subida en dry-run.
- Este PR agrega solo metadata de versiones, sin descargar/restaurar/subir.

## Qué NO hace este PR
- No descarga versiones.
- No restaura versiones.
- No crea nuevas versiones.
- No realiza writes en DB.
- No conecta AWS/S3.

## Futuro
- PR separado para restore/download versionado con controles de seguridad, auditoría extendida y feature flags.

## Checklist de seguridad
- [x] Aislamiento tenant/user.
- [x] Ruta protegida por sesión + `cloud.view`.
- [x] Sin exposición de `s3_key` / `s3_version_id` crudos.
- [x] Sin operaciones mutables sobre `cloud_file_versions`.
- [x] Sin AWS SDK ni llamadas reales a S3.
