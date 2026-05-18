# Core Admin — Checklist de demo privada controlada (PR #240)

- **Fecha base:** 2026-05-18 (UTC)
- **Repositorio:** `jimmybackend/Ecosistema-core-admin`
- **Objetivo:** ejecutar una demo privada controlada, con datos ficticios y guardrails activos, sin activar servicios externos reales.
- **Alcance:** preparación operativa y validación previa (no certifica producción SaaS).

## 1) Precondiciones

- [ ] Demo limitada a entorno privado controlado (local/VM interna).
- [ ] No se usarán datos reales de clientes, proveedores ni personal interno.
- [ ] Se usará tenant demo y usuarios ficticios (`example.test`).
- [ ] Se confirma narrativa técnica: estados **operativo**, **read-only**, **dry-run**, **controlled**.
- [ ] Se confirma explícitamente que esta demo **NO** es salida a producción SaaS.

## 2) Entorno recomendado

- [ ] Ejecutar sobre VM/local con `.env` derivado de `.env.vm.example` o `.env.example`.
- [ ] Mantener APP en modo seguro para demo (`APP_DEBUG=false` en sesión compartida).
- [ ] Base de datos apuntando a entorno controlado/no productivo (`adbbmis1_eco` de demo).
- [ ] Sin workers productivos externos, sin colas productivas y sin integraciones reales.

## 3) Usuario demo/ficticio

- [ ] Usuario owner demo ficticio (ejemplo: `alicia.demo+owner@example.test`).
- [ ] Usuario operador demo ficticio (ejemplo: `bruno.operador@example.test`).
- [ ] Usuario auditor read-only ficticio (ejemplo: `carla.audit@example.test`).
- [ ] Credenciales temporales solo en gestor seguro local; no en markdown ni capturas.

## 4) Datos demo permitidos

- [ ] Tenants, campañas, leads y reportes sintéticos con prefijos `DEMO-`/`CMP-DEMO-`.
- [ ] Correos de prueba exclusivamente `@example.test`.
- [ ] Teléfonos, direcciones y URLs ficticias (`https://demo.local` / `https://example.test`).
- [ ] Archivos demo locales no sensibles (ej. `demo-brief.pdf`).

## 5) Datos prohibidos

- [ ] Correos/telefonía/PII reales.
- [ ] API keys, tokens, JWTs, secretos, passwords reales.
- [ ] Dumps o exportes con datos reales de cliente.
- [ ] Capturas con credenciales, paneles de infraestructura real o logs sensibles.

## 6) Flags que deben permanecer apagadas (`false`)

Verificadas en `.env.example` y `.env.vm.example`:

- [ ] `MAIL_SEND_ENABLED=false`
- [ ] `MAIL_ALLOW_TEST_SEND=false`
- [ ] `CLOUD_S3_ENABLED=false`
- [ ] `CLOUD_ALLOW_UPLOADS=false`
- [ ] `CLOUD_ALLOW_DOWNLOADS=false`
- [ ] `ECOSISTEMA_DRIVE_AWS_ENABLED=false`
- [ ] `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=false`
- [ ] `ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS=false`
- [ ] `ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS=false`
- [ ] `ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS=false`
- [ ] `ECOSISTEMA_AI_PROVIDER_ENABLED=false`
- [ ] `ECOSISTEMA_AI_WRITE_PROPOSALS=false`
- [ ] `ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED=false`
- [ ] `ECOSISTEMA_REPORT_EXPORT_WRITE=false`
- [ ] `ECOSISTEMA_REPORT_EXPORT_INCLUDE_PII=false`
- [ ] `CORE_REGISTRATION_ENABLED=false`

## 7) Comandos previos obligatorios

```bash
composer dump-autoload
php -l routes/web.php
php -l scripts/smoke-check.php
php -l scripts/schema-compatibility-check.php
php -l scripts/schema-usage-check.php
composer smoke
composer schema:usage
```

## 8) Rutas a probar (mínimo)

- [ ] `/login`
- [ ] `/dashboard`
- [ ] `/tenants`, `/users`, `/roles`, `/permissions`, `/modules`
- [ ] `/system/health`, `/system/logs`, `/system/audit`, `/audit/events`
- [ ] `/cloud` y vistas administrativas de drive (solo lectura/controlado)
- [ ] `/reports/*` en lectura/dry-run

## 9) Módulos a mostrar en demo

- [ ] Auth + Dashboard (operativo interno)
- [ ] Tenants/Users/Roles/Permissions/Modules (administración interna)
- [ ] System/Audit (lectura)
- [ ] CRM/Campaigns/Landing/URL Locator/Reports/Workflow en modo read-only/dry-run/controlled

## 10) Módulos que NO deben mostrarse como productivos

- [ ] SMTP/Mail send real.
- [ ] AWS/S3 o Drive remoto real (uploads/downloads/signed URLs).
- [ ] IA externa real.
- [ ] Ejecución real de workflows/workers productivos.
- [ ] Exportes con PII o billing real.

## 11) Criterios Go/No-Go antes de iniciar demo

**Go**
- [ ] Lint y smoke sin fallos críticos nuevos.
- [ ] `schema:usage` en OK o warning controlado por DB no disponible.
- [ ] Flags sensibles confirmadas en `false`.
- [ ] Dataset ficticio validado.

**No-Go**
- [ ] Cualquier evidencia de datos reales/secretos en UI o consola.
- [ ] Flags sensibles activadas sin control.
- [ ] Falla crítica en login/dashboard o en gate base.

## 12) Checklist post-demo

- [ ] Cerrar sesión de todos los usuarios demo.
- [ ] Revocar/rotar credenciales temporales usadas en sesión.
- [ ] Eliminar archivos temporales de export/demo.
- [ ] Confirmar nuevamente flags sensibles en `false`.
- [ ] Registrar resultado final: Go / Go con advertencias / No-Go.

## 13) Limpieza de datos temporales

- [ ] Eliminar o desactivar usuarios demo creados ad hoc.
- [ ] Limpiar registros temporales de pruebas dry-run si aplica.
- [ ] Borrar capturas locales no necesarias o moverlas a repositorio seguro interno.
- [ ] Verificar que no quedaron `.env`, tokens o secretos en staging/commits.

## 14) Notas de seguridad

- Esta demo es **privada controlada** y no habilita capacidad pública SaaS.
- Toda integración externa debe permanecer desactivada por defecto.
- Cualquier excepción debe documentarse, aprobarse y revertirse al cierre.
- Cualquier hallazgo de datos sensibles implica corte inmediato de demo (No-Go).
