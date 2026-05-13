# Ecosistema Drive AWS/S3 config (preparada y apagada)

Este cambio prepara la capa de configuración AWS/S3 sin activar conexión real.

## Relación
- PR #64: validación segura de `s3_key`.
- PR #66: signed URL dry-run sin URL real.

## Variables
Todas apagadas por defecto en `.env.example`:
- `ECOSISTEMA_DRIVE_AWS_ENABLED=false`
- `ECOSISTEMA_DRIVE_AWS_REGION=`
- `ECOSISTEMA_DRIVE_AWS_BUCKET=`
- `ECOSISTEMA_DRIVE_AWS_ENDPOINT=`
- `ECOSISTEMA_DRIVE_AWS_ACCESS_KEY_ID=`
- `ECOSISTEMA_DRIVE_AWS_SECRET_ACCESS_KEY=`
- `ECOSISTEMA_DRIVE_AWS_SESSION_TOKEN=`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=false`
- `ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS=false`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS=false`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS=false`

## Seguridad
- No usa AWS SDK.
- No crea cliente S3.
- No genera signed URL real.
- No descarga ni sube archivos.
- No imprime secretos.
- Nunca commitear `.env` ni pegar secretos en issues/logs/vistas.

## Futuro PR
Queda pendiente activar conexión controlada para descarga S3, manteniendo validaciones y auditoría.
