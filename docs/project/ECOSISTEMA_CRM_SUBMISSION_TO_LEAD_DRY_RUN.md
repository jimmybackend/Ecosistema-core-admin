# CRM submission to lead dry-run

## Objetivo
Simular en modo administrativo si una fila de `landing_form_submissions` podría convertirse en lead CRM, sin escrituras en base de datos.

## Ruta
- `GET /crm/submission-to-lead/{id}/dry-run`

## Fuente canónica
- Se prioriza `adbbmis1_eco` como fuente maestra de tablas/campos.

## Comportamiento
- Resuelve tenant desde sesión (`auth_tenant_id`), no desde request.
- Lee `landing_form_submissions` y `landing_form_submission_values` por `tenant_id` + `submission_id`.
- Mapea campos conocidos: `contact_name`, `email`, `phone`, `company_name`, `interest`, `message`.
- Hace detección de duplicados potenciales por `email`/`phone` en `crm_leads` usando solo `SELECT COUNT(*)`.
- Nunca ejecuta `INSERT`, `UPDATE` o `DELETE`.

## DTO de salida
- `mode = dry-run`
- `would_create_lead`
- `would_link_campaign`
- `would_update_submission = false`
- `db_write = false`
- `duplicate_candidates_count`
- `mapped_fields`
- `missing_required_fields`
- `warnings`
- `pii_preview_only = true`

## Privacidad
- No expone `raw_data_json` ni `value_json` crudos.
- Campos sensibles se muestran como preview/masking.

## Flags
- Runtime esperado:
  - `ECOSISTEMA_CRM_ENABLED=true`
  - `ECOSISTEMA_CRM_SUBMISSION_TO_LEAD_DRY_RUN=true`
- `.env.example` se mantiene en `false` para:
  - `ECOSISTEMA_CRM_ENABLED`
  - `ECOSISTEMA_CRM_SUBMISSION_TO_LEAD_DRY_RUN`
  - `ECOSISTEMA_CRM_SUBMISSION_TO_LEAD_WRITE`
