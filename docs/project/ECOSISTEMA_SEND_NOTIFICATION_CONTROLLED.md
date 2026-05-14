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
