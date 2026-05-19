# ECOSISTEMA SEND NOTIFICATION CONTROLLED

## Objetivo
Habilitar envío controlado de notificaciones por flags, respetando tenant de sesión y bloqueo seguro cuando SMTP real no está disponible.

## Flags requeridas
- `ECOSISTEMA_MAIL_NOTIFICATIONS_ENABLED=true` habilita queue write.
- `ECOSISTEMA_MAIL_SEND_ENABLED=true` habilita send_write.
- `ECOSISTEMA_SMTP_ENABLED=true` habilita smtp_connection.

Si cualquier flag falta, el flujo queda bloqueado de forma segura.

## Seguridad
- No se acepta `tenant_id` desde request.
- Se usa CSRF obligatorio en `POST /mail-notifications/send`.
- No se expone `password_encrypted` ni password SMTP en respuestas/vistas.
- Escrituras controladas con transacción PDO y prepared statements.

## Escrituras permitidas
- `notifications_queue`.
- `mail_messages` sólo cuando flags de envío + smtp están activas.
- `mail_delivery_logs` como traza controlada de preparación de entrega.

No hay envío SMTP directo en request web: se deja en estado `queued` para worker controlado.

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

- Update 2026-05-19 (mailboxes compartidas por tenant): `mail_mailboxes.available_to_everyone` es columna requerida del contrato de esquema y su default operativo debe ser `0`.
- `available_to_everyone = 1` solo habilita compartición dentro del mismo `tenant_id`; no habilita cruce entre tenants y sigue exigiendo permisos/autorización del usuario autenticado.
- Este campo soporta el modelo operativo multiusuario donde usuario de panel puede ser distinto de la mailbox operativa asignada.

