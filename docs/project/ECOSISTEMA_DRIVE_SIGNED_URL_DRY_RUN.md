# Ecosistema Drive Signed URL Dry-Run

## Objetivo
Agregar una capa informativa/protegida para simular elegibilidad de generación de signed URL futura sin activar AWS/S3 real.

## Relación con PR #63 y PR #64
- PR #63: contrato de descarga futura (sin descarga real).
- PR #64: validación segura de `s3_key` sin exponerla.
- PR #66: reutiliza validación de PR #64 y agrega dry-run de signed URL.

## Qué valida
- sesión autenticada y permiso `cloud.view`.
- id de archivo entero positivo.
- aislamiento tenant/user mediante repositorio existente.
- estado de metadata segura + forma de `s3_key` (sin mostrarla).

## Qué simula
- modo `dry-run`.
- `signed_url_generated=false`.
- `aws_connection=false`.
- `download_enabled=false`.
- TTL sugerido (`expires_in_seconds_preview`) solo documental.

## Qué NO hace
- no genera signed URLs reales.
- no usa AWS SDK.
- no conecta AWS/S3.
- no descarga, no stream, no redirección.
- no expone `s3_key` ni secretos operativos.

## Próximo paso (PR futuro)
Configurar integración AWS/S3 real (hoy apagada) para signed URLs reales bajo controles de seguridad/auditoría adicionales.
\n- AWS/S3 config preparada y apagada: ver docs/project/ECOSISTEMA_DRIVE_AWS_S3_CONFIG.md
\n- Controlled download S3 backend route añadida: /cloud/drive/files/{id}/download (bloqueada por defecto).
