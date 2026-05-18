# PR #240 — Checklist obligatoria: preparar demo privada controlada Core Admin

- **Fecha (UTC):** 2026-05-18
- **Repositorio:** `jimmybackend/Ecosistema-core-admin`
- **Tipo de PR:** documental-operativo (sin cambios de esquema real)

## 1) Alcance ejecutado

- [x] Revisión documental de estado demo/QA/deploy/security y cierre de fase previa.
- [x] Creación de checklist de demo privada controlada.
- [x] Creación de runbook de ejecución demo privada controlada.
- [x] Alineación de criterios Go/No-Go para ejecución controlada.

## 2) Documentos revisados

- [x] `README.md`
- [x] `.env.example`
- [x] `.env.vm.example`
- [x] `composer.json`
- [x] `scripts/smoke-check.php`
- [x] `scripts/schema-usage-check.php`
- [x] `docs/project/CORE_ADMIN_DEMO_GO_NO_GO.md`
- [x] `docs/demo/CORE_ADMIN_SAFE_DEMO_DATASET.md`
- [x] `docs/qa/CORE_ADMIN_MANUAL_QA_CHECKLIST.md`
- [x] `docs/project/CORE_ADMIN_SCHEMA_AUDIT_PHASE_CLOSURE_PR225_PR238.md`
- [x] `docs/deploy/` (revisión de referencias operativas)
- [x] `docs/security/` (revisión de referencias de flags/seguridad)

## 3) Documentos creados

- [x] `docs/demo/CORE_ADMIN_PRIVATE_DEMO_CHECKLIST.md`
- [x] `docs/demo/CORE_ADMIN_PRIVATE_DEMO_RUNBOOK.md`
- [x] `docs/schema-usage/checklists/PR_240_preparar_demo_privada_controlada_core_admin.md`

## 4) Flags revisadas (default seguro en `false`)

- [x] `MAIL_SEND_ENABLED`
- [x] `MAIL_ALLOW_TEST_SEND`
- [x] `CLOUD_S3_ENABLED`
- [x] `CLOUD_ALLOW_UPLOADS`
- [x] `CLOUD_ALLOW_DOWNLOADS`
- [x] `ECOSISTEMA_DRIVE_AWS_ENABLED`
- [x] `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS`
- [x] `ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS`
- [x] `ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS`
- [x] `ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS`
- [x] `ECOSISTEMA_AI_PROVIDER_ENABLED`
- [x] `ECOSISTEMA_AI_WRITE_PROPOSALS`
- [x] `ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED`
- [x] `ECOSISTEMA_REPORT_EXPORT_WRITE`
- [x] `ECOSISTEMA_REPORT_EXPORT_INCLUDE_PII`
- [x] `CORE_REGISTRATION_ENABLED`

## 5) Datos sensibles revisados

- [x] No se documentan credenciales reales ni secretos en los documentos creados.
- [x] Se exige dataset ficticio (`example.test`, prefijos `DEMO-`).
- [x] Se prohíben capturas con `.env`, tokens o PII real.

## 6) Servicios externos confirmados como desactivados

- [x] SMTP/envío real
- [x] AWS/S3/Drive remoto
- [x] IA proveedor externo
- [x] Workers/ejecución de workflow real
- [x] Exportes sensibles con PII
- [x] Registro público/core registration

## 7) Validaciones ejecutadas

- [x] `composer dump-autoload`
- [x] `php -l routes/web.php`
- [x] `php -l scripts/smoke-check.php`
- [x] `php -l scripts/schema-compatibility-check.php`
- [x] `php -l scripts/schema-usage-check.php`
- [x] `composer smoke`
- [x] `composer schema:usage`

## 8) Hallazgos

1. Los defaults de flags sensibles en `.env.example` y `.env.vm.example` ya están en modo seguro (`false`) para demo controlada.
2. El gate `composer schema:usage` puede devolver warning controlado cuando la DB no está disponible localmente; este caso se clasifica como **Go con advertencias** para demo privada.
3. No se detectaron instrucciones nuevas que obliguen uso de datos reales en los documentos creados.

## 9) Pendientes para backlog

1. Estandarizar evidencia de corrida pre-demo (template único de salida de comandos).
2. Automatizar checklist de captura segura/no segura para sesiones de demo.
3. Revalidar `schema:usage` en un entorno de verificación con DB controlada accesible para cerrar advertencia de conectividad local.

## 10) Resultado final

- **Resultado:** **Go con advertencias**.
- **Motivo principal de advertencia esperada:** posible no disponibilidad de DB local durante `composer schema:usage` (warning controlado y documentado).
- **Condición de continuidad:** mantener demo en modo privado controlado, sin activación de integraciones reales ni uso de datos reales.
