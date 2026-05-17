# CORE ADMIN — Guía de demo técnica (sin datos reales)

Guía para demo técnica de **10–15 minutos** enfocada solo en Core Admin, alineada al estado real del repositorio y sin sobreprometer capacidad productiva.

> Frase obligatoria para apertura/cierre: **“No es SaaS público productivo completo; es una base administrativa modular con capas read-only, dry-run y controlled”.**

## 1) Alcance y mensaje de demo

- Alcance: panel administrativo interno Core Admin.
- Objetivo: mostrar estado real por módulo, controles de permisos y enfoque safe-by-default.
- No presentar como listo productivo lo que esté en `read-only`, `dry-run`, `controlled` o `roadmap`.

Fuentes base para esta guía:
- `README.md`
- `docs/project/CORE_ADMIN_MODULE_STATUS.md`
- `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`
- `docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md`
- `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`
- `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md`
- `docs/ops/WORKERS_CRON_CURRENT_STATE.md`
- `routes/web.php`
- `scripts/smoke-check.php`
- `.env.example`

## 2) Guion sugerido (10–15 minutos)

## Min 0–1 — Apertura honesta

- Decir la frase obligatoria.
- Aclarar que la demo es técnica y de operación interna.

## Min 1–3 — Login + Dashboard

1. Entrar por `/login`.
2. Acceder a `/dashboard`.
3. Explicar: autenticación/sesión y punto central operativo.

## Min 3–6 — Core Admin estable (gobierno)

Recorrer:
- `/tenants`
- `/users`
- `/roles`
- `/permissions`
- `/modules`

Mensaje recomendado:
- “La parte estable hoy es gobierno administrativo (usuarios, roles, permisos, módulos).”

## Min 6–8 — System + Audit + Observabilidad

Recorrer:
- `/system/health`
- `/system/logs`
- `/system/audit`
- `/audit/events`

Mensaje recomendado:
- “Priorizamos trazabilidad, auditoría y diagnóstico antes de habilitar escrituras masivas.”

## Min 8–12 — Módulos con límites operativos (mostrar con etiqueta explícita)

Mostrar **sin vender como productivo completo**:
- Drive/Cloud (`/cloud`, `/cloud/drive`): read-only + controlled.
- URL Locator/Landing/Browser Analytics: read-only/dry-run/controlled según ruta y flag.
- CRM/Campaigns/Reports/Workflow: mezcla de read-only, dry-run y controlled.
- AI: asistencia/dry-run/controlada, no autónoma.

Frase recomendada:
- “Visible en UI no equivale a ejecución productiva real; la activación depende de permisos + flags.”

## Min 12–15 — Flags, seguridad y cierre

- Explicar que `.env.example` trae defaults seguros (`false`) para integraciones y escrituras sensibles.
- Cerrar repitiendo la frase obligatoria.

## 3) Qué no mostrar (prohibido en demo)

- Datos reales de clientes/PII.
- Secretos, credenciales o valores sensibles de `.env` real.
- AWS/S3 real activo.
- SMTP real o envíos reales externos.
- Proveedor IA externo activo.
- Billing productivo.
- Workers/cron productivos activos.

## 4) Checklist de screenshots seguros

Antes de capturar:
- [ ] Usar entorno demo/local aislado.
- [ ] Confirmar que no hay datos reales en tablas mostradas.
- [ ] Confirmar que no hay secretos visibles (tokens, keys, hostnames internos sensibles, correos reales).
- [ ] Verificar flags sensibles en `false` (S3/SMTP/AI/workflow write/export write/etc.).
- [ ] Evitar mostrar `.env` real, paneles cloud reales o bandejas de correo reales.

Capturas recomendadas:
- [ ] Login (`/login`) con cuenta de prueba.
- [ ] Dashboard (`/dashboard`) sin métricas sensibles.
- [ ] Core Admin (`/tenants`, `/users`, `/roles`, `/permissions`, `/modules`).
- [ ] System/Audit (`/system/health`, `/system/logs`, `/system/audit`, `/audit/events`).
- [ ] 1 ejemplo de módulo read-only.
- [ ] 1 ejemplo dry-run.
- [ ] 1 ejemplo controlled explicando flag/permisos.

Después de capturar:
- [ ] Revisar metadatos/miniaturas para evitar exposición accidental.
- [ ] Guardar en carpeta de demo (no mezclar con evidencias productivas).

## 5) Guía de discurso para no sobreprometer

- Decir “read-only” cuando solo hay consulta.
- Decir “dry-run” cuando solo hay simulación.
- Decir “controlled” cuando depende de flags/permisos para ejecutar.
- No usar lenguaje de “SaaS público listo” o “producción completa” para módulos condicionados.

## 6) Verificación mínima pre-demo

Comandos base:

```bash
composer dump-autoload
php -l routes/web.php
php -l scripts/smoke-check.php
composer smoke
```

Si alguna validación no puede correr por entorno, documentar el motivo en la evidencia de demo.

## 7) Notas operativas de seguridad

- Mantener defaults seguros del repositorio (safe-by-default).
- No habilitar integraciones externas durante la demo técnica.
- No ejecutar workers/cron productivos como parte del recorrido.
- No usar ni solicitar datos reales para “probar” módulos.
