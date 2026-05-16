# Matriz comercial de estado por módulo

Esta matriz resume el **estado real de avance** por módulo en Core Admin para demos y comunicación externa sin sobrepromesas.

## Categorías de estado comercial

- **Disponible**: funcionalidad básica demostrable.
- **Parcial**: existe una parte operativa, pero no el módulo completo.
- **Read-only**: sólo consulta datos; no escribe.
- **Dry-run**: simula acciones; no modifica datos.
- **Controlled**: ejecuta acciones reales sólo con permisos y flags activas.
- **Roadmap**: existe diseño/documentación, pero no UI/operación funcional completa.

## Estado por módulo

| Módulo | Estado comercial | Estado técnico | Qué ya se puede mostrar | Qué está limitado | Próximo paso |
|---|---|---|---|---|---|
| Core Admin (Auth, tenants, users, roles, permissions, modules) | Disponible | Operativo en rutas principales y gestión administrativa base | Login, dashboard, gestión de tenants/usuarios/roles/permisos/módulos | Endpoints de ecosistema ampliado siguen condicionados por flags/modos | Consolidar pruebas funcionales end-to-end por rol |
| System Health / Audit | Disponible | Monitoreo y auditoría base activos | Salud de sistema/DB, logs y auditoría de eventos | No reemplaza observabilidad distribuida ni SIEM externo | Integrar alertamiento centralizado y trazas extendidas |
| Reports | Parcial | Vistas y consultas disponibles; exportaciones con restricciones por modo | Reportes administrativos y métricas visibles | Parte de exportación/escritura opera en dry-run/controlled | Abrir exportaciones reales por etapas con permisos finos |
| Campaigns | Parcial | Existe cockpit y flujos acotados | Vistas de campañas y flujo de revisión | Creación/operación total depende de flags y controles | Habilitar create/edit productivo por fases |
| CRM | Parcial | Lectura estable + acciones puntuales controladas | Leads, detalle y seguimiento básico demostrable | Automatizaciones y escritura avanzada no totalmente abiertas | Completar lifecycle de lead con reglas y QA |
| Landing | Parcial | Render/formularios con modos read-only/dry-run/controlled | Superficies de páginas/entradas y formularios de demo | Publicación y operación plena no abierta por defecto | Activación gradual de publish/submit real |
| URL Locator | Parcial | CRUD/redirect con control por modo | Consulta de links, detalle y simulaciones de redirect | Redirección/edición real sujeta a flags/permisos | Fortalecer flujo productivo con auditoría reforzada |
| Browser Analytics | Read-only | Consultas y dashboards de lectura | Eventos/pageviews y panel de analítica | Sin escritura operativa directa en módulo admin | Evolucionar a operación controlada con consentimiento estricto |
| Notifications / Mail | Controlled | Plantillas/colas/vistas con envío real bloqueado por defecto | Composición, previsualización y estado de cola | Envío SMTP real requiere habilitación explícita | Activar envío real por entornos con guardrails |
| AI Assistant | Dry-run | Capacidades de apoyo con ejecución acotada y flags | Asistencia y propuestas no destructivas para demo | Sin automatización autónoma full ni escrituras abiertas por defecto | Expandir casos controlados y trazabilidad de prompts |
| Cloud / Drive | Controlled | Inventario y contratos operativos; integraciones remotas protegidas por flags | Resumen de archivos/carpetas, contratos de descarga y operación guiada | AWS/S3 real desactivado por defecto; acciones remotas restringidas | Activar integración real por entorno con checklist de hardening |
| Workflow | Parcial | Reglas/runs visibles + dry-run y control por flags | Definiciones, ejecuciones simuladas y monitoreo de runs | Ejecución productiva full no activa por defecto | Abrir ejecución real gradualmente con controles de riesgo |
| Billing | Roadmap | No aparece como módulo funcional completo en Core Admin | Referencias de visión comercial | Sin UI/operación integral de facturación en este panel | Definir alcance MVP y plan de entrega incremental |
| Integrations | Roadmap | Integraciones visibles de forma fragmentada y/o técnica | Demostraciones técnicas puntuales por feature | No existe un módulo funcional integral de integraciones | Diseñar hub de integraciones con catálogo y estado por conector |
| Support | Roadmap | No existe módulo de soporte end-to-end consolidado | Evidencia indirecta vía auditoría/logs operativos | Sin mesa de soporte integral (tickets/SLA/operación completa) | Definir módulo de soporte y flujo operativo unificado |
| Privacy / Compliance | Parcial | Hay controles y documentación, pero no un módulo UI completo dedicado | Políticas, flags y lineamientos de consentimiento | Falta superficie única de compliance operacional | Construir cockpit de cumplimiento y evidencia auditable |
| Jobs / Workers | Roadmap | Cron actual limitado a tareas acotadas; sin workers productivos completos | Plan y estado actual documentados | No hay pipeline productivo integral de colas/workers | Implementar workers productivos por etapas con observabilidad |

## Nota de uso comercial

La visión del producto se mantiene: Core Admin integra capacidades transversales para operar el ecosistema. Esta matriz sólo aclara el **estado real actual** (listo, parcial o roadmap) para evitar ambigüedades en demos y materiales externos.
