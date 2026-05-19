# CORE ADMIN — Cierre Go/No-Go para demo privada controlada (PR #224)

Fecha de corte: 2026-05-17.
Repositorio: `jimmybackend/Ecosistema-core-admin`.

## 1) Objetivo y alcance

Este documento formaliza la decisión de cierre **Go/No-Go** de Core Admin para **demo privada controlada**, usando como fuentes de verdad la documentación y artefactos técnicos del repositorio.

Este cierre **no** declara producción SaaS.

Fuentes base de evaluación:
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
- `docs/project/CORE_ADMIN_POST_208_VERIFICATION_REPORT.md`

---

## 2) Matriz de criterios Go/No-Go

| Criterio | Estado | Evidencia | Responsable | Siguiente acción |
|---|---|---|---|---|
| Smoke (`composer smoke`) | Go con advertencia | El smoke está definido como validación mínima oficial y se ejecuta en este cierre; como este PR es documental, no altera lógica PHP, pero sigue siendo obligatorio registrar resultado. | Ingeniería Core Admin | Mantener smoke como gate previo a cada demo y adjuntar salida en evidencia interna de corrida. |
| Lint sintáctico PHP | Go | `php -l routes/web.php` y `php -l scripts/smoke-check.php` validan sintaxis de rutas/script involucrados en verificación demo. | Ingeniería Core Admin | Conservar chequeo sintáctico en checklist pre-demo junto a smoke. |
| DB demo | Go con advertencia | La documentación exige DB de demo/no productiva y dataset sintético para pruebas; no se declara certificación de producción. | Operaciones + Ingeniería | Ejecutar reset de dataset antes de cada sesión y registrar origen de datos sintéticos usados. |
| Auth (login/logout/dashboard) | Go | Estado operativo base reportado para Core/Auth y uso interno administrativo. | Ingeniería Core Admin | Mantener prueba manual de login/dashboard/logout en cada ensayo de demo. |
| Permisos / RBAC | Go con advertencia | RBAC administrativo documentado; parte de módulos está en modo read-only/dry-run/controlled y depende de contexto/flags. | Ingeniería + Seguridad | Ejecutar caso permitido/denegado antes de demo y no ampliar permisos fuera del recorrido acordado. |
| Rutas públicas | Go con advertencia | Existen rutas públicas (`/u/{slug}`, `/l/{slug}`, submit) condicionadas por flags y política de hardening. | Seguridad + Ingeniería | Mantener defaults bloqueantes y monitoreo de exposición; no habilitar públicamente sin hardening adicional. |
| Flags críticas | Go | Defaults seguros en `false` para SMTP, AWS/S3, IA, workflows y escrituras sensibles. | Seguridad + DevOps | Verificar `.env` efectivo del entorno demo contra matriz de defaults antes de presentar. |
| PII / secretos | Go con advertencia | Documentación exige no exponer secretos ni PII real; módulos con potencial PII existen pero con operación real controlada por flags. | Seguridad + QA | Ejecutar revisión de pantallas/logs/exportes para evitar leaks antes de compartir demo. |
| Demo dataset | Go con advertencia | Requisito explícito de datos ficticios/anonimizados para recorridos de demo. | QA + Operaciones | Revalidar que no existan correos/teléfonos/documentos reales en tablas y reportes mostrados. |
| QA manual | Go con advertencia | Existe checklist formal de demo readiness con pasos manuales y reglas de No-Go si hay dudas de seguridad/control. | QA + Ingeniería | Ejecutar checklist end-to-end y dejar evidencia de los ítems críticos completados. |
| Documentación de estado | Go | Hay trazabilidad consolidada: estado por módulo, matriz extendida y reporte post-208; este documento cierra decisión para PR #224. | Owner técnico documental | Mantener sincronía entre README, post-208 y este cierre Go/No-Go. |

---

## 3) Decisión final sugerida

## ✅ Decisión para demo privada

**GO condicionado** para demo privada controlada de Core Admin.

