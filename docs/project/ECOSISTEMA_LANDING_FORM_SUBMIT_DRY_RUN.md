# ECOSISTEMA Landing Form Submit Dry-run

## Objetivo
Simular envío de formulario Landing sin persistencia: valida required, tipo, longitud y spam básico.

## Rutas
- `GET /landing/forms/{id}/submit-dry-run`
- `POST /landing/forms/{id}/submit-dry-run`

## Flags
- `ECOSISTEMA_LANDING_FORM_SUBMIT_DRY_RUN=false` (default).

## Seguridad
- Tenant tomado desde sesión (`auth_tenant_id`).
- No acepta `tenant_id` desde request.
- No ejecuta INSERT/UPDATE/DELETE.
- No crea submissions ni leads CRM.
- Respuesta usa previews en campos sensibles (email/teléfono/textos largos).
