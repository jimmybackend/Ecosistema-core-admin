# Módulos del producto

La plataforma se construye por etapas para asegurar calidad operativa y control de riesgos: **read-only → dry-run → controlled → producción**.  
Esto significa que no todos los módulos están en el mismo nivel al mismo tiempo.

## Leyenda de estado

- **Disponible base**: funcionalidad administrativa base existente.
- **Read-only**: consulta datos sin modificar.
- **Dry-run**: simula sin escribir.
- **Controlled**: escribe o ejecuta sólo con flags/permisos/CSRF y defaults seguros.
- **Roadmap**: diseñado/documentado, pendiente de implementación funcional.
- **Mixto**: tiene partes en más de un estado.

## Estado real por módulo

| Módulo | Estado | Resumen comercial honesto |
| --- | --- | --- |
| Base de datos canónica | **Disponible base** | Modelo canónico operativo para centralizar datos y sostener los flujos administrativos actuales. |
| Core Admin | **Mixto** | Núcleo administrativo funcional con capacidades ya utilizables y otras todavía en evolución controlada. |
| Auth, roles y permisos | **Controlled** | Gobierno de acceso activo con controles de permisos y operación segura por defecto. |
| Ecosistema Drive | **Read-only** | Visibilidad y consulta de información/documentos, con cambios limitados a etapas controladas. |
| URL Locator | **Read-only** | Descubrimiento y consulta de URLs como base para decisiones operativas antes de automatizar cambios. |
| Landing Pages | **Roadmap** | Diseño funcional definido para iterar en fases antes de habilitar operación completa. |
| Browser Analytics | **Read-only** | Lectura de señales y métricas para diagnóstico, sin intervención directa automática. |
| CRM y campañas | **Mixto** | Gestión comercial con componentes utilizables hoy y otros en transición por fases. |
| Mail / Notifications | **Controlled** | Envíos y notificaciones bajo controles explícitos para evitar ejecuciones no deseadas. |
| Workflow | **Dry-run** | Orquestaciones simuladas para validar reglas y secuencias antes de escritura efectiva. |
| SaaS Core | **Disponible base** | Base operativa del producto disponible para sostener administración y evolución modular. |
| Billing | **Roadmap** | Definición de arquitectura y flujo comercial; implementación funcional avanza por etapas. |
| Security / Audit / Privacy | **Mixto** | Seguridad y auditoría con base activa; privacidad/compliance continúa en maduración progresiva. |
| Integrations | **Roadmap** | Marco de integraciones diseñado para incorporarse de forma gradual y gobernada. |
| Support | **Roadmap** | Funcionalidad de soporte planificada para despliegue incremental, no cerrada como módulo final. |
| Reports | **Disponible base** | Reportería administrativa base disponible para seguimiento operativo y toma de decisiones. |
| Jobs / Workers | **Dry-run** | Ejecuciones de procesos en modo de validación/simulación previo a operación productiva completa. |
| IA Operativa | **Controlled** | IA enfocada en asistencia controlada con supervisión humana; no se plantea autonomía sin gobierno. |
| Go-live | **Mixto** | Activación por fases con checklists y controles, según criticidad y nivel de madurez de cada módulo. |

## Qué significa para clientes y socios

- El producto **sí está en operación**, pero con capacidades que se habilitan por capas.
- Las funciones de mayor impacto pasan primero por validaciones **read-only/dry-run** antes de abrir escritura controlada.
- Este enfoque reduce riesgo comercial y técnico al escalar funcionalidades sin sobreprometer alcance.
