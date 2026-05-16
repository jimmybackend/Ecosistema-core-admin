# Flujo operativo del ecosistema

## Visión integrada (flujo ideal)

El flujo punta-a-punta de producto se mantiene como visión objetivo:

**Campaña → Short link → Click → Landing page → Visita → Formulario → Submission → Lead CRM → Notificación → Workflow → Reporte → IA operativa**

Este recorrido representa la integración esperada entre módulos cuando el ecosistema esté plenamente habilitado en operación productiva.

## Estado actual del flujo

| Etapa | Módulo relacionado | Estado actual | Qué se puede demostrar | Limitación actual |
|---|---|---|---|---|
| Campaña | Campaigns | Parcial | Cockpit, vistas y revisión de campañas | Creación/operación completa depende de flags y controles |
| Short link | URL Locator | Controlled por flags | Consulta de links, detalle y simulación de redirect | Edición/redirección real sujeta a permisos y flags |
| Click | Browser Analytics | Read-only | Trazas de eventos y pageviews en dashboards | Sin operación de escritura ni acciones activas desde admin |
| Landing page | Landing | Parcial | Superficies de páginas y render de experiencias de demo | Publicación completa no abierta por defecto |
| Visita | Browser Analytics | Read-only | Lectura de navegación y actividad capturada | No habilita acciones automatizadas de ejecución |
| Formulario | Landing | Parcial | Estructura y flujo de formularios para demo | Operación integral depende de modo y permisos |
| Submission | CRM / Landing | Dry-run | Simulación de envío y trazabilidad del evento | No siempre escribe en destino final en todos los entornos |
| Lead CRM | CRM | Parcial | Listado, detalle y seguimiento básico de leads | Lifecycle completo y automatizaciones avanzadas aún parciales |
| Notificación | Notifications / Mail | Controlled por flags | Plantillas, previsualización y estado de cola | Envío SMTP real desactivado por defecto |
| Workflow | Workflow | Controlled por flags | Reglas, ejecuciones simuladas y monitoreo de runs | Ejecución productiva completa no activa por defecto |
| Reporte | Reports | Parcial | Métricas y reportes administrativos visibles | Exportaciones/escritura aún condicionadas por modo |
| IA operativa | AI Assistant | Dry-run | Asistencia y propuestas no destructivas para operación | Sin automatización autónoma full en producción |

> El flujo completo representa la visión integrada del ecosistema. La implementación avanza por módulos seguros: primero lectura, luego simulación, después ejecución controlada por permisos y flags.

Para el detalle comercial/técnico por módulo, ver también [Matriz comercial de estado por módulo](./estado_modulos.md).
