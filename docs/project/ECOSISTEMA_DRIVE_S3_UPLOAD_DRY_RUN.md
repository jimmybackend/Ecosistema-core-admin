# Ecosistema Drive - S3 Upload Dry-Run

## Objetivo
Agregar una capa informativa de **subida S3 dry-run** para validar preparación futura sin subir archivos reales.

## Relación con PR #67 y #68
- Continúa la preparación AWS/S3 deshabilitada de PR #67.
- Mantiene el mismo enfoque seguro de operación controlada de PR #68.

## Qué simula
- Estado de elegibilidad para futura subida remota.
- Límites de tamaño (`CLOUD_MAX_UPLOAD_MB`) y extensiones (`CLOUD_ALLOWED_EXTENSIONS`).
- Banderas `ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS`, `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS`, `ECOSISTEMA_DRIVE_AWS_ENABLED`.

## Qué valida
- Ruta protegida con sesión autenticada y permiso `cloud.view`.
- Lectura de configuración segura y respuesta DTO sin secretos.

## Qué NO hace
- No acepta archivos, no procesa `$_FILES`, no usa `move_uploaded_file`.
- No usa `putObject`, no conecta AWS/S3 real.
- No escribe en DB ni en storage.

## Banderas futuras requeridas
Para habilitación futura (en otro PR):
- `ECOSISTEMA_DRIVE_AWS_ENABLED=true`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=true`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS=true`

## Rollback
- Eliminar ruta `/cloud/drive/upload-dry-run`.
- Eliminar contrato/servicio dry-run de subida.
- Eliminar vista informativa y revertir documentación.

## Checklist de seguridad
- AWS/S3 real apagado.
- upload_enabled=false.
- remote_upload_attempted=false.
- db_write=false.
- storage_write=false.
- Sin exposición de keys, token, `.env`, `s3_key`, `stored_name`, rutas internas.

## Siguiente paso
Subida S3 real controlada queda para PR futuro.

- Versiones de archivo Drive read-only disponibles en `/cloud/drive/files/{id}/versions` usando `cloud_file_versions`, sin exponer `s3_key`/`s3_version_id` y sin download/restore real.

- Share contract read-only disponible en `/cloud/drive/files/{id}/share-contract` (sin links/tokens/shares reales). Ver `docs/project/ECOSISTEMA_DRIVE_SHARE_CONTRACT.md`.

- Ver también: \/docs\/project\/ECOSISTEMA_DRIVE_CONTROLLED_S3_UPLOAD.md y ruta controlada `/cloud/drive/upload`.
