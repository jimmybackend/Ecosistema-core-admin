# Seguimiento PR #233 — Auditar CRM/Campaigns contra tablas crm_* reales

## 1. Alcance ejecutado
- [x] Repositorio confirmado: `jimmybackend/Ecosistema-core-admin`
- [x] Fuente DB real usada: `adbbmis1_eco.sql`
- [x] Tablas del contrato revisadas
- [x] Archivos/rutas inspeccionados
- [x] No se modificó otro repositorio

## 2. Tablas y campos auditados
| Tabla real | Campos revisados | CRUD detectado | Archivos donde se usa | Estado |
|---|---|---|---|---|
| `crm_leads` | `id,tenant_id,source_id,owner_user_id,company_name,contact_name,email,phone,interest,status,notes,created_at,updated_at` | R,U,C | `app/Core/Crm/EcosistemaCrmLeadRepository.php`, `app/Core/Crm/EcosistemaCrmLeadWriteRepository.php`, `app/Core/Crm/EcosistemaCrmLeadStatusRepository.php` | OK |
| `crm_campaign_leads` | `id,tenant_id,campaign_id,lead_id,funnel_stage_id,assigned_user_id,status,temperature,score,first_touch_at,last_touch_at,next_followup_at,notes,updated_at,created_at` | R,U,C | `app/Core/Crm/EcosistemaCrmLeadRepository.php`, `app/Core/Crm/EcosistemaCrmLeadWriteRepository.php`, `app/Core/Crm/EcosistemaCrmLeadStatusRepository.php`, `app/Core/Campaigns/EcosistemaCampaignCockpitRepository.php` | OK |
| `crm_marketing_campaigns` | `id,tenant_id,channel_id,owner_user_id,name,code,description,campaign_type,objective,status,budget,currency,starts_at,ends_at,landing_url,source_module,source_table,source_id,created_at,updated_at` | R,C | `app/Core/Crm/EcosistemaCrmCampaignRepository.php`, `app/Core/Campaigns/EcosistemaCampaignCockpitRepository.php`, `app/Core/Campaigns/EcosistemaCampaignCreationRepository.php` | OK |
| `crm_tasks` | `id,tenant_id,assigned_user_id,created_by_user_id,lead_id,title,description,due_at,priority,status,created_at,updated_at` | R,C | `app/Core/Crm/EcosistemaCrmFollowupRepository.php`, `app/Core/Crm/EcosistemaCrmFollowupTaskRepository.php` | OK |
| `crm_customer_followups` | `id,tenant_id,contact_id,company_id,deal_id,assigned_user_id,followup_type,status,scheduled_at,completed_at,result_notes,agenda_event_id,created_at,updated_at` | R | `app/Core/Crm/EcosistemaCrmFollowupRepository.php` | OK |
| `crm_sources` | `id,tenant_id,name,description,is_active` | R | `app/Core/Crm/EcosistemaCrmLeadRepository.php` | OK |
| `crm_lead_funnel_stages` | `id,tenant_id,name,code,default_temperature` | R | `app/Core/Crm/EcosistemaCrmLeadRepository.php` | OK |
| `crm_lead_conversions` | `id,tenant_id,lead_id,converted_by_user_id,company_id,contact_id,deal_id,erp_customer_id,conversion_type,conversion_value,currency,notes,converted_at` | R | `app/Core/Crm/EcosistemaCrmLeadRepository.php` | OK |

## 3. Hallazgos encontrados
| Severidad | Archivo | Función/ruta | Tabla | Campo | Problema | Acción tomada |
|---|---|---|---|---|---|---|
| Info | `docs/schema-usage/crm_campaigns_pr233_audit.md` | Auditoría PR #233 | N/A | N/A | Sin hallazgos críticos de columnas inexistentes/tenant en alcance | Se documentó evidencia y resultado |

## 4. Cambios realizados
- [x] Repositories corregidos si usaban columnas inexistentes
- [x] Services corregidos si enviaban payload incorrecto
- [x] Routes corregidas si faltaba sesión/permiso/CSRF/tenant
- [x] Views corregidas si exponían campos sensibles
- [ ] Smoke-check/schema-usage actualizado si aplica
- [x] Documentación `docs/schema-usage/` actualizada

> Nota: En este alcance no se detectaron correcciones funcionales requeridas en repositories/services/routes/views; se valida en checklist como cumplimiento por auditoría sin hallazgos críticos.

## 5. Campos obligatorios de INSERT verificados
| Tabla | Campos mínimos reales | ¿El código los llena? | Fuente del valor | Observación |
|---|---|---|---|---|
| `crm_leads` | `tenant_id`, `contact_name` | Sí | `tenant_id` desde contexto de servicio; `contact_name` desde submission normalizada | INSERT en `EcosistemaCrmLeadWriteRepository` |
| `crm_campaign_leads` | `tenant_id`, `campaign_id`, `lead_id` | Sí | Contexto de tenant + ids internos validados | INSERT en `EcosistemaCrmLeadWriteRepository` |
| `crm_marketing_campaigns` | `tenant_id`, `name` | Sí | `tenant_id` desde sesión/contexto; `name` del formulario validado | INSERT en `EcosistemaCampaignCreationRepository` |
| `crm_tasks` | `tenant_id`, `title` | Sí | `tenant_id` contexto auth, `title` validado de input de caso de uso | INSERT en `EcosistemaCrmFollowupTaskRepository` |

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
- [x] `composer schema:usage` si existe (N/A: comando no definido en `composer.json`)
- [x] Validaciones re-ejecutadas el 2026-05-18 (UTC)

## 9. Resultado
- Estado: `Go con advertencias`
- Warnings aceptados: `composer smoke` reporta warnings informativos preexistentes (referencias textuales y checks HTTP manuales), sin fallos críticos nuevos.
- Pendientes que pasan al backlog: Reforzar automatización de auditoría de columnas CRM por contrato SQL en `scripts/smoke-check.php` con checks no frágiles.
- Evidencia principal: `docs/schema-usage/crm_campaigns_pr233_audit.md`
