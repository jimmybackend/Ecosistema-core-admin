# ECOSISTEMA AI Assistant Controlled

## Qué hace
- Expone la ruta `POST /ai/assist` para generar una propuesta **controlada** desde un lead del tenant autenticado.
- Requiere sesión activa, permiso `modules.view` y validación CSRF antes de procesar la solicitud.
- Toma `tenant_id` y `user_id` desde sesión (`auth_tenant_id`, `auth_user_id`) y no desde request.
- Sanitiza contexto del lead antes de construir la salida (`email`, `phone`, `contact_name`, texto de interés).
- Entrega sólo vista previa acotada (no payloads crudos).

## Qué NO hace
- No ejecuta acciones autónomas en CRM/campañas/workflows.
- No llama proveedor externo si `ECOSISTEMA_AI_PROVIDER_ENABLED=false`.
- No persiste propuestas en `os_ai_proposals` si `ECOSISTEMA_AI_WRITE_PROPOSALS=false`.
- No imprime prompts/respuestas crudas con PII completa en la vista de resultado.

## Activación en ambiente controlado
1. Mantener defaults seguros en `.env`:
   - `ECOSISTEMA_AI_ENABLED=false`
   - `ECOSISTEMA_AI_PROVIDER_ENABLED=false`
   - `ECOSISTEMA_AI_WRITE_PROPOSALS=false`
2. Habilitar de forma incremental por ambiente:
   - Paso 1: `ECOSISTEMA_AI_ENABLED=true` (sin proveedor, sin escritura).
   - Paso 2: `ECOSISTEMA_AI_PROVIDER_ENABLED=true` (proveedor stub/controlado).
   - Paso 3: `ECOSISTEMA_AI_WRITE_PROPOSALS=true` sólo con aprobación operativa.
3. Mantener revisión humana explícita de cualquier propuesta (`requires_human_confirmation=1`).

## Riesgos y mitigaciones
- **Riesgo: exposición de PII** → mitigado con máscaras y previews sanitizadas.
- **Riesgo: acción autónoma accidental** → mitigado con gate de flags + permiso + CSRF.
- **Riesgo: escritura no autorizada** → mitigado con flag `ECOSISTEMA_AI_WRITE_PROPOSALS` en false por defecto.
- **Riesgo: salida insegura en UI** → mitigado con render escapado (`htmlspecialchars`) y sin JSON crudo.
