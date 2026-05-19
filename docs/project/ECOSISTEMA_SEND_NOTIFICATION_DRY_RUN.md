# ECOSISTEMA_SEND_NOTIFICATION_DRY_RUN

PR #109 agrega simulación de envío de notificación en modo dry-run.

## Rutas
- `GET /mail-notifications/send-dry-run`
- `POST /mail-notifications/send-dry-run` (con CSRF)

## Comportamiento
- Valida `template_id` activo y su canal en `notifications_templates` + `notifications_channels` por `tenant_id` de sesión.
- Valida destinatario por `recipient_user_id` (si pertenece al tenant) o `recipient_email_preview` (email válido).
- Valida `payload_json` seguro (objeto simple con claves permitidas y valores escalares).
- Renderiza `subject_preview` y `body_preview` con variables seguras.
- Devuelve `would_queue=true` y `would_send=true` sólo como simulación.
- No ejecuta inserciones en `notifications_queue` ni `mail_messages`.
- No abre conexión SMTP.

## Seguridad
- Tenant aplicado desde sesión/contexto (`auth_tenant_id`).
- No se acepta `tenant_id` desde request.
- No se exponen secretos ni payloads sensibles sin sanitización.

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
