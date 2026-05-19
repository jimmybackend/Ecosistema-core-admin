# Core Admin — Checklist de hardening preproducción (PR #255)

- **Fecha base (UTC):** 2026-05-18
- **Repositorio:** `jimmybackend/Ecosistema-core-admin`
- **Estado objetivo de esta fase:** **Go con advertencias**
- **Límite formal:** **No habilita producción SaaS pública**

## 1) Propósito

Definir una checklist técnica, operativa y auditable para endurecer Core Admin antes de considerar una **producción controlada interna** posterior al piloto, sin activar por defecto integraciones externas reales ni exponer datos sensibles.

## 2) Alcance

Incluye:

- hardening de configuración y variables de entorno;
- sesiones/cookies, CSRF, autenticación y autorización/RBAC;
- aislamiento tenant, logs, privacidad y manejo de errores;
- uploads/downloads, SMTP, AWS/S3, IA externa, workers/cron;
- backups, DB/schema, observabilidad, dependencias y despliegue VM/EC2.

Fuera de alcance:

- producción SaaS pública;
- migraciones o cambios de esquema;
- activación permanente de side effects externos reales;
- inclusión de secretos o datos reales en repositorio/docs.

## 3) Diferencias de etapa: demo vs piloto vs preproducción vs producción

| Etapa | Objetivo | Datos | Integraciones externas | Decisión esperada |
|---|---|---|---|---|
| Demo privada/controlada | Mostrar cobertura funcional guiada | Ficticios/sanitizados | Apagadas/simuladas | Go con advertencias |
| Piloto interno controlado | Operación interna repetible por ventana acotada | Ficticios/sanitizados | Apagadas o controlled | Go con advertencias / No-Go |
| Preproducción hardening | Cerrar brechas técnicas/seguridad/operación | Sintéticos + validación controlada | Activables sólo por prueba controlada con rollback | Go / Go con advertencias / No-Go |
| Producción SaaS pública | Servicio externo continuo | Reales | Habilitadas con controles completos | Go productivo (no aplica en este PR) |

## 4) Checklist de configuración segura

- [ ] `APP_ENV=production` y `APP_DEBUG=false` en entorno candidato.
- [ ] `APP_URL` usa HTTPS en entorno expuesto.
- [ ] Sólo se mantienen puertos y accesos administrativos mínimos (VM/EC2 restringida).
- [ ] Feature flags de riesgo siguen en `false` por defecto y sólo se habilitan con change record.
- [ ] No se commitean archivos `.env` ni credenciales reales.

## 5) Checklist de `.env`

- [ ] `.env.example` y `.env.vm.example` contienen placeholders/no secretos reales.
- [ ] Variables sensibles (`DB_PASSWORD`, `MAIL_PASSWORD`, `AWS_SECRET_ACCESS_KEY`, tokens) se inyectan fuera de git.
- [ ] Se valida consistencia entre `.env`, `config/*.php` y matriz de flags.
- [ ] Se verifica que los toggles `*_WRITE`, `*_ENABLED`, `*_ALLOW_REMOTE_*` estén apagados salvo prueba controlada.
- [ ] Se registra evidencia de revisión sin imprimir valores reales en logs/reportes.

## 6) Checklist de sesiones/cookies

- [ ] `SESSION_SECURE=true` cuando el entorno use HTTPS.
- [ ] `SESSION_SAMESITE=Lax` (o `Strict` si flujo lo permite).
- [ ] `SESSION_IDLE_TIMEOUT` validado contra política interna.
- [ ] Rotación/expiración de sesión comprobada para evitar sesiones huérfanas.
- [ ] No se expone token/hash de sesión en UI, logs ni exportes.

## 7) Checklist de CSRF

- [ ] Formularios administrativos de escritura cubiertos con CSRF.
- [ ] Respuesta 419 validada para requests sin token.
- [ ] Endpoints públicos sólo se mantienen sin CSRF cuando el diseño lo justifica y con mitigaciones antiabuso.
- [ ] Pruebas negativas registradas para bypass de CSRF.

## 8) Checklist de autenticación

