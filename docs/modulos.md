# Módulos del producto — estado demostrable

## Tabla obligatoria para demo/comercial

| Módulo | Qué se puede mostrar | Estado | Qué no se debe prometer | Próximo paso |
|---|---|---|---|---|
| Core Admin (Auth, tenants, users, roles, permissions, modules) | Login, dashboard y CRUD base | Base operativa | Que todo el ecosistema ampliado está full productivo | QA funcional por rol |
| System Health / Audit | Salud de sistema/DB, logs y auditoría | Base operativa | Cobertura SIEM/observabilidad empresarial completa | Alertamiento centralizado |
| Reports | Consultas y reportes administrativos | Parcial/read-only + controlled en export | Export productivo abierto en todos los entornos | Habilitación gradual de export |
| CRM / Campaigns | Vistas, seguimiento y cockpit | Parcial/controlled | Automatización comercial completa end-to-end | Cierre de lifecycle de lead |
| Landing / URL Locator | Lectura, trazabilidad y simulación | Read-only + dry-run + controlled | Publicación/redirección full activas por defecto | Activación gradual con auditoría |
| Notifications / Mail | Plantillas, preview y cola | Controlled + dry-run | Envío real habilitado globalmente | Activar por entorno con guardrails |
| Workflow | Definiciones/runs y simulación | Dry-run + controlled | Orquestación productiva plena | Apertura de ejecución real por etapas |
| Browser Analytics | Dashboard y lectura de eventos/pageviews | Read-only | Recolección total sin consentimiento/controles | Operación controlada de captura |
| Cloud / Drive | Inventario, resumen y contratos | Read-only + controlled | AWS/S3 remoto full por defecto | Hardening + activación por entorno |
| AI assistance | Asistencia de resumen/propuesta | Dry-run + controlled | IA autónoma sin gobierno humano | Expandir casos con trazabilidad |
| Workers | Estado operativo parcial y documentación | Parcial/roadmap | Workers productivos completos | Implementación progresiva |
| Billing / Integrations / Support | Alcance y roadmap | Roadmap | Módulos terminados y en producción completa | Definición MVP incremental |

## Mensaje de cierre recomendado

La plataforma es demostrable hoy con valor real, pero su habilitación productiva es por capas y controles; no se comunica como SaaS completo en esta etapa.
