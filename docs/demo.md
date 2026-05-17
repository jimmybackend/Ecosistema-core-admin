# Demo pública — guion honesto

> Este documento prepara la demo comercial/técnica sin sobreprometer estado productivo.

## Mensajes obligatorios antes de iniciar

- Este entorno se presenta en modo **seguro**: predomina `read-only`, `dry-run` y `controlled`.
- La IA se muestra como **asistencia con gobierno humano**, no como ejecución autónoma.
- No se exponen datos reales de clientes ni secretos.
- No se promete SaaS completo en esta etapa.

## Tabla de exposición por módulo

| Módulo | Qué se puede mostrar | Estado | Qué no se debe prometer | Próximo paso |
|---|---|---|---|---|
| Core Admin (auth, tenants, users, roles, permissions, modules) | Login, dashboard, CRUD base administrativo | Estable/base operativa | Que todo el ecosistema extendido ya es productivo | Pruebas funcionales por rol |
| System health / audit | Health, logs, eventos de auditoría | Estable/base operativa | SIEM/observabilidad empresarial completa | Alertamiento centralizado |
| Reports | Consultas y vistas de reporte | Read-only + controlled en export | Exportaciones productivas abiertas en todos los entornos | Habilitar export por etapas |
| Campaigns / CRM | Cockpit y seguimiento base | Parcial / controlled | Ciclo comercial 100% automatizado | Completar lifecycle con QA |
| Landing / URL Locator | Vistas, trazabilidad y simulaciones | Read-only + dry-run + controlled | Publicación/redirección full activa por defecto | Apertura gradual por flags |
| Notifications / Mail | Plantillas, previsualización y estado de cola | Controlled + dry-run | Envío SMTP real habilitado globalmente | Activación por entorno con guardrails |
| Workflow | Definiciones, runs y simulación | Dry-run + controlled | Orquestación productiva full | Activar ejecución real gradual |
| Browser Analytics | Dashboard y consultas de eventos/pageviews | Read-only | Recolección total sin controles de privacidad | Operación controlada con consentimiento |
| Cloud / Drive | Inventario y contratos operativos | Read-only + controlled | S3 remoto full activo por defecto | Hardening por entorno |
| AI assistance | Resúmenes/propuestas asistidas | Dry-run + controlled | IA autónoma que actúa sola | Expandir casos con revisión humana |
| Workers / Billing / Integrations / Support | Estado y roadmap documental | Roadmap/parcial | Que estén terminados o productivos end-to-end | Definir MVP y entregas incrementales |

## Guion de 15 minutos (ejecutivo)

1. **Contexto (2 min)**: problema que resuelve y enfoque por etapas.
2. **Base operativa (4 min)**: login, dashboard, auth, permisos, auditoría.
3. **Módulos extendidos (5 min)**: mostrar sólo superficies reales (consulta/simulación).
4. **Controles (2 min)**: flags, permisos, dry-run, no datos reales.
5. **Cierre (2 min)**: próximos pasos por módulo, sin prometer SaaS completo.

## Guion de 30 minutos (detalle)

1. **Narrativa de negocio (5 min)**.
2. **Demo base operativa (8 min)**.
3. **Recorrido campaña→lead→reporte con límites reales (8 min)**.
4. **Seguridad/gobierno IA/privacidad (5 min)**.
5. **Roadmap y criterios de salida productiva (4 min)**.

## Versión técnica

- Enfatizar rutas y modos operativos (`read-only`, `dry-run`, `controlled`).
- Explicar flags y defaults seguros en `.env.example`.
- Aclarar dependencias externas no activas por defecto (SMTP/S3/IA/workers productivos).

## Versión comercial

- Enfatizar valor actual demostrable y trazabilidad.
- Repetir límites: no SaaS completo hoy, no prometer módulos roadmap como cerrados.
- Posicionar roadmap como activación gradual basada en evidencia.
