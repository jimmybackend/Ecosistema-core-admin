# Ecosistema Core Admin — Pendientes futuros

> Este documento enumera pendientes de siguientes etapas. No se implementan en este PR.

## Seguridad y autorización
- Autorización fina por permisos (enforcement global por ruta/acción).
- Asignación de roles a usuarios, alineada a tablas reales y reglas de negocio.

## Mail
- Envío real de correos SMTP.
- Lectura IMAP/POP (si aplica funcionalmente).
- Adjuntos de Mail integrados con Cloud.

## Cloud
- Subida real de archivos.
- Descarga real desde S3.
- Integración AWS SDK.

## Procesamiento asíncrono
- Workers / cron para tareas en segundo plano.

## Onboarding
- Onboarding con aprovisionamiento real.

## Observabilidad y trazabilidad
- Auditoría automática más completa en acciones críticas.

## Calidad
- Tests automatizados (unitarios/funcionales/integración).

## Producción e infraestructura
- Hardening de producción.
- Deploy en EC2.
- Backups y health checks externos.
