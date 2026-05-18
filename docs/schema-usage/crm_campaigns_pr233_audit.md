# PR #233 — Auditoría CRM/Campaigns contra tablas `crm_*` reales

Fuente auditada: `adbbmis1_eco.sql` (contrato entregado en el prompt).

## Alcance
Se auditó uso de tablas `crm_*` en:
- `app/Core/Crm/`
- `app/Core/Campaigns/`
- `routes/web.php`
- `resources/views/pages/crm/`
- `resources/views/pages/campaigns/`

## Evidencia de consultas y escrituras detectadas

### `crm_leads`
- Lectura por tenant en listados y detalle: `EcosistemaCrmLeadRepository` (`WHERE tenant_id=:tenant_id`).
- Escritura controlada: `EcosistemaCrmLeadWriteRepository::createLeadFromSubmission` llena `tenant_id` desde parámetro de contexto y `contact_name` desde payload normalizado del servicio.
- Cambio de estado por tenant: `EcosistemaCrmLeadStatusRepository::updateLeadStatus`.

### `crm_campaign_leads`
- Lecturas por tenant y joins por tenant en `EcosistemaCrmLeadRepository`.
- Inserción por tenant en `EcosistemaCrmLeadWriteRepository::linkLeadToCampaign` con mínimos reales (`tenant_id`, `campaign_id`, `lead_id`).
- Update por tenant en `EcosistemaCrmLeadStatusRepository::updateCampaignLeadStatus`.

### `crm_marketing_campaigns`
- Lectura por tenant en `EcosistemaCrmCampaignRepository` y `EcosistemaCampaignCockpitRepository`.
- Inserción en `EcosistemaCampaignCreationRepository::createCampaign` con `tenant_id` desde contexto y `name` obligatorio presente.

### `crm_tasks`
- Lecturas por tenant en `EcosistemaCrmFollowupRepository`.
- Inserción por tenant en `EcosistemaCrmFollowupTaskRepository::createTask` con mínimos reales (`tenant_id`, `title`).

### `crm_customer_followups`
- Lecturas por tenant en `EcosistemaCrmFollowupRepository`.
- No se detectaron escrituras en Core Admin para esta tabla en este alcance.

### Otras tablas CRM referenciadas
- `crm_sources`, `crm_lead_funnel_stages`, `crm_lead_conversions` (lectura en repositorio de leads con filtro tenant y joins tenant-aware).

## Hallazgos
- **Sin hallazgos críticos** de columnas inexistentes en tablas `crm_*` usadas por CRM/Campaigns dentro del alcance.
- **Sin hallazgos críticos** de falta de `tenant_id` en lecturas/escrituras sobre tablas tenant-aware del alcance.
- **Sin hallazgos críticos** de omisión de campos mínimos de `INSERT` para tablas `crm_*` con escrituras detectadas en alcance.

## Verificaciones de seguridad y cumplimiento
- Escrituras CRM/Campaigns detectadas usan `tenant_id` parametrizado (no proveniente libremente de request).
- Los flujos de UI revisados para CRM no exponen secretos tipo `s3_key` (no se detectó uso en vistas auditadas del alcance).
- Las rutas de escritura auditadas en `routes/web.php` operan bajo sesión autenticada y helpers de guard existentes del módulo.

## Acción correctiva aplicada en este PR
- Se agrega documentación de auditoría y checklist de seguimiento PR #233.
- No se requirieron cambios funcionales de repositorios/servicios en el alcance por no hallarse desalineaciones críticas contra el contrato entregado.
