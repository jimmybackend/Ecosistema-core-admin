# Seguimiento PR #234 — Auditar Workflow/Reports contra tablas workflow_* y reports_* reales

## 1. Alcance ejecutado
- [x] Repositorio confirmado: `jimmybackend/Ecosistema-core-admin`
- [x] Fuente DB real usada: `adbbmis1_eco.sql`
- [x] Tablas del contrato revisadas
- [x] Archivos/rutas inspeccionados
- [x] No se modificó otro repositorio

## 2. Tablas y campos auditados
| Tabla real | Campos revisados | CRUD detectado | Archivos donde se usa | Estado |
|---|---|---|---|---|
| `reports_dashboards` | `id, tenant_id, dashboard_key, name, visibility, is_active, created_at` | SELECT | `app/Core/Reports/EcosistemaReportExportRepository.php`, `app/Core/Reports/EcosistemaReportExportDryRunRepository.php` | OK (con control de tenant) |
| `reports_exports` | `tenant_id, report_type, source_id, format, status, requested_by_user_id, requested_at, metadata_json` | INSERT | `app/Core/Reports/EcosistemaReportExportRepository.php` | Corregido (`status`) |
| `workflow_rules` | `id, tenant_id, name, trigger_module, trigger_event, conditions_json, is_active, created_by_user_id` | SELECT/INSERT | `app/Core/Workflow/EcosistemaWorkflowRuleRepository.php`, `app/Core/Workflow/EcosistemaWorkflowTemplateInstallRepository.php` | OK |
| `workflow_runs` | `id, tenant_id, rule_id, triggered_by_user_id, source_module, source_table, source_id, status, input_json, output_json, error_message, started_at, finished_at, created_at` | SELECT/INSERT/UPDATE | `app/Core/Workflow/EcosistemaWorkflowRunRepository.php`, `app/Core/Workflow/EcosistemaWorkflowExecutionRepository.php`, `app/Core/Campaigns/EcosistemaCampaignCockpitRepository.php` | Corregido (`campaign_id` inexistente) |
| `workflow_run_logs` | `id, tenant_id, run_id, action_id, level, message, context_json, created_at` | SELECT/INSERT | `app/Core/Workflow/EcosistemaWorkflowRunRepository.php`, `app/Core/Workflow/EcosistemaWorkflowExecutionRepository.php` | OK |
| `reports_saved_queries` | Contrato revisado | No CRUD en alcance | sin referencias activas en código auditado | Sin uso directo |
| `reports_widgets` | Contrato revisado | No CRUD en alcance | sin referencias activas en código auditado | Sin uso directo |

## 3. Hallazgos encontrados
| Severidad | Archivo | Función/ruta | Tabla | Campo | Problema | Acción tomada |
|---|---|---|---|---|---|---|
| Alta | `app/Core/Reports/EcosistemaReportExportRepository.php` | `createExportRequest` | `reports_exports` | `status` | Se insertaba `pending`, valor no válido para enum real | Cambio a `queued` |
| Alta | `app/Core/Campaigns/EcosistemaCampaignCockpitRepository.php` | `workflowsByStatus` y conteo de embudo | `workflow_runs` | `campaign_id` | Se consultaba columna inexistente | Se migró el filtro a `source_table='crm_marketing_campaigns'` + `source_id=:campaign_id` |

## 4. Cambios realizados
- [x] Repositories corregidos si usaban columnas inexistentes
- [ ] Services corregidos si enviaban payload incorrecto
- [ ] Routes corregidas si faltaba sesión/permiso/CSRF/tenant
- [ ] Views corregidas si exponían campos sensibles
- [ ] Smoke-check/schema-usage actualizado si aplica
- [x] Documentación `docs/schema-usage/` actualizada

## 5. Campos obligatorios de INSERT verificados
| Tabla | Campos mínimos reales | ¿El código los llena? | Fuente del valor | Observación |
|---|---|---|---|---|
| `reports_exports` | `tenant_id` | Sí | sesión autenticada (`auth_tenant_id`) pasada a servicio/repositorio | `status` alineado a enum real (`queued`) |
| `workflow_rules` | `tenant_id, name, trigger_module, trigger_event` | Sí | tenant desde sesión/contexto; plantilla controlada para el resto | Inserción controlada en install service |
| `workflow_runs` | `tenant_id, rule_id, source_module` | Sí | tenant/rule desde servicio; source_module controlado por flujo | Inserción con validación previa de regla |
| `workflow_run_logs` | `tenant_id, run_id, message` | Sí | tenant/run del flujo de ejecución; mensaje saneado | Mensaje truncado/saneado |

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
- Warnings aceptados: no existe comando `composer schema:usage` en este repositorio.
- Pendientes que pasan al backlog: auditar tabla `workflow_actions` (fuera del alcance de tablas objetivo de este PR) para mantener alineación continua de catálogos de acciones.
- Evidencia principal: correcciones en `reports_exports.status` y sustitución de uso de `workflow_runs.campaign_id` inexistente por `source_table/source_id` reales.
