# Checklist PR #254 — Crear plan de piloto interno controlado Core Admin

- **Fecha (UTC):** 2026-05-18
- **Repositorio:** `jimmybackend/Ecosistema-core-admin`
- **Tipo de PR:** Documental / operativo (sin cambios de esquema ni migraciones)

## 1) Alcance ejecutado

- [x] Definir plan formal de piloto interno controlado post-demo ampliada.
- [x] Mantener alcance no productivo (sin SaaS pública).
- [x] Documentar ejecución por fases con criterios de entrada/salida.

## 2) Documentos revisados

- [x] `docs/demo/CORE_ADMIN_CONTROLLED_EXTENDED_DEMO_PLAN.md`
- [x] `docs/project/CORE_ADMIN_POST_DEMO_BACKLOG_AND_ROADMAP.md`
- [x] `docs/demo/CORE_ADMIN_PRIVATE_DEMO_READINESS_MASTER.md`
- [x] `docs/deploy/CORE_ADMIN_PRIVATE_DEMO_VM_EC2_RUNBOOK.md`
- [x] `docs/demo/CORE_ADMIN_SAFE_DEMO_DATASET.md`
- [x] `README.md`

## 3) Documento creado

- [x] `docs/project/CORE_ADMIN_INTERNAL_PILOT_PLAN.md`

## 4) Riesgos documentados

- [x] Riesgos bloqueantes identificados (seguridad, tenancy, integraciones reales, evidencia).
- [x] Riesgos aceptados controlados (incluye warning `schema:usage` sin DB).

## 5) Guardrails revisados

- [x] No activar SMTP/AWS-S3/IA externa/workers/billing reales.
- [x] No usar datos reales, PII real ni secretos.
- [x] Mantener flags sensibles en `false`.
- [x] No migraciones ni cambios de esquema.

## 6) Validaciones ejecutadas

- [x] `composer dump-autoload`
- [x] `php -l routes/web.php`
- [x] `php -l scripts/smoke-check.php`
- [x] `php -l scripts/schema-compatibility-check.php`
- [x] `php -l scripts/schema-usage-check.php`
- [x] `composer smoke`
- [x] `composer schema:usage`

## 7) Resultado

- [x] **Go con advertencias**
- [ ] Go limpio
- [ ] No-Go

Notas:

- Se acepta warning controlado de `schema:usage` cuando no haya DB disponible en entorno aislado, sin evidencia de regresión funcional.

## 8) Pendientes para backlog

- [x] Re-ejecutar `composer schema:usage` en entorno con DB de verificación controlada.
- [x] Convertir hallazgos de piloto en backlog priorizado por riesgo.
- [x] Preparar gate de hardening preproducción con evidencia técnica.
