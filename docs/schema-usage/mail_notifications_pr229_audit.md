# PR #229 — Auditoría Mail/Notifications vs `adbbmis1_eco.sql`

## Resumen de hallazgos y correcciones

| Archivo | Función | Query | Hallazgo | Corrección |
|---|---|---|---|---|
| `app/Core/MailNotifications/EcosistemaNotificationTemplateRepository.php` | `listTemplates`, `findTemplate` | `notifications_templates` JOIN `notifications_channels` | Se usaba `notifications_channels.tenant_id` en el JOIN, columna inexistente en tabla real. | Se removió el predicado `nc.tenant_id=nt.tenant_id`, manteniendo JOIN por `nc.id=nt.channel_id`. |
| `app/Core/MailNotifications/EcosistemaSendNotificationRepository.php` | `findActiveTemplate` | `notifications_templates` JOIN `notifications_channels` | Mismo uso de columna inexistente `notifications_channels.tenant_id`. | JOIN corregido a `nc.id=nt.channel_id`. |
| `app/Core/MailNotifications/EcosistemaSendNotificationDryRunService.php` | `findTemplate` | `notifications_templates` JOIN `notifications_channels` | Mismo uso de columna inexistente `notifications_channels.tenant_id`. | JOIN corregido a `nc.id=nt.channel_id`. |
| `app/Core/MailNotifications/EcosistemaSendNotificationService.php` | `sendControlled` | INSERT `notifications_queue` | Se enviaba estado `blocked`, valor no permitido por enum real (`pending`,`processing`,`sent`,`failed`,`canceled`). | Se reemplazó por `canceled` cuando flags bloquean envío, y `pending` cuando está habilitado. |
| `app/Core/MailNotifications/EcosistemaSendNotificationService.php` | `sendControlled` | INSERT `mail_messages` | Se enviaba `mail_scope='transactional'`, valor no permitido por enum real. | Se cambió a `mail_scope='system'` (valor real permitido). |
| `app/Core/MailNotifications/EcosistemaSendNotificationService.php` | `sendControlled` | INSERT `mail_messages` | `to_addresses` (json) recibía string plano. | Se serializa como JSON array (`json_encode([$toAddress])`). |

## Reglas tenant/user verificadas
- En lecturas y escrituras de `notifications_templates`, `notifications_queue`, `mail_messages`, `mail_delivery_logs`, `mail_smtp_accounts` se mantiene `tenant_id` desde contexto de sesión/middleware y no desde payload libre.
- `recipient_user_id` se valida contra `core_users` filtrado por `tenant_id`.

## Campos sensibles
- En vistas/servicios auditados no se imprimen `password_encrypted` ni tokens completos; se usa enmascarado/previews cuando aplica.
