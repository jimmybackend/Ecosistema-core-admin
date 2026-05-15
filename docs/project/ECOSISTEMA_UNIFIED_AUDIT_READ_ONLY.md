# ECOSISTEMA UNIFIED AUDIT (READ-ONLY)

Implementa una vista unificada de auditoría basada en tablas canónicas `core_audit`, `audit_entity_changes` y `module_audit_links`.

## Alcance
- Solo lectura.
- Tenant tomado desde sesión autenticada (`auth_tenant_id`).
- Sin aceptar `tenant_id` por request.
- Sin exponer JSON crudo (`old_values/new_values/before_json/after_json/metadata_json`).

## Rutas
- `GET /audit` redirige a `GET /audit/events`.
- `GET /audit/events` lista eventos con filtros seguros.
- `GET /audit/events/{id}` detalle con flags `*_present` para JSON sensibles.
