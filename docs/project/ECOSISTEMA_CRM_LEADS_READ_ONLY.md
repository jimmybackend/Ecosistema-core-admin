# ECOSISTEMA CRM LEADS READ-ONLY

## Objetivo
Exponer listado de `crm_leads` en modo solo lectura para el tenant autenticado.

## Alcance
- Ruta `GET /crm/leads`.
- Repositorio y servicio dedicados para `crm_leads`.
- DTO seguro con previews/máscaras para PII (`contact_name`, `email`, `phone`) y contenido sensible (`notes`, `interest`).
- Capabilities CRM actualizadas:
  - `leads_read: true`
  - `lead_write: false`

## Seguridad y privacidad
- Tenant aplicado desde sesión (`auth_tenant_id`), no desde request.
- Solo consultas `SELECT` con PDO prepared statements.
- No se exponen email/teléfono/contacto completos en la vista.
- Modo explícito `read-only` y `db_write=false`.

## Campos del DTO
- `id`
- `source_id`
- `owner_user_id`
- `company_name_preview`
- `contact_name_present`
- `contact_name_preview`
- `email_present`
- `email_preview`
- `phone_present`
- `phone_preview`
- `interest_preview`
- `status`
- `notes_present`
- `notes_preview`
- `created_at`
- `updated_at`
- `mode=read-only`
- `db_write=false`
