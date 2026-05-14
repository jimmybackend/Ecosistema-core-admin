# ECOSISTEMA Landing Form Submit Controlled

Implementa `POST /l/{slug}/forms/{id}/submit` con guardas por flags y persistencia controlada.

- Flags default apagadas:
  - `ECOSISTEMA_LANDING_FORM_SUBMIT_ENABLED=false`
  - `ECOSISTEMA_LANDING_FORM_FILE_UPLOADS=false`
- Escribe únicamente en:
  - `landing_form_submissions`
  - `landing_form_submission_values`
- No crea leads CRM automáticamente (`crm_lead_write=false`).
- `tenant_id` se aplica desde contexto de aplicación/sesión, nunca desde request.