Condiciones mínimas obligatorias para sostener el GO:
1. Mantener flags sensibles en `false` por defecto.
2. Usar únicamente datos sintéticos/no sensibles.
3. Ejecutar smoke + lint + checklist manual previo a la sesión.
4. Respetar narrativa técnica: no presentar módulos read-only/dry-run/controlled como productivos plenos.

## ❌ Decisión para producción SaaS

**NO-GO** para producción SaaS general en este cierre.

Motivos principales:
- Estado mixto de múltiples módulos (read-only/dry-run/controlled/roadmap).
- Integraciones externas y escrituras sensibles desactivadas por diseño seguro.
- Workers/colas productivas completas aún no activas como operación end-to-end.

---

## 4) Riesgos aceptados (para demo privada)

1. Riesgo de confusión narrativa entre “demo técnica” y “producto productivo completo”.
   - Mitigación: guion explícito de estados por módulo.
2. Riesgo residual de exposición en rutas públicas condicionadas.
   - Mitigación: flags bloqueantes por defecto + scope de demo interno controlado.
3. Riesgo de mostrar datos no adecuados en pantallas/logs/reportes.
   - Mitigación: dataset sintético y revisión previa de evidencias visuales.
4. Riesgo de interpretar presencia de cron/jobs como madurez productiva de workers.
   - Mitigación: declarar explícitamente estado actual de workers/colas como no productivo completo.

## 5) Riesgos bloqueantes (gatillos de No-Go)

Se declara **No-Go inmediato** para demo si ocurre cualquiera de los siguientes:
1. Falla crítica en `composer smoke` sin mitigación documentada.
2. Evidencia de secretos/PII reales en UI, logs, exportes o consola compartida.
3. Flags sensibles activadas sin justificación ni control de rollback.
4. Inconsistencias visibles de autenticación/permisos en el recorrido acordado.
5. Caída de rutas críticas de acceso demo (`/login`, `/dashboard` o módulos base del recorrido).

---

## 6) Evidencia de validación mínima de este cierre

Comandos requeridos para PR #224:
- `composer dump-autoload`
- `php -l routes/web.php`
- `php -l scripts/smoke-check.php`
- `composer smoke`

Resultado esperado:
- Si alguno falla por entorno, registrar advertencia y causa.
- Si falla por error real de aplicación, el cierre pasa a No-Go hasta corregir.

---

## 7) Trazabilidad cruzada

- Estado de módulos y límites reales: `docs/project/CORE_ADMIN_MODULE_STATUS.md` y `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`.
- Mapa de rutas/servicios/tablas: `docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md`.
- Gobernanza de flags/permisos/seguridad: `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`.
- Defaults seguros: `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md` y `.env.example`.
- Estado workers/cron: `docs/ops/WORKERS_CRON_CURRENT_STATE.md`.
- Consolidado de fase previa: `docs/project/CORE_ADMIN_POST_208_VERIFICATION_REPORT.md`.



## Actualización de ejecución real en VM controlada (2026-05-19)

- Repo actualizado y limpio en `main` (commit `836d0db`, PR #257).
- Nginx y PHP-FPM operativos (`fastcgi_pass unix:/run/php/php8.5-fpm.sock`).
- `GET /login` validado en local y público con `HTTP 200`.
- `POST /login` validado con `HTTP 302 Found` y `Location: /dashboard`.
- Dashboard confirmado visible en navegador.
- DB remota `adbbmis1_eco` autorizada por IP pública de la VM en Remote MySQL / Manage Access Hosts.
- Causa raíz del fallo inicial: `.env` ilegible para `www-data` por `chmod 600`.
- Corrección aplicada: owner deploy user + group `www-data` + `chmod 640` para `.env`.
- Pendiente obligatorio preprod/prod: rotar `DB_PASSWORD`, `APP_KEY` y `CORE_REGISTRATION_INVITE_CODE`.
- `composer schema:usage` en validación real reporta 5 incompatibilidades pendientes (`mail_messages.status`, `os_ai_proposals.id`, `os_ai_proposals.module_code`, `os_ai_proposals.entity_table`, `os_ai_proposals.entity_id`) sin bloquear login.
