# Seguimiento PR #236 — Auditar AI/VitaOS/Chat/Knowledge/Documents contra tablas reales

## 1. Alcance ejecutado
- [x] Repositorio confirmado: `jimmybackend/Ecosistema-core-admin`
- [x] Fuente DB real usada: `adbbmis1_eco.sql`
- [x] Tablas del contrato revisadas
- [x] Archivos/rutas inspeccionados
- [x] No se modificó otro repositorio

## 2. Tablas y campos auditados
| Tabla real | Campos revisados | CRUD detectado | Archivos donde se usa | Estado |
|---|---|---|---|---|
| `os_ai_proposals` | `proposal_id`, `boot_id`, `tenant_id`, `user_id`, `created_unix`, `proposal_type`, `summary`, `rationale`, `risk_level`, `benefit_level`, `requires_human_confirmation`, `status` | INSERT | `app/Core/Ai/EcosistemaAiAssistanceRepository.php` | Corregido |
| `service_event_logs` | `tenant_id`, `source_table`, `source_id` | SELECT | `app/Core/Ai/EcosistemaAiCampaignInsightDryRunRepository.php` | Corregido |
| `chat_threads` | inventario de columnas | Documentación | `docs/project/ECOSISTEMA_AI_ASSISTANCE_SCHEMA_INVENTORY.md` | Corregido |
| `chat_messages` | inventario de columnas | Documentación | `docs/project/ECOSISTEMA_AI_ASSISTANCE_SCHEMA_INVENTORY.md` | Corregido |

## 3. Hallazgos encontrados
| Severidad | Archivo | Función/ruta | Tabla | Campo | Problema | Acción tomada |
|---|---|---|---|---|---|---|
| Alta | `app/Core/Ai/EcosistemaAiAssistanceRepository.php` | `insertProposal` | `os_ai_proposals` | `proposal_id` | INSERT sin campo mínimo obligatorio real | Se agregó `proposal_id` al INSERT y se genera valor seguro si no viene en payload interno. |
| Alta | `app/Core/Ai/EcosistemaAiAssistanceRepository.php` | `insertProposal` | `os_ai_proposals` | `boot_id` | Bind como entero, tipo real varchar(64) | Se cambió a string compatible con contrato real. |
| Alta | `app/Core/Ai/EcosistemaAiCampaignInsightDryRunRepository.php` | `countCampaignEvents` | `service_event_logs` | `resource_type`, `resource_id` | Columnas inexistentes | Reemplazo por `source_table` y `source_id`. |
| Media | `resources/views/pages/ai/assist-result.php` | vista resultado AI | `os_ai_proposals` | `proposal_id` | Se mostraba casteado a int aunque es varchar | Se presenta como string escapado. |
| Media | `docs/project/ECOSISTEMA_AI_ASSISTANCE_SCHEMA_INVENTORY.md` | sección tablas chat | `chat_threads`, `chat_messages` | múltiples | Columnas documentadas no coinciden con contrato real | Se actualizó inventario documental. |

## 4. Cambios realizados
- [x] Repositories corregidos si usaban columnas inexistentes
- [x] Services corregidos si enviaban payload incorrecto
- [x] Routes corregidas si faltaba sesión/permiso/CSRF/tenant
- [x] Views corregidas si exponían campos sensibles
- [x] Smoke-check/schema-usage actualizado si aplica
- [x] Documentación `docs/schema-usage/` actualizada

## 5. Campos obligatorios de INSERT verificados
| Tabla | Campos mínimos reales | ¿El código los llena? | Fuente del valor | Observación |
|---|---|---|---|---|
| `os_ai_proposals` | `proposal_id`, `boot_id`, `created_unix`, `proposal_type`, `summary`, `rationale`, `risk_level`, `benefit_level`, `requires_human_confirmation`, `status` | Sí | contexto interno + payload controlado + generación segura (`proposal_id`) | `tenant_id` y `user_id` llegan por contexto de sesión en servicio, no por request libre. |

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
- Warnings aceptados: `composer schema:usage` no existe en `composer.json`.
- Pendientes que pasan al backlog: revisar referencias no canónicas fuera del alcance AI/Docs indicados en este PR.
- Evidencia principal: `docs/schema-usage/PR_236_ai_vitaos_chat_knowledge_documents_auditoria.md`