- [ ] Login con credenciales nominales internas (sin cuentas compartidas fuera de control).
- [ ] Bloqueos/rate-limit de intentos fallidos verificados.
- [ ] Recuperación/rotación de credenciales documentada para entorno controlado.
- [ ] No existen credenciales hardcodeadas en repositorio o scripts.

## 9) Checklist de autorización / RBAC

- [ ] Rutas críticas administrativas requieren permisos explícitos (`requirePermission`).
- [ ] Se valida principio de menor privilegio por rol.
- [ ] Usuario sin permiso recibe 403 consistente.
- [ ] Cambios de rol/permisos quedan auditados.

## 10) Checklist de tenant isolation

- [ ] Operaciones admin usan tenant desde sesión/autenticación, no desde input manipulable.
- [ ] Rutas públicas con tenant default tienen controles y límites explícitos documentados.
- [ ] No hay mezcla de datos entre tenants en listados/detalles/exportes.
- [ ] Se ejecutan pruebas negativas de cruce de tenant.

## 11) Checklist de logs y privacidad

- [ ] Logs estructurados sin PII completa ni secretos.
- [ ] Redacción/enmascaramiento activo para campos sensibles.
- [ ] Políticas de retención y acceso a logs definidas para entorno controlado.
- [ ] Evidencias de auditoría (`core_audit` y enlaces) disponibles para acciones críticas.

## 12) Checklist de errores y stack traces

- [ ] Errores 4xx/5xx no exponen stack trace, SQL ni secretos.
- [ ] Manejo seguro para 403/404/419/500 verificado manualmente.
- [ ] Mensajes de error al usuario final son mínimos y no sensibles.

## 13) Checklist de uploads/downloads

- [ ] Uploads remotos desactivados por defecto (`CLOUD_ALLOW_UPLOADS=false`, equivalentes drive).
- [ ] Downloads remotos controlados/desactivados por defecto.
- [ ] Restricciones de tamaño/extensión validadas.
- [ ] URLs firmadas deshabilitadas salvo prueba puntual controlada.
- [ ] Sin exposición de paths internos o metadatos sensibles.

## 14) Checklist de correo SMTP

- [ ] `MAIL_SEND_ENABLED=false` y `ECOSISTEMA_SMTP_ENABLED=false` por defecto.
- [ ] `MAIL_ALLOW_TEST_SEND` sólo para pruebas controladas, temporal y trazable.
- [ ] No se usan cuentas SMTP productivas en demo/piloto/preprod controlada.
- [ ] Se verifica que previews/dry-run no disparen envío real.

## 15) Checklist de AWS/S3

- [ ] `CLOUD_S3_ENABLED=false` y `ECOSISTEMA_DRIVE_AWS_ENABLED=false` por defecto.
- [ ] Claves AWS no aparecen en repo, terminal compartida ni docs.
- [ ] Acciones remotas y signed URLs deshabilitadas por defecto.
- [ ] Si se prueba activación puntual, existe plan de rollback y evidencia de apagado posterior.

## 16) Checklist de IA externa

- [ ] `ECOSISTEMA_AI_PROVIDER_ENABLED=false` por defecto.
- [ ] `ECOSISTEMA_AI_WRITE_PROPOSALS=false` salvo test controlado.
- [ ] No se envía PII/secretos/prompts sensibles a proveedores externos.
- [ ] Resultado IA se considera asistido, no fuente autónoma para acciones críticas.

## 17) Checklist de workers / cron

- [ ] Workers reales con side effects permanecen desactivados.
- [ ] Tareas de cron sólo en dry-run durante fase controlada.
- [ ] Runbook de activación/rollback de jobs documentado antes de cualquier habilitación.
- [ ] Se valida idempotencia y manejo de reintentos antes de ejecución real.

## 18) Checklist de backups

- [ ] Backups de DB y artefactos críticos definidos y ejecutables.
- [ ] Restore probado en entorno controlado no productivo.
- [ ] RPO/RTO internos definidos para etapa preproducción.
- [ ] Evidencia de simulacro de recuperación registrada.

## 19) Checklist de DB / schema

- [ ] No se crean migraciones ni cambios de esquema en este PR.
- [ ] Compatibilidad de código contra esquema real documentada.
- [ ] `composer schema:usage` ejecutado; warning por DB no disponible se clasifica como controlado si aplica.
- [ ] Cualquier desviación de tablas/campos abre pendiente de backlog con dueño.

