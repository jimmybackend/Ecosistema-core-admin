# CRM submission to lead controlled write

## Objetivo
Permitir creación controlada de `crm_leads` desde `landing_form_submissions` para el tenant de sesión, con flags, CSRF y permiso administrativo.

## Ruta
- `POST /crm/submission-to-lead/{id}`

## Guardas de seguridad
- Requiere login.
- Requiere permiso `modules.manage`.
- Requiere CSRF válido.
- Usa tenant desde sesión (`auth_tenant_id`), nunca desde request.
- Exige flags activas:
  - `ECOSISTEMA_CRM_ENABLED=true`
  - `ECOSISTEMA_CRM_SUBMISSION_TO_LEAD_WRITE=true`
- Bloquea si `landing_form_submissions.crm_lead_id` ya existe.
- Detecta duplicados por email/teléfono y bloquea salvo confirmación explícita (`force_duplicate=1`).

## Escrituras permitidas
- `INSERT INTO crm_leads`
- `INSERT INTO crm_campaign_leads` (si `campaign_id` de submission es válido)
- `UPDATE landing_form_submissions` para setear `crm_lead_id` y `processed_at`

## Privacidad
- Resultado no expone PII completa; solo IDs, contadores y estado.
