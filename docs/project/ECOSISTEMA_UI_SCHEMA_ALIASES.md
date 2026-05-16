# ECOSISTEMA UI Schema Aliases

## Objetivo
Documentar aliases de presentación usados por la UI administrativa cuando el esquema real no tiene las mismas columnas que la grilla/formulario.

## Alcance revisado
- `app/Core/Roles`
- `app/Core/Permissions`
- `app/Core/Modules`
- `app/Core/Users`
- `app/Core/Tenants`

## Inventario de aliases UI

| Módulo | Tabla real | Columna real | Alias UI | Read-only / editable | Riesgo de confusión |
|---|---|---|---|---|---|
| Roles | `core_roles` | `slug` | `code` | Editable vía edición de rol (persiste en `slug`) | Bajo (alias explícito en vista y repositorio) |
| Roles | `core_roles` | _no existe_ | `status='active'` derivado | **Read-only** (no persiste) | Medio si se mostrara acción de cambio; mitigado: no hay acción y servicio responde mensaje explícito |
| Permisos | `core_permissions` | `code` | `code` (sin alias) | Editable (persiste en `code`) | Bajo |
| Permisos | `core_permissions` | _no existe_ | `status='active'` derivado | **Read-only** (no persiste) | Medio si se ofreciera cambio; mitigado: no hay acción y servicio responde mensaje explícito |
| Permisos | `core_permissions` | _no existe_ | `action=''`, `resource=''` derivados | Read-only de presentación | Bajo |
| Módulos | `core_modules` | `status` | `status` | Editable (persiste en DB) | Bajo |
| Usuarios | `core_users` | `status` | `status` | Editable (persiste en DB) | Bajo |
| Tenants | `core_tenants` | `status` | `status` | Editable (persiste en DB) | Bajo |

## Repositorios y decisiones

### Roles (`core_roles`)
- Alias detectados:
  - `code` derivado de `slug`.
  - `status` derivado y fijo en `active`.
- `updateStatus()` en repositorio es no-op (`false`) y **no escribe DB**.
- Acción tomada: mantener como no-op explícito y evitar UI engañosa (la vista lista solo badge de status derivado; no hay botón de cambio).

### Permisos (`core_permissions`)
- Alias detectados:
  - `status` derivado y fijo en `active`.
  - `action` y `resource` hidratados para compatibilidad de vista.
- `updateStatus()` en repositorio es no-op (`false`) y **no escribe DB**.
- Acción tomada: mantener no-op explícito y sin acción de cambio de status en UI.

### Módulos (`core_modules`)
- `status` es columna real.
- `updateStatus()` persiste en DB.
- Acción de cambio de status en UI es válida.

### Usuarios (`core_users`)
- `status` es columna real.
- `updateStatus()` persiste en DB.
- Acción de cambio de status en UI es válida.

### Tenants (`core_tenants`)
- `status` es columna real.
- `updateStatus()` persiste en DB.
- Acción de cambio de status en UI es válida.

## Criterios operativos
- No usar aliases derivados para writes cuando la columna no existe.
- Si un campo es derivado (`status` en roles/permisos), debe exponerse solo como lectura en UI.
- Cualquier intento de cambio de status en dominios sin columna real debe devolver mensaje explícito de no soporte por DB.
