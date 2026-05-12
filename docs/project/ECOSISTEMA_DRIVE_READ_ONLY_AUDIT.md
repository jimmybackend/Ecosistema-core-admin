# Ecosistema Drive Read-Only Audit (PR #62)

## Objetivo
Agregar auditoría operativa **solo de visualización** para rutas administrativas de Ecosistema Drive usando la infraestructura existente (`core_audit`), sin activar AWS/S3 real y sin operaciones mutables.

## Eventos auditados
- `drive.summary.viewed`
- `drive.access_policy.viewed`
- `drive.files.listed`
- `drive.file.viewed`
- `drive.folders.listed`
- `drive.folder.viewed`
- `drive.browser.viewed`
- `drive.root.viewed`
- `drive.buckets.viewed`

## Datos que sí se registran
- `tenant_id`
- `user_id`
- `action`
- `entity_type`
- `entity_id` (si aplica)
- metadata mínima en `after_data`:
  - `route`
  - `mode=read-only`
  - `aws=false`
  - `operation=view|list`

## Datos que NO se registran
No se registran `s3_key`, `stored_name`, `prefix`, `root_prefix`, hashes sensibles, `password_hash`, `secure_hint`, `encryption_key_ref`, JSON crudo sensible (`config_json`, `policy_json`, `metadata_json`), rutas internas, secretos, tokens, contraseñas ni contenido de archivos.

## Alcance de seguridad
- Auditoría read-only de visualización administrativa.
- Sin uploads/downloads/signed URLs.
- Sin AWS/S3 real.
- Sin cambios de estructura de base de datos.
- Si la auditoría falla, **no rompe** la vista principal y no expone detalles internos.

## Limitaciones actuales
- No incluye pantalla nueva de auditoría Drive.
- No incluye exportación, filtros avanzados, sharing, previews, workers ni cron.
- No modifica `jimmybackend/Ecosistema-bd`.
