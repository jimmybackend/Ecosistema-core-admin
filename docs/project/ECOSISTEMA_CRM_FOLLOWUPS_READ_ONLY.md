# ECOSISTEMA CRM FOLLOWUPS (READ-ONLY)

- Rutas: `GET /crm/followups`, `GET /crm/leads/{id}/followups`.
- Tenant: tomado de sesión (`auth_tenant_id`).
- Modo: `read-only`, `db_write=false`.
- Fuentes consultadas con `SELECT`: `crm_tasks`, `crm_customer_followups`, `agenda_events`.
- `tenant_id` no se acepta por request.
- IDs validados como enteros positivos.
- No se realizan `INSERT/UPDATE/DELETE`.
