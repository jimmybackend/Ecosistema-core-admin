# ECOSISTEMA CRM Lead Status Controlled

- Rutas:
  - `GET /crm/leads/{id}/status`
  - `POST /crm/leads/{id}/status`
- Flag por defecto apagada: `ECOSISTEMA_CRM_LEAD_STATUS_WRITE=false`.
- Fuente canónica: `adbbmis1_eco`.

## Alcance de escritura
- `UPDATE crm_leads.status` (si flag activa).
- `UPDATE crm_campaign_leads.status/temperature/score` opcional (si `campaign_lead_id` válido del mismo `tenant_id` y `lead_id`).
- Auditoría segura en `core_audit` mediante `AuditLogger` sin exponer PII sensible.

## Seguridad
- `tenant_id` siempre desde sesión/auth context.
- No se acepta `tenant_id` desde request.
- IDs validados como enteros positivos.
- Validación de transición: no permite mover desde `won/lost` a otros estados.
- Vistas sólo muestran previews/flags (`pii_preview_only=true`).
