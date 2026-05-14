# ECOSISTEMA CRM LEAD DETAIL (READ-ONLY)

- Ruta: `GET /crm/leads/{id}`
- Tenant: tomado de sesión (`auth_tenant_id`).
- Modo: `read-only`, `db_write=false`, `pii_exposed=false`.
- No expone PII completa (`email`, `phone`, `contact_name`) ni JSON crudo (`raw_data_json`, `value_json`, `metadata_json`).
- Fuentes relacionadas consultadas por `SELECT`:
  - `crm_campaign_leads` + `crm_marketing_campaigns` + `crm_lead_funnel_stages`
  - `crm_lead_conversions`
  - `landing_form_submissions` + conteo de `landing_form_submission_values`
  - `browser_analytics_attribution`
- Diferencias con legacy deben resolverse con DB canónica `adbbmis1_eco`.
