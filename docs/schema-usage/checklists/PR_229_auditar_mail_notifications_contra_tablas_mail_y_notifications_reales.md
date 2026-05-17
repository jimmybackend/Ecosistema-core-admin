# Seguimiento PR #229 — Auditar Mail/Notifications contra tablas mail_* y notifications_* reales

## 1. Alcance ejecutado
- [x] Repositorio confirmado: `jimmybackend/Ecosistema-core-admin`
- [x] Fuente DB real usada: `adbbmis1_eco.sql`
- [x] Tablas del contrato revisadas
- [x] Archivos/rutas inspeccionados
- [x] No se modificó otro repositorio

## 2. Tablas y campos auditados
| Tabla real | Campos revisados | CRUD detectado | Archivos donde se usa | Estado |
|---|---|---|---|---|
| `notifications_templates` | `tenant_id`, `channel_id`, `subject`, `body`, `variables_json`, `is_active` | SELECT | `app/Core/MailNotifications/*Repository.php`, `*DryRunService.php` | Corregido |
| `notifications_channels` | `id`, `code`, `name` | JOIN SELECT | `app/Core/MailNotifications/*Repository.php`, `*DryRunService.php` | Corregido |
| `notifications_queue` | `tenant_id`, `user_id`, `channel_id`, `template_id`, `module_code`, `entity_table`, `entity_id`, `title`, `body`, `payload_json`, `status`, `scheduled_at` | INSERT/SELECT | `app/Core/MailNotifications/EcosistemaSendNotificationRepository.php`, `EcosistemaNotificationQueueRepository.php` | Corregido |
| `mail_messages` | `tenant_id`, `mailbox_id`, `folder_id`, `user_id`, `message_uuid`, `direction`, `mail_scope`, `from_address`, `to_addresses`, `subject`, `body_text`, `body_html` | INSERT/SELECT/UPDATE | `app/Core/Mail/MailMessageRepository.php`, `app/Core/MailNotifications/EcosistemaSendNotificationRepository.php`, `...Service.php` | Corregido |
| `mail_delivery_logs` | `tenant_id`, `message_id`, `smtp_account_id`, `provider`, `status`, `response_code`, `response_message`, `attempted_at` | INSERT | `app/Core/MailNotifications/EcosistemaSendNotificationRepository.php` | OK |
| `mail_smtp_accounts` | `tenant_id`, `mailbox_id`, `status`, `host_out`, `port_out`, `ssl_out`, `username`, `password_encrypted` | SELECT | `app/Core/MailNotifications/EcosistemaSendNotificationRepository.php` | OK |

## 3. Hallazgos encontrados
| Severidad | Archivo | Función/ruta | Tabla | Campo | Problema | Acción tomada |
|---|---|---|---|---|---|---|
| Alta | `app/Core/MailNotifications/EcosistemaNotificationTemplateRepository.php` | `listTemplates`, `findTemplate` | `notifications_channels` | `tenant_id` | Columna inexistente usada en JOIN | JOIN corregido |
| Alta | `app/Core/MailNotifications/EcosistemaSendNotificationRepository.php` | `findActiveTemplate` | `notifications_channels` | `tenant_id` | Columna inexistente usada en JOIN | JOIN corregido |
| Alta | `app/Core/MailNotifications/EcosistemaSendNotificationDryRunService.php` | `findTemplate` | `notifications_channels` | `tenant_id` | Columna inexistente usada en JOIN | JOIN corregido |
| Alta | `app/Core/MailNotifications/EcosistemaSendNotificationService.php` | `sendControlled` | `notifications_queue` | `status` | Valor `blocked` fuera de enum real | Sustituido por `pending`/`canceled` |
| Alta | `app/Core/MailNotifications/EcosistemaSendNotificationService.php` | `sendControlled` | `mail_messages` | `mail_scope` | Valor `transactional` fuera de enum real | Sustituido por `system` |
| Media | `app/Core/MailNotifications/EcosistemaSendNotificationService.php` | `sendControlled` | `mail_messages` | `to_addresses` | Se insertaba string en campo JSON | Ahora se serializa JSON array |

## 4. Cambios realizados
- [x] Repositories corregidos si usaban columnas inexistentes
- [x] Services corregidos si enviaban payload incorrecto
- [ ] Routes corregidas si faltaba sesión/permiso/CSRF/tenant
- [ ] Views corregidas si exponían campos sensibles
- [ ] Smoke-check/schema-usage actualizado si aplica
- [x] Documentación `docs/schema-usage/` actualizada

## 5. Campos obligatorios de INSERT verificados
| Tabla | Campos mínimos reales | ¿El código los llena? | Fuente del valor | Observación |
|---|---|---|---|---|
| `notifications_queue` | `tenant_id`, `channel_id`, `title`, `body` | Sí | contexto + template sanitizado | `status` normalizado a enum real |
| `mail_messages` | `tenant_id`, `mailbox_id`, `user_id`, `message_uuid`, `direction`, `from_address`, `to_addresses` | Sí | sesión/contexto + smtp + uuid interno | `to_addresses` ahora JSON válido |
| `mail_delivery_logs` | `tenant_id`, `message_id` | Sí | contexto + ID generado | Sin cambios requeridos |

## 6. Reglas tenant/user verificadas
- [x] `tenant_id` se toma de sesión/contexto validado cuando aplica
- [x] `user_id`/`owner_user_id`/`created_by_user_id` no se aceptan libremente desde request cuando aplica
- [x] Lecturas administrativas filtran por tenant cuando la tabla es tenant-aware
- [x] Escrituras administrativas llenan tenant desde contexto seguro

## 7. Campos sensibles revisados
- [x] No se imprimen hashes completos
- [x] No se imprimen tokens completos
- [x] No se imprime `s3_key`, rutas internas o secretos
- [x] JSON sensible se muestra como preview, máscara o `*_present`

## 8. Validaciones ejecutadas
- [x] `composer dump-autoload`
- [x] `php -l routes/web.php`
- [x] `php -l scripts/smoke-check.php`
- [x] `composer smoke`
- [ ] `composer schema:usage` si existe

## 9. Resultado
- Estado: `Go con advertencias`
- Warnings aceptados: `composer schema:usage` no existe en este repositorio.
- Pendientes que pasan al backlog: revisión expandida de tablas `mail_*` no referenciadas aún por módulos activos.
- Evidencia principal: `docs/schema-usage/mail_notifications_pr229_audit.md`
