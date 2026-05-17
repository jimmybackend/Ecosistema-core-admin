# CORE_ADMIN_PII_SECRETS_OUTPUT_AUDIT

Fecha: 2026-05-17
Alcance: `resources/views`, `README.md`, `docs`, `assets`, `routes/web.php`, `scripts/smoke-check.php`, `.env.example`.

## Objetivo
Verificar que Core Admin no imprima secretos, hashes, tokens, `s3_key`, JSON sensible crudo ni PII completa en vistas administrativas, y documentar controles para evitar regresiones.

## Hallazgos

### 1) Secretos/hashes/tokens
- No se detectó rendering directo de `password_hash`, `session_token_hash`, `refresh_token_hash`, `AWS_SECRET`, `MAIL_PASSWORD` en vistas auditadas.
- `.env.example` contiene placeholders (`change-me`) y no valores reales.

### 2) Campos storage y JSON sensible
- En módulos Drive se mantiene contrato de no exposición de `s3_key` cruda y uso de flags/estados (`present/exposed=false`).
- Se mantiene patrón de indicadores `*_present` para JSON/metadata en pantallas de workflow/mail/audit.

### 3) PII en vistas
- **Ajustado** `resources/views/pages/crm/submission-to-lead-dry-run.php` para mostrar `mapped_fields` con máscara de preview (`email`, `phone`, `contact_name`, etc.) en lugar de valor completo.
- **Ajustado** `resources/views/pages/cloud/drive-storage-usage.php` para mostrar `email/display_name` como preview enmascarado en bloque “Por usuario”.

## Endurecimiento de smoke-check
Se agregó cobertura para validar que:
- La vista `submission-to-lead-dry-run` no renderice acceso directo a `mapped_fields` sensibles sin máscara.
- La vista `drive-storage-usage` no renderice email/display_name crudos y mantenga columna `*_preview`.

## Criterio operativo
- Documentación usa nombres de campo como inventario técnico; no debe incluir valores reales de clientes/credenciales.
- Vistas administrativas muestran indicadores/preview enmascarado para PII y campos sensibles.

## Estado
✅ Auditoría y correcciones aplicadas en vistas críticas + checks de regresión en smoke-check.
