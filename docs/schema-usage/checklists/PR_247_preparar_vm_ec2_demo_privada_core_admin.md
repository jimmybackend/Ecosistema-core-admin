# Checklist PR #247 — Preparar guía de ejecución en VM/EC2 controlada para demo privada Core Admin

- **Fecha:** 2026-05-18 (UTC)
- **Repositorio:** `jimmybackend/Ecosistema-core-admin`
- **Tipo de PR:** documental/operativo (sin cambios de esquema ni migraciones)

## 1) Alcance del PR

- [x] Se crea `docs/deploy/CORE_ADMIN_PRIVATE_DEMO_VM_EC2_RUNBOOK.md`.
- [x] El contenido se limita a operación de demo privada controlada.
- [x] Se declara explícitamente que **no** habilita producción SaaS pública.

## 2) Reglas de seguridad y control

- [x] Se refuerza uso de dataset ficticio (sin datos reales).
- [x] Se documenta preparación segura de `.env` sin commit de secretos.
- [x] Se listan flags críticas que deben permanecer en `false`.
- [x] Se prohíbe activar SMTP real, AWS/S3 real, IA externa, workers reales y billing real.

## 3) Operación VM/EC2 controlada

- [x] Se define entorno permitido: local, VM interna y EC2 controlada.
- [x] Se documentan requisitos mínimos de servidor.
- [x] Se definen reglas de acceso restringido y no exposición pública.
- [x] Se incluye limpieza post-demo.

## 4) Validaciones técnicas documentadas

- [x] `composer dump-autoload`
- [x] `php -l routes/web.php`
- [x] `php -l scripts/smoke-check.php`
- [x] `php -l scripts/schema-compatibility-check.php`
- [x] `php -l scripts/schema-usage-check.php`
- [x] `composer smoke`
- [x] `composer schema:usage`

## 5) Criterio de resultado

- [x] Se incluye criterio Go / Go con advertencias / No-Go.
- [x] Se acepta warning controlado de `schema:usage` por DB no disponible en entorno aislado.
- [x] Se mantiene trazabilidad con artefactos previos de demo privada controlada.

## 6) Exclusiones y cumplimiento

- [x] No se crean migraciones.
- [x] No se modifica esquema.
- [x] No se tocan otros repositorios.
- [x] No se agregan secretos ni datos reales.
