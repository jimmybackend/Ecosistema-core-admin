# Flujo operativo del ecosistema

## 1) Flujo ideal del producto

El recorrido objetivo (punta-a-punta) sigue siendo:

**Campaña → Short link → Click → Landing → Visita → Formulario → Submission → Lead CRM → Notificación → Workflow → Reporte → IA**

Esta es la **visión integrada** del ecosistema cuando todos los módulos estén habilitados en modo productivo con sus controles completos.

## 2) Flujo disponible actualmente por etapas

Hoy el producto combina etapas en distintos niveles de madurez:

- **Operación parcial**: hay funcionalidad visible y usable, pero no toda la cadena está abierta por defecto.
- **Read-only**: se observa información, sin ejecutar acciones de escritura u operación activa desde admin.
- **Dry-run**: se puede simular comportamiento sin impacto operativo final.
- **Controlled**: la acción existe, pero depende de flags, permisos y contexto operativo.

## 3) Estado real por tramo

| Tramo | Estado | Explicación clara para demo |
|---|---|---|
| Campaña | Parcial | Se puede mostrar cockpit, revisión y seguimiento de campañas; la operación total depende de controles habilitados. |
| Short link | Controlled | Es demostrable la consulta y trazabilidad del link; redirección/edición real sujeta a flags y permisos. |
| Click tracking | Read-only | El tracking se presenta desde vistas analíticas de eventos y navegación, sin acciones activas desde admin. |
| Landing render | Parcial | Se puede enseñar render y experiencia de landing en contexto demo; publicación total no está abierta en todos los casos. |
| Form submit | Parcial | El flujo de formulario es visible y demostrable; su operación completa depende del modo activo y permisos. |
| Submission | Dry-run | Hay simulación y trazabilidad del envío; no siempre implica escritura final en todos los entornos. |
| Lead CRM | Parcial | Existe visualización y seguimiento base de leads; automatizaciones de ciclo completo continúan por etapas. |
| Notification | Controlled | Se puede mostrar plantilla, cola y estado; envío real permanece condicionado por configuración operativa. |
| Workflow | Controlled | Reglas y corridas pueden demostrarse en modo controlado/simulado; ejecución productiva total no está abierta por defecto. |
| Reports | Parcial | Los reportes y métricas administrativas son enseñables; ciertas salidas/acciones siguen condicionadas por modo. |
| AI assistance | Dry-run | IA funciona como apoyo para resumir, sugerir y asistir; no ejecuta acciones autónomas en operación real. |

## 4) Nota de seguridad operativa

Las acciones con impacto real se habilitan de forma progresiva y controlada. Según el caso, requieren:

- **flags de feature**,
- **permisos por rol**,
- y **aprobación humana** antes de ejecutar cambios o envíos en producción.

## 5) Qué se puede mostrar en demo hoy

Para una demo comercial honesta y defendible, hoy se puede enfatizar:

- Visión completa del recorrido de negocio.
- Evidencia de trazabilidad (campaña, link, click, visita, lead, reporte).
- Simulación controlada en tramos sensibles (submission, notification, workflow).
- IA como copiloto operativo (resumen, propuesta y asistencia), no como ejecutor autónomo.

## 6) Qué viene después

Siguiente avance esperado del flujo:

- Apertura gradual de etapas hoy controladas.
- Mayor continuidad entre eventos y automatizaciones de punta-a-punta.
- Más capacidades de ejecución con controles de seguridad y auditoría.
- Evolución de IA desde asistencia a recomendaciones más contextualizadas, manteniendo revisión humana.

---

Para alinear mensajes de presentación, alcances y límites de operación, ver también [FAQ](./faq.md).
