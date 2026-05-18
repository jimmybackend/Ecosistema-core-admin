# Core Admin — Guion ejecutivo/técnico de demo privada controlada (10–15 min)

- **PR:** #244
- **Fecha base (UTC):** 2026-05-18
- **Repositorio:** `jimmybackend/Ecosistema-core-admin`
- **Audiencia:** equipo técnico/operativo (no público general)
- **Duración sugerida:** 10–15 minutos
- **Alcance:** demo privada controlada (no producción SaaS pública)

## 1) Propósito

Este guion permite ejecutar una demo privada controlada de Core Admin sin improvisar, usando solo datos ficticios y manteniendo integraciones externas reales desactivadas.

Objetivos de la sesión:

1. Mostrar capacidades administrativas reales en contexto controlado.
2. Diferenciar explícitamente estados **operativo**, **read-only**, **dry-run** y **controlled**.
3. Evitar mensajes o acciones que sugieran salida a producción SaaS.

---

## 2) Mensaje de apertura (lectura sugerida)

> “Esta es una demo privada controlada de Core Admin, orientada al equipo técnico/operativo. Usamos exclusivamente datos ficticios. Las integraciones externas permanecen desactivadas y no estamos declarando producción SaaS pública. Durante el recorrido vamos a distinguir qué está operativo, qué está en solo lectura, qué corre en simulación dry-run y qué está bajo ejecución controlada.”

---

## 3) Preparación antes de iniciar (checklist rápida)

- [ ] Dataset ficticio cargado o preparado según `CORE_ADMIN_SAFE_DEMO_DATASET.md`.
- [ ] Usuario demo disponible (owner/operador/auditor ficticio).
- [ ] Flags sensibles apagadas (`mail/s3/drive remoto/ia/workflow real/export write`).
- [ ] Confirmado: no existen datos reales de clientes en pantallas de demo.
- [ ] Validaciones técnicas ejecutadas (smoke + schema usage + lint).
- [ ] Capturas sensibles explícitamente prohibidas para toda la sesión.

---

## 4) Guion por minutos (10–15 min)

## Minuto 0–2: contexto y límites

- Declarar alcance: “demo privada controlada”.
- Repetir límites: datos ficticios, sin integración externa real, no producción SaaS.
- Confirmar que el objetivo es operativo/técnico y no comercial público.

## Minuto 2–4: login y dashboard (Operativo)

- Login con usuario demo.
- Mostrar dashboard administrativo base.
- Señalar evidencia de operación interna sin impacto externo.

## Minuto 4–6: usuarios/roles/permisos (Operativo)

- Mostrar gestión interna de usuarios/roles/permisos.
- Aclarar: alcance de gobierno interno, no onboarding público masivo.

## Minuto 6–8: auditoría/system health/logs (Operativo + consulta)

- Mostrar vista de auditoría y health/logs.
- Subrayar trazabilidad y observabilidad interna.
- Evitar mostrar payloads completos con campos sensibles.

## Minuto 8–10: módulo read-only

- Ejemplo recomendado: CRM/Campaigns/Reports en consulta.
- Explicar: “en este tramo solo lectura, sin escritura ni side effects”.

## Minuto 10–12: módulo dry-run

- Ejemplo recomendado: Workflow/AI/Report export en simulación.
- Mostrar respuesta de simulación y control.
- Repetir: “sin ejecución real externa”.

## Minuto 12–14: módulo controlled

- Ejemplo recomendado: Cloud/Drive/Mail notifications en modo controlado.
- Explicar guardrails por flags y permisos.
- Enfatizar: integración documentada, no activada en real.

## Minuto 14–15: cierre, riesgos y siguientes pasos

- Resumir logros demostrados.
- Recordar límites vigentes y advertencia de no producción.
- Registrar resultado: Go / Go con advertencias / No-Go.

---

## 5) Qué mostrar (por estado)

## Operativo

- Login, dashboard, administración base de usuarios/roles/permisos.
- Auditoría, system health y logs de forma segura.

## Read-only

- CRM/Campaigns/Landing/Reports en visualización.
- Sin acciones de escritura o envío real.

## Dry-run

- Simulaciones de workflow/AI/export sin side effects externos.
- Resultados de prueba marcados como simulación.

## Controlled

- Integraciones con guardrails activos (Cloud/Drive/Mail) bajo control explícito.
- Acciones limitadas por flags, permisos y contexto de demo.

---

## 6) Qué NO mostrar

- `.env` completo o parcial.
- Secretos, tokens, passwords, API keys.
- IP completa o user-agent completo en bruto.
- JSON sensible completo.
- Dumps reales de base de datos.
- S3 real, SMTP real, IA externa real, billing real.
- Datos de clientes/personas reales.

---

## 7) Frases permitidas (guía de discurso)

- “Esto está preparado para demo privada controlada.”
- “Este módulo está en modo lectura.”
- “Esta acción es una simulación (dry-run).”
- “Esta integración está documentada pero no activada.”
- “Esto requiere una fase posterior antes de producción.”

## 8) Frases prohibidas

- “Ya está listo para producción SaaS.”
- “Ya podemos usar datos reales.”
- “Ya está conectado a AWS/S3 real.”
- “Ya envía correos reales.”
- “Ya está listo para clientes externos.”

---

## 9) Manejo de preguntas difíciles (respuestas cortas)

**¿Está listo para producción?**  
No. Está listo para demo privada controlada; producción requiere fases adicionales de hardening, operación y gobierno.

**¿Usa datos reales?**  
No. Esta demo usa exclusivamente datos ficticios y sanitizados.

**¿Ya manda correos?**  
No en esta demo. El envío real está desactivado por flags de seguridad.

**¿Ya conecta a S3?**  
No en modo real para esta sesión. La integración se mantiene controlada/no activa.

**¿Ya usa IA externa?**  
No. Cualquier flujo mostrado aquí está en simulación o controlado sin proveedor externo activo.

**¿Qué falta para producción?**  
Hardening adicional, validación integral en entorno controlado con criterios de salida, y cierre de pendientes operativos/seguridad.

**¿Qué pasa si falla `schema:usage` por DB no disponible?**  
Se clasifica como advertencia controlada, se documenta evidencia, y el estado queda “Go con advertencias” para demo privada.

---

## 10) Cierre de demo (lectura sugerida)

> “Cerramos la sesión con estado de demo privada controlada: mostramos capacidades operativas internas, módulos en lectura, simulaciones dry-run y acciones controladas con guardrails activos. Los logros son de validación técnica y operativa en entorno seguro. Los límites siguen vigentes: sin datos reales, sin integraciones externas reales y sin declarar producción SaaS pública. Los siguientes pasos se documentan como backlog para fases posteriores de producción.”

---

## 11) Checklist post-demo

- [ ] Cerrar sesión de usuarios demo.
- [ ] Apagar VM/instancia temporal si aplica.
- [ ] Limpiar datos temporales de la sesión si aplica.
- [ ] Verificar que no se compartieron capturas sensibles.
- [ ] Registrar feedback, riesgos y pendientes.

---

## 12) Resultado de ejecución (plantilla)

- **Fecha:**
- **Presentador:**
- **Audiencia:**
- **Estado:** Go / Go con advertencias / No-Go
- **Observaciones:**
- **Pendientes:**

---

## 13) Resultado esperado para PR #244

Resultado objetivo: **Go con advertencias**, aceptando warning controlado en `composer schema:usage` cuando no haya DB disponible en entorno de demo.
