# ECOSISTEMA AI Assistant Controlled

- Ruta: `POST /ai/assist` (sesión + `modules.view` + CSRF).
- Fuente canónica: `adbbmis1_eco`.
- Tenant se toma de sesión (`auth_tenant_id`), nunca de request.
- Sanitiza PII de lead (`email/phone/contact_name`) antes de armar contexto.
- Llamada de proveedor sólo si `ECOSISTEMA_AI_ENABLED=true` y `ECOSISTEMA_AI_PROVIDER_ENABLED=true`.
- Persistencia en `os_ai_proposals` sólo si además `ECOSISTEMA_AI_WRITE_PROPOSALS=true`.
- Sin prompts/respuestas crudas con PII; sólo previews y campos controlados.
