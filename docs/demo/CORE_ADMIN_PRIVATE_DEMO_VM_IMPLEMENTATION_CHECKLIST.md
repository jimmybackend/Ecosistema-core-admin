# Core Admin — Checklist formal de implementación VM para demo privada controlada

- **Fecha de actualización (UTC):** 2026-05-19  
- **Repositorio:** `jimmybackend/Ecosistema-core-admin`  
- **Entorno objetivo:** VM Google para pruebas/demo privada técnica interna  
- **Estado consolidado sugerido:** **Implementación lograda con pendientes controlados**

## 1) Preparación de VM

- [x] VM Google activa y accesible para operación técnica controlada.
- [x] Acceso operativo validado para ejecutar despliegue y verificaciones.
- [ ] Reservar IP estática para la VM o documentar procedimiento de actualización de acceso Remote MySQL ante cambio de IP.

## 2) Actualización de repositorio

- [x] Repositorio actualizado a rama `main`.
- [x] Referencia de actualización aplicada: PR #258 (`3e5e959` merge commit).
- [x] Estado de trabajo validado en base limpia para pruebas técnicas de login/dashboard.

## 3) Dependencias / Composer

- [x] Dependencias Composer instaladas/actualizadas en la VM.
- [x] `composer validate --no-check-publish` ejecutado correctamente.
- [x] `composer smoke` ejecutado sin fallos críticos.
- [x] `composer vm:check` ejecutado en estado OK.

## 4) Configuración Nginx + PHP-FPM

- [x] Nginx configurado apuntando a `public/` (`root /var/www/ecosistema-core-admin/public`).
- [x] Integración FastCGI validada con socket `unix:/run/php/php8.5-fpm.sock`.
- [x] PHP-FPM 8.5 activo y atendiendo como usuario/grupo operativo esperado (`www-data`).

## 5) Configuración segura de `.env`

- [x] `.env` presente exclusivamente en servidor (sin exponer secretos en repositorio).
- [x] Propietario/grupo corregidos para lectura de runtime web (`owner: jimmybackend`, `group: www-data`).
- [x] Permisos corregidos a `chmod 640` para permitir lectura por PHP-FPM sin sobreexposición.
- [x] Causa raíz documentada: con `chmod 600`, PHP-FPM no leía `.env`, derivando en fallback de conexión DB incorrecta y error `PDOException 2002 Connection refused`.
- [ ] Rotar `DB_PASSWORD`, `APP_KEY` y `CORE_REGISTRATION_INVITE_CODE` antes de preproducción/producción por exposición durante operación manual.

## 6) DB remota / Remote MySQL

- [x] Conectividad a DB remota validada desde la VM.
- [x] Remote MySQL ajustado para permitir acceso desde IP pública de la VM.
- [x] `composer schema:usage` ya conecta contra DB real del entorno.
- [ ] Documentar y automatizar control operativo ante cambio de IP pública (o migrar a IP estática).

## 7) Validación de login

- [x] `GET /login` local validado (`HTTP 200`).
- [x] `GET /login` público validado (`HTTP 200`).
- [x] `POST /login` validado con respuesta `HTTP 302` y `Location: /dashboard`.
- [x] AuthService validado funcionalmente en flujo real de autenticación.

## 8) Validación de dashboard

- [x] `GET /dashboard` sin sesión redirige a `/login` (`HTTP 302`).
- [x] Login real en navegador ejecutado exitosamente.
- [x] Dashboard visible tras autenticación exitosa.

## 9) Validaciones Composer / scripts

- [x] `php -l routes/web.php`
- [x] `php -l scripts/smoke-check.php`
- [x] `php -l scripts/schema-compatibility-check.php`
- [x] `php -l scripts/schema-usage-check.php`
- [x] `php -l scripts/vm-runtime-check.php`
- [x] `composer validate --no-check-publish`
- [x] `composer smoke` (críticos fallidos: `0`)
- [x] `composer vm:check` (OK)

## 10) Pendientes conocidos

- [ ] Resolver o ajustar las 5 incompatibilidades reportadas por `composer schema:usage`:
  - `mail_messages.status`
  - `os_ai_proposals.id`
  - `os_ai_proposals.module_code`
  - `os_ai_proposals.entity_table`
  - `os_ai_proposals.entity_id`
- [ ] Rotar secretos expuestos en operación manual (`DB_PASSWORD`, `APP_KEY`, `CORE_REGISTRATION_INVITE_CODE`).
- [ ] Reservar IP estática o formalizar actualización de Remote MySQL ante cambio de IP.
- [ ] Definir siguiente ronda de QA manual funcional por módulos.

> Nota de impacto: las 5 incompatibilidades actuales de `schema:usage` **no bloquearon** login ni dashboard en la validación real de VM.

## 11) Criterio Go / No-Go para demo privada

### Estado sugerido

**GO condicionado para demo privada técnica / validación interna.**

### Fundamento del GO condicionado

- Login funcional validado (incluyendo `POST /login` con `302` a `/dashboard`).
- Dashboard funcional y visible en navegador.
- Entorno web operativo (Nginx + PHP-FPM 8.5 + root a `public/`).
- Conexión a DB remota operativa desde VM.
- Flags de riesgo continúan bajo control y no se habilita producción.

### Condiciones obligatorias

- No usar este estado como producción.
- Rotar secretos (`DB_PASSWORD`, `APP_KEY`, `CORE_REGISTRATION_INVITE_CODE`) antes de cualquier paso de preproducción/producción.
- Mantener trazabilidad de las 5 incompatibilidades conocidas de `schema:usage` hasta su cierre.

---

## 12) Referencias cruzadas

- `docs/demo/CORE_ADMIN_PRIVATE_DEMO_READINESS_MASTER.md`
- `docs/project/CORE_ADMIN_DEMO_GO_NO_GO.md`
- `docs/ops/ECOSISTEMA_OPERATIONAL_READINESS_VERIFICATION.md`
- `docs/security/CORE_ADMIN_PREPRODUCTION_HARDENING_CHECKLIST.md`

- Update 2026-05-19: `mail_smtp_accounts` ahora es editable desde UI controlada (`/mail/smtp-accounts*`) solo para usuarios autenticados con `mail.manage`; no se insertan datos por PR, password SMTP cifrada en `password_encrypted` (independiente del password del panel) y envío real sigue bloqueado por `MAIL_SEND_ENABLED` + `MAIL_ALLOW_TEST_SEND` en `false`.

- Update 2026-05-19 (mailboxes compartidas por tenant): `mail_mailboxes.available_to_everyone` es columna requerida del contrato de esquema y su default operativo debe ser `0`.
- `available_to_everyone = 1` solo habilita compartición dentro del mismo `tenant_id`; no habilita cruce entre tenants y sigue exigiendo permisos/autorización del usuario autenticado.
- Este campo soporta el modelo operativo multiusuario donde usuario de panel puede ser distinto de la mailbox operativa asignada.

