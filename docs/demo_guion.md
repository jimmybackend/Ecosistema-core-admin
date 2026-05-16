# Guion de demo honesto (10–15 min)

Objetivo: presentar **Ecosistema Esforzados / Core Admin** de forma comercial y técnica, sin exagerar estado de producción.

Duración sugerida total: **12 minutos**.

---

## 0) Apertura (30–45 s)

**Mensaje clave:**
> Hoy vas a ver una plataforma administrativa real con módulos en distintos niveles de madurez: operativo base, read-only, dry-run y controlled por flags.

**Frase recomendada:**
> La meta de esta demo es mostrar valor real hoy y ruta clara de evolución, no prometer automatización total donde aún estamos en despliegue gradual.

**No decir:**
> “Todo está automatizado y listo para producción”.

---

## 1) Problema que resuelve (1–2 min)

- Centraliza operación administrativa dispersa (usuarios, permisos, tenants, auditoría y salud).
- Ordena módulos de negocio del ecosistema bajo controles de seguridad y flags.
- Reduce riesgo operativo al habilitar capacidades por fases (read-only → dry-run → controlled → real).

**Frase recomendada:**
> Este proyecto resuelve primero la gobernanza operativa: quién puede hacer qué, dónde y con qué trazabilidad.

**No decir:**
> “Ya reemplaza todos los procesos manuales del negocio”.

---

## 2) Visión del ecosistema (1–2 min)

- Núcleo: Auth, Core Admin, Health/Audit.
- Módulos extendidos: Cloud/Drive, URL Locator, Landing, CRM, Campaigns, Workflow, Reports, Analytics, AI.
- Principio rector: crecimiento por seguridad operativa, no por activación masiva inmediata.

**Frase recomendada:**
> La visión es unificar operación comercial y técnica en una sola superficie, con activación gradual por riesgo y criticidad.

**No decir:**
> “Ya está todo integrado end-to-end en tiempo real”.

---

## 3) Qué ya se puede mostrar (2 min)

### Demo en vivo recomendada
1. Login y acceso al dashboard.
2. Gestión base: tenants, users, roles, permissions, modules.
3. Superficies de system health y auditoría.
4. Navegación por módulos extendidos con lectura de estado real.

**Frases recomendadas para módulos parciales:**
> Este módulo ya cuenta con estructura canónica y primeras vistas administrativas; las operaciones sensibles avanzan por fases controladas.

> Aquí ya se puede validar consistencia operativa y trazabilidad, aunque la ejecución productiva completa sigue en habilitación progresiva.

**No decir:**
> “Si aparece en menú, ya está full productivo”.

---

## 4) Qué está en modo read-only (1 min)

- Varios listados/tableros y vistas de diagnóstico se muestran sin escritura.
- Sirve para validar datos, estructura y operación sin riesgo de mutación.

**Frase recomendada:**
> En read-only priorizamos visibilidad y control, antes de habilitar cambios de estado en producción.

**No decir:**
> “Read-only significa que ya falta muy poco para producción” (sin evidencias concretas).

---

## 5) Qué está en dry-run (1 min)

- Flujos como redirects, envíos, ejecuciones o propuestas pueden simularse sin efecto final.
- Permite verificar lógica, payloads y auditoría sin impacto externo.

**Frase recomendada:**
> En dry-run medimos confiabilidad del flujo antes de activar efectos reales en canales externos.

**No decir:**
> “Dry-run ya equivale a operación real”.

---

## 6) Qué está controlled por flags (1 min)

- SMTP real, AWS/S3 real, ejecución real de workflow, escrituras sensibles e integraciones externas dependen de flags.
- Defaults seguros: apagado por defecto para proteger entornos.

**Frase recomendada:**
> El sistema está diseñado para activar capacidades por bandera, con rollback rápido y trazabilidad.

**No decir:**
> “Como existe el botón, está habilitado en todos los ambientes”.

---

## 7) Qué está en roadmap (1 min)

- Consolidación de workers productivos completos.
- Cierre de integraciones end-to-end en módulos parciales.
- Hardening operativo y observabilidad avanzada por entorno.

**Frase recomendada:**
> El roadmap prioriza confiabilidad operativa y seguridad antes de escalar automatización.

**No decir:**
> “La fecha de producción total es fija e inamovible” (si no existe compromiso formal aprobado).

---

## 8) Seguridad y privacidad (1–2 min)

- Principio de mínimo privilegio por roles/permisos.
- Auditoría y health como capas visibles de control.
- Datos sensibles y secretos fuera de repositorio.
- En demos: sin datos reales, sin credenciales reales.

**Frase recomendada:**
> La seguridad no se trata como “fase final”; es una restricción de diseño desde el primer módulo.

**No decir:**
> “Estamos seguros porque nadie nos ha reportado incidentes”.

---

## 9) Próximos pasos (45–60 s)

1. Validar checklist de demo readiness por ambiente.
2. Activar un subconjunto controlled para piloto acotado.
3. Medir estabilidad operativa y ajustar flags por evidencia.
4. Definir criterios de salida a productivo por módulo.

**Cierre recomendado:**
> Hoy mostramos una base sólida y honesta: valor operativo actual + plan claro para completar capacidades críticas.

---

## Preguntas difíciles y respuesta honesta

### ¿Ya está listo para clientes?
**Respuesta sugerida:**
Está listo para mostrar valor operativo en escenarios controlados. Para operación comercial amplia, depende del módulo y del ambiente, porque algunas piezas siguen en read-only/dry-run/controlled.

### ¿Ya cobra?
**Respuesta sugerida:**
No presentamos billing completo como capacidad productiva cerrada en este estado. Existe a nivel de roadmap/documentación.

### ¿Ya envía correos?
**Respuesta sugerida:**
El envío real está controlado por flags y configuración de entorno. En demo suele mostrarse en modo seguro para evitar envíos reales no deseados.

### ¿Ya usa IA real?
**Respuesta sugerida:**
Hay superficies de IA y flujos de apoyo, pero su operación externa/productiva depende de flags y proveedor configurado por entorno.

### ¿Ya conecta S3?
**Respuesta sugerida:**
La integración existe a nivel de arquitectura y contratos operativos; la conexión real se habilita de forma controlled por entorno y credenciales.

### ¿Ya tiene soporte completo?
**Respuesta sugerida:**
No se comunica como soporte completo cerrado. Hay base operativa y documentación, con evolución planificada por etapas.

---

## Frases rápidas de respaldo (anti-exageración)

- “Lo que ves en producción potencial no siempre está activado por defecto; privilegiamos seguridad operativa.”
- “Diferenciamos visibilidad funcional de habilitación transaccional real.”
- “Preferimos una verdad incremental comprobable frente a una promesa total no auditada.”
- “Este avance ya es utilizable para gobierno operativo; la automatización plena sigue su roadmap.”
