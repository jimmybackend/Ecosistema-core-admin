# PR #236 — Auditoría AI/VitaOS/Chat/Knowledge/Documents vs `adbbmis1_eco`

## Evidencia de auditoría y correcciones

| Archivo | Función/área | Query/uso | Tabla(s) | Columnas usadas | Hallazgo | Acción correctiva |
|---|---|---|---|---|---|---|
| `app/Core/Ai/EcosistemaAiAssistanceRepository.php` | `insertProposal` | `INSERT INTO os_ai_proposals (...)` | `os_ai_proposals` | `proposal_id`, `boot_id`, `tenant_id`, `user_id`, `created_unix`, `proposal_type`, `summary`, `rationale`, `risk_level`, `benefit_level`, `requires_human_confirmation`, `status` | Faltaba `proposal_id` obligatorio y `boot_id` se bindeba como entero (schema real es `varchar(64)`) | Se agrega `proposal_id` generado seguro si no existe y `boot_id` string; se retorna `proposal_id` en lugar de `lastInsertId()`. |
| `app/Core/Ai/EcosistemaAiCampaignInsightDryRunRepository.php` | `countCampaignEvents` | `SELECT COUNT(*) ...` | `service_event_logs` | `tenant_id`, `source_table`, `source_id` | Se usaban columnas inexistentes (`resource_type`, `resource_id`) | Se reemplaza por columnas reales del contrato: `source_table` y `source_id`. |
| `resources/views/pages/ai/assist-result.php` | render de resultado | salida HTML | `os_ai_proposals` (campo lógico de retorno) | `proposal_id` | Se casteaba a `int`, incompatible con `proposal_id` varchar(64) | Se muestra como string escapado. |
| `docs/project/ECOSISTEMA_AI_ASSISTANCE_SCHEMA_INVENTORY.md` | inventario documental | documentación | `chat_threads`, `chat_messages` | columnas de ambas tablas | Documentación desalineada (columnas legacy no reales) | Se actualiza inventario a columnas reales del contrato. |

## Verificación de reglas tenant/user
- `tenant_id` en escrituras AI auditadas se deriva del contexto de servicio (`$tenantId`) y no desde request libre.
- Lecturas auditadas sobre tablas tenant-aware revisadas filtran por `tenant_id`.

## Campos sensibles
- No se añadió exposición de hashes/tokens/JSON crudo en vistas de AI.
