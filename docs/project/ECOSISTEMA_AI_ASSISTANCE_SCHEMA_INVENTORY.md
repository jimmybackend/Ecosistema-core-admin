# ECOSISTEMA AI Assistance — Schema Inventory (read-only, PR #146)

## Objetivo
Documentar puntos seguros para asistencia IA en Core Admin usando como fuente canónica `adbbmis1_eco`, sin integrar proveedor IA, sin ejecutar acciones externas y sin persistir prompts/responses de proveedor.

## Fuentes y precedencia
1. **Canónica:** dump/catálogo real `adbbmis1_eco`.
2. `jimmybackend/Ecosistema-core-admin` para patrones técnicos (repositories/services/rutas/vistas/docs/smoke-check).
3. `jimmybackend/Ecosistema-bd` sólo como referencia auxiliar (sin modificar).
4. `jimmybackend/mailit-click` sólo referencia funcional legacy, sin copiar código.

En discrepancias, prevalece `adbbmis1_eco`.

## Alcance de este PR
- Inventario documental de tablas AI/OS existentes.
- Inventario de casos de uso IA **seguros** en modo lectura/asistencia.
- Definición de guardrails de privacidad y tenancy para futuras fases.

Fuera de alcance:
- Nuevas rutas funcionales.
- Escrituras DB.
- Migraciones/seeds/tablas/campos nuevos.
- Llamadas a proveedor IA.
- Almacenamiento de prompts/responses crudos.

## Tablas AI/OS confirmadas (canónicas)

### `os_ai_proposals`
Columnas: `proposal_id`, `boot_id`, `tenant_id`, `user_id`, `created_unix`, `proposal_type`, `summary`, `rationale`, `risk_level`, `benefit_level`, `requires_human_confirmation`, `status`, `created_at`, `updated_at`.

Uso seguro esperado:
- Catálogo interno de propuestas operativas resumidas.
- Estado de revisión humana (`requires_human_confirmation`, `status`).

### `os_human_responses`
Columnas: `id`, `proposal_id`, `boot_id`, `tenant_id`, `user_id`, `response`, `operator_key`, `response_unix`, `created_at`.

Uso seguro esperado:
- Registro de confirmación/rechazo humano.
- Trazabilidad por operador sin exponer PII completa.

### `os_knowledge_packs`
Columnas: `pack_id`, `tenant_id`, `name`, `version`, `lang`, `topic`, `source_hash`, `loaded_unix`, `created_at`.

Uso seguro esperado:
- Inventario de paquetes de conocimiento por tenant.
- Verificación de versión/hashes en diagnósticos internos.

### `chat_threads`
Columnas: `id`, `tenant_id`, `user_id`, `title`, `status`, `created_at`, `updated_at`.

### `chat_messages`
Columnas: `id`, `tenant_id`, `thread_id`, `sender_type`, `sender_user_id`, `content`, `metadata_json`, `created_at`.

Uso seguro esperado:
- Métricas operativas (conteos, estados, latencia de respuesta).
- Evitar exposición de `content` y `metadata_json` crudos en vistas.

## Tablas relacionadas para contexto (sin escritura)
- `crm_leads`
- `crm_campaign_leads`
- `browser_analytics_daily_rollups`
- `reports_saved_queries`
- `service_event_logs`
- `core_audit`
- `privacy_consents`
- `privacy_tracking_preferences`

## Casos de uso IA seguros (inventario funcional)

1. **Lead summary (read-only):**
   - Entrada: lead/campaign signals existentes.
   - Salida: resumen textual acotado + banderas de riesgo/beneficio.
   - Restricción: sin mostrar email/teléfono completos ni payloads raw.

2. **Campaign insight (read-only):**
   - Entrada: rollups agregados y relación campaña-leads.
   - Salida: observaciones de desempeño y oportunidades de ajuste.
   - Restricción: no exponer PII ni prompts de proveedor.

3. **Workflow suggestions (dry-run conceptual):**
   - Entrada: eventos/auditoría/rules context.
   - Salida: sugerencias no ejecutables y sujetas a confirmación humana.
   - Restricción: sin ejecutar acciones ni escribir workflows.

4. **Report explanations (read-only):**
   - Entrada: metadata de reportes guardados + métricas agregadas.
   - Salida: explicación natural de resultados/tendencias.
   - Restricción: no revelar SQL completo ni JSON sensible.

## Guardrails técnicos obligatorios para fases futuras
- Tenant siempre desde sesión/contexto autenticado (`auth_tenant_id` equivalente); **nunca** desde request.
- IDs validados como enteros positivos.
- SQL únicamente en repositories y con PDO prepared statements.
- Services para DTO/reglas/mascarado de salida.
- Vistas con previews/redactions para:
  - JSON (`metadata_json`, payloads)
  - URLs/IPs/user agents
  - emails/teléfonos
  - tokens/hashes/secrets
- Mensajes de error seguros (sin SQL errors/stack traces en UI).

## Campos sensibles a proteger
- PII de leads y datos de campaña.
- Contenido de chat y `metadata_json`.
- Valores de auditoría (`before/after` o equivalentes).
- Prompts/responses de proveedor (si existieran en futuro).
- Tokens, secretos, hashes, credenciales y correos raw.

## Estado de implementación
- Esta entrega es **sólo documental**.
- No se agregan flags obligatorias nuevas.
- Defaults operativos permanecen seguros (sin ejecución IA externa).

## Diferencias y resolución de conflictos
- Si legacy (`mailit-click`) difiere de `Ecosistema-bd` o de `adbbmis1_eco`, se adopta `adbbmis1_eco` y se documenta la brecha antes de implementar.
- Si alguna columna esperada no existe en canónico, el flujo debe quedar bloqueado/seguro (sin “inventar” estructura).

## Checklist para siguientes PRs (incremental)
1. Inventario/read-only (este PR).
2. Dry-run interno sin proveedor IA ni escrituras.
3. Flujo controlado por flags explícitas y defaults en `false`.
4. Revisión de permisos existentes (sin seeds/permisos nuevos).
5. Smoke-check de no-escritura/no-PII/no-tenant-from-request.