## 20) Checklist de observabilidad

- [ ] Métricas mínimas definidas (errores, latencia, disponibilidad, throughput).
- [ ] Correlación request/session/tenant en logs operativos.
- [ ] Alertas básicas para incidentes de seguridad y degradación crítica.
- [ ] Runbook de triage y escalamiento interno disponible.

## 21) Checklist de dependencias

- [ ] `composer.lock` consistente y sin cambios no planificados.
- [ ] Dependencias con riesgo conocido quedan registradas y con plan de mitigación.
- [ ] No se agregan librerías externas sin revisión de seguridad/licencia.

## 22) Checklist de despliegue VM/EC2

- [ ] Entorno objetivo restringido (allowlist/VPN/túnel), no abierto públicamente.
- [ ] Usuario operativo no-root, acceso SSH por llave y hardening de host básico.
- [ ] `.env` local preparado sin secretos en repo.
- [ ] Validaciones técnicas ejecutadas antes de exponer entorno a stakeholders.
- [ ] Cierre post-ejecución: apagar accesos temporales, limpiar artefactos y registrar decisión.

## 23) Criterios Go / Go con advertencias / No-Go

### Go

- Checklist completada con evidencia auditable.
- Validaciones técnicas en verde.
- Sin fugas de secretos/PII ni bypass de permisos/tenant.

### Go con advertencias (esperado en esta fase)

- Sin bloqueantes críticos, con advertencias menores controladas y plan de cierre.
- Puede incluir warning controlado de `composer schema:usage` por falta de DB de verificación en entorno aislado.

### No-Go

- Cualquier exposición de secretos/PII real.
- Integraciones reales activas sin control/rollback.
- Brechas críticas de RBAC, aislamiento tenant o errores con stack trace sensible.

## 24) Pendientes para backlog

1. cerrar evidencia de `schema:usage` en entorno con DB de verificación disponible;
2. formalizar threat model para rutas públicas (`/u/{slug}`, `/l/{slug}` y submit);
3. fortalecer pruebas negativas tenant-end-to-end por módulo crítico;
4. consolidar alertas operativas mínimas y tiempos de respuesta internos;
5. definir plan de activación gradual de SMTP/S3/IA/workers con rollback por integración.

## 25) Declaración final

Esta checklist habilita una evaluación de **preproducción controlada**, no una salida a **producción SaaS pública**.


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

## Referencia cruzada de implementación VM (2026-05-19)

Para evidencia del estado técnico real alcanzado en VM controlada (incluyendo pendiente de rotación de secretos y pendientes de `schema:usage`), ver:

- `docs/demo/CORE_ADMIN_PRIVATE_DEMO_VM_IMPLEMENTATION_CHECKLIST.md`

## SMTP por usuario/mailbox: administrado vs propio
- SMTP global por `.env` se mantiene como fallback controlado.
- SMTP por mailbox/usuario se persiste en `mail_smtp_accounts`.
- `password_encrypted` se cifra y nunca se muestra en UI ni logs.
- `core_users.password_hash` no se reutiliza para SMTP.
- El correo administrado `username+id@dominio` requiere provisión real en servidor SMTP externo/cPanel.

## SMTP por usuario/mailbox: administrado vs propio
- SMTP global por `.env` se mantiene como fallback controlado.
- SMTP por mailbox/usuario se persiste en `mail_smtp_accounts`.
- `password_encrypted` se cifra y nunca se muestra en UI ni logs.
- `core_users.password_hash` no se reutiliza para SMTP.
- El correo administrado `username+id@dominio` requiere provisión real en servidor SMTP externo/cPanel.

- Update 2026-05-19: `mail_smtp_accounts` ahora es editable desde UI controlada (`/mail/smtp-accounts*`) solo para usuarios autenticados con `mail.manage`; no se insertan datos por PR, password SMTP cifrada en `password_encrypted` (independiente del password del panel) y envío real sigue bloqueado por `MAIL_SEND_ENABLED` + `MAIL_ALLOW_TEST_SEND` en `false`.
