# Configuración Compartida S3 Drive (PR #46)

## Propósito
Definir una configuración compartida y **segura por defecto** para la futura integración Core Admin ↔ S3 Drive, sin activar AWS/S3 real y sin llamadas remotas en esta etapa.

## Variables S3_DRIVE_*
- `S3_DRIVE_ENABLED=false`: mantiene deshabilitada la integración.
- `S3_DRIVE_MODE=contract`: modo documental/contractual; prepara contrato sin invocar integración real.
- `S3_DRIVE_BASE_URL=`: URL base opcional para futuras fases; vacía por defecto.
- `S3_DRIVE_API_TIMEOUT=5`: timeout de referencia para futuras llamadas, sin uso remoto activo en este PR.
- `S3_DRIVE_ALLOW_REMOTE_CALLS=false`: bloquea llamadas remotas.
- `S3_DRIVE_ALLOW_SIGNED_URLS=false`: bloquea signed URLs.
- `S3_DRIVE_ALLOW_REMOTE_UPLOADS=false`: bloquea subidas remotas.
- `S3_DRIVE_ALLOW_REMOTE_DOWNLOADS=false`: bloquea descargas remotas.

## Valores por defecto seguros
Todos los flags sensibles quedan en `false` para evitar activaciones accidentales. El comportamiento esperado es de **no conexión externa** y operación local/controlada.

## Significado de `mode=contract`
`contract` indica preparación por contrato entre repositorios (Core Admin y `jimmybackend/s3`) sin acoplar implementación ni ejecutar llamadas HTTP hacia S3 Drive en este PR.

## Garantías de este PR
- No hay llamadas reales al repo `jimmybackend/s3`.
- No hay conexión AWS real ni uso de SDK para operación remota.
- No hay generación de signed URLs.
- No hay subida remota de archivos.
- No hay descarga remota de archivos.
- No hay cambios de base de datos (`cloud_*` permanece canónico en `Ecosistema-bd`).

## Nota sobre mailit-click
El repositorio `mailit-click` queda explícitamente fuera de esta etapa y se evaluará en PR posterior.
