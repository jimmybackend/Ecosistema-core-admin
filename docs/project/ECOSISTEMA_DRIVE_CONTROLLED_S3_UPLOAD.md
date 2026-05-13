# Ecosistema Drive — Subida S3 real controlada

Objetivo: habilitar infraestructura de subida S3 real **solo** con flags explícitas y configuración AWS válida.

## Relación con dry-run
- `GET /cloud/drive/upload-dry-run` sigue como simulación segura.
- `GET/POST /cloud/drive/upload` implementa ruta controlada con validaciones estrictas.

## Flags requeridas
- `ECOSISTEMA_DRIVE_ENABLED=true`
- `ECOSISTEMA_DRIVE_AWS_ENABLED=true`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=true`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS=true`
- `ECOSISTEMA_DRIVE_MODE=controlled_upload`
- `CLOUD_ALLOW_UPLOADS=true`
- `CLOUD_S3_ENABLED=true`

## Flujo seguro
1. Requiere login + permiso `cloud.manage`.
2. Valida CSRF y archivo multipart.
3. Valida tamaño/extensión/MIME/nombre.
4. Genera `s3_key` interno y lo valida (sin exponerlo).
5. Solo si SDK AWS existe y todo está habilitado, ejecuta `putObject`.
6. Inserta metadata en `cloud_files` solo tras éxito.

## Seguridad
- No se acepta `bucket`, `s3_key`, `path` desde request.
- No se exponen secretos, claves AWS ni `.env`.
- Rollback: apagar flags anteriores.
