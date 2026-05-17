# Flujo operativo para demo pública

## Flujo objetivo (visión)

**Campaña → Short link → Click → Landing → Visita → Formulario → Submission → Lead CRM → Notificación → Workflow → Reporte → IA**

## Flujo demostrable actual

- El flujo se puede contar de punta a punta como visión.
- En ejecución real, distintos tramos están en `read-only`, `dry-run` o `controlled`.
- Las acciones sensibles requieren flags, permisos y aprobación humana.

## Tabla por tramo

| Módulo | Qué se puede mostrar | Estado | Qué no se debe prometer | Próximo paso |
|---|---|---|---|---|
| Campaigns | Cockpit y seguimiento | Parcial | Gestión total sin controles | Activar create/edit por fases |
| URL Locator | Consulta y simulación de redirect | Read-only + dry-run + controlled | Redirect full abierto por defecto | Endurecer flujo con auditoría |
| Landing | Render y formularios en demo | Parcial/controlled | Publicación y submit productivos totales | Activación gradual por entorno |
| CRM | Lead list/detail y seguimiento base | Parcial/controlled | Lifecycle completo automatizado | Completar reglas y QA |
| Notifications | Preview/cola y trazabilidad | Controlled + dry-run | Envío real siempre activo | Guardrails por entorno |
| Workflow | Definiciones y ejecución simulada | Dry-run + controlled | Ejecución full productiva | Apertura progresiva con controles |
| Reports | Métricas y consultas | Parcial/read-only | Export total sin restricciones | Habilitar salidas controladas |
| AI assistance | Resumen/propuesta con operador | Dry-run + controlled | IA actuando sola | Más casos con revisión humana |

## Mensajes obligatorios durante la demo

- No se exponen datos reales.
- No se activan flags productivas por defecto.
- No se presenta como SaaS completo.
- La IA está gobernada por humanos.
