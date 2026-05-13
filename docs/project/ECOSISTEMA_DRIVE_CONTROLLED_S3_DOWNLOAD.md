# ECOSISTEMA Drive — Controlled S3 Download

## Objetivo
Implementar infraestructura de descarga S3 controlada por backend, bloqueada por defecto.

## Relación con PRs previos
Continúa el contrato (#63), validación segura s3_key (#64), signed URL dry-run (#66), y AWS/S3 config preparada apagada (#67).

## Banderas obligatorias
Se requiere todo lo siguiente para habilitar ejecución real:
- `ECOSISTEMA_DRIVE_ENABLED=true`
- `ECOSISTEMA_DRIVE_AWS_ENABLED=true`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=true`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS=true`
- `ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS=false`
- `ECOSISTEMA_DRIVE_MODE=controlled_download`

Además, configuración AWS completa y SDK disponible.

## Default seguro
`.env.example` permanece apagado (`false`) para AWS/S3 y descargas remotas.

## Bloqueo seguro
Si faltan flags, SDK o configuración, la ruta muestra estado bloqueado seguro y no expone `s3_key` ni secretos.

## Metadata permitida
`file_id`, `tenant_id`, `user_id`, estado seguro de flags y reason codes.

## Secretos nunca expuestos
Keys AWS, session token, `s3_key`, prefijos internos, endpoints sensibles, URLs firmadas reales.

## Auditoría esperada
Intento de descarga vía `drive.file.download.attempted`, sin secretos.

## Rollback
Regresar banderas a `false`.

## Nota de alcance
Además del flujo de descarga controlada, existe ruta separada `/cloud/drive/upload-dry-run` para simulación de subida, también sin conexión AWS/S3 real.
