# PR #257 — Checklist de ejecución: plan de observabilidad/monitoreo Core Admin

- **Fecha (UTC):** 2026-05-18
- **Repositorio:** `jimmybackend/Ecosistema-core-admin`
- **Tipo de PR:** documental/operativo
- **Resultado esperado:** **Go con advertencias**

## 1) Alcance ejecutado

- [x] Se trabajó únicamente en `jimmybackend/Ecosistema-core-admin`.
- [x] PR documental/operativo sin cambios funcionales de negocio.
- [x] Sin migraciones y sin cambios de esquema.
- [x] Sin activación de SMTP/AWS/S3/IA externa/workers reales/billing real.
- [x] Sin inclusión de secretos ni datos reales.
- [x] Sin declaración de producción SaaS pública.

## 2) Documentos revisados

- [x] `docs/project/CORE_ADMIN_INTERNAL_PILOT_PLAN.md`
- [x] `docs/security/CORE_ADMIN_PREPRODUCTION_HARDENING_CHECKLIST.md`
- [x] `docs/qa/CORE_ADMIN_FULL_MANUAL_QA_PLAN.md`
- [x] `docs/project/CORE_ADMIN_POST_DEMO_BACKLOG_AND_ROADMAP.md`
- [x] `docs/demo/CORE_ADMIN_CONTROLLED_EXTENDED_DEMO_PLAN.md`
- [x] `docs/deploy/CORE_ADMIN_PRIVATE_DEMO_VM_EC2_RUNBOOK.md`
- [x] `docs/ops/` (contexto operativo existente)
- [x] `README.md`
- [x] `routes/web.php`
- [x] `scripts/smoke-check.php`
- [x] `scripts/schema-usage-check.php`
- [x] `scripts/schema-compatibility-check.php`

## 3) Documento creado

- [x] `docs/ops/CORE_ADMIN_OBSERVABILITY_MONITORING_PLAN.md` creado con enfoque de entorno controlado.

## 4) Señales de monitoreo cubiertas

- [x] Disponibilidad login/dashboard.
- [x] Errores HTTP y PHP.
- [x] Sesiones expiradas e intentos de login fallidos.
- [x] Warnings de `smoke`/`schema:usage`.
- [x] Estado de flags críticas.
- [x] Estado de módulos read-only/dry-run/controlled.
- [x] Uso básico de disco.
- [x] Estado de DB (si aplica).
- [x] Estado de workers/cron como apagados/controlados.

## 5) Privacidad/logs revisados

- [x] Definición de logs permitidos (sanitizados).
- [x] Definición de logs prohibidos (`.env`, tokens, API keys, passwords, hashes reales, JSON sensibles completos, PII real).
- [x] Regla de no exponer IP/user-agent completos en documentación pública.
- [x] Regla de no capturar datos reales de clientes.

## 6) Guardrails revisados

- [x] Entorno restringido (local/VM interna/EC2 controlada).
- [x] Integraciones externas reales desactivadas por defecto.
- [x] Sin narrativa de producción SaaS pública.
- [x] Criterios Go / Go con advertencias / No-Go definidos.

## 7) Validaciones ejecutadas

- [x] `composer dump-autoload`
- [x] `php -l routes/web.php`
- [x] `php -l scripts/smoke-check.php`
- [x] `php -l scripts/schema-compatibility-check.php`
- [x] `php -l scripts/schema-usage-check.php`
- [x] `composer smoke`
- [x] `composer schema:usage`

## 8) Resultado de fase

- [x] **Go con advertencias**.
- [x] Warning controlado de `schema:usage` aceptado cuando no existe DB de verificación disponible en entorno aislado.

## 9) Pendientes para backlog

- [x] Integrar monitoreo real al avanzar a preproducción.
- [x] Definir dashboards.
- [x] Definir retención de logs.
- [x] Definir alertas automáticas.
- [x] Definir sanitización automática.
- [x] Re-ejecutar `schema:usage` con DB real disponible.
