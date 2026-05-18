# Core Admin — Backlog post-demo privada y roadmap hacia producción controlada (PR #252)

- **Fecha base (UTC):** 2026-05-18
- **Repositorio:** `jimmybackend/Ecosistema-core-admin`
- **Estado actual consolidado:** **Listo para demo privada controlada**
- **Resultado ejecutivo vigente:** **Go con advertencias**
- **Límite formal vigente:** **No habilitado para producción SaaS pública**

## 1) Resumen ejecutivo

Core Admin cerró exitosamente su readiness para demo privada controlada (PR #251), con paquete documental y operativo trazable para ejecución en entorno controlado.

Este documento define el **backlog post-demo** y un **roadmap por fases** para transitar de demo privada a una posible producción controlada. El objetivo es evitar sobrepromesas: hoy existe capacidad para demo controlada, pero aún no para SaaS público.

Declaración de gestión:

- se mantiene criterio **Go con advertencias** para la etapa de demo;
- no se habilitan integraciones reales (SMTP/AWS-S3/IA/workers/billing);
- cualquier avance a fases superiores requiere gates formales Go/No-Go.

## 2) Estado actual

### 2.1 Estado habilitado

- Core Admin está **listo para demo privada controlada**.
- Existen checklists, runbooks, guion de demo y reporte post-demo.
- El warning de `composer schema:usage` por ausencia de DB de verificación se acepta sólo como riesgo controlado y documentado.

### 2.2 Límite explícito

Core Admin **no** está listo para producción SaaS pública.

No se declara readiness productivo para:

- exposición pública abierta;
- operación 24x7 con SLO/SLA;
- procesamiento de datos reales de clientes;
- side effects externos productivos.

## 3) Pendientes críticos antes de considerar producción

1. **Cerrar warning de esquema con evidencia fuerte**
   - Re-ejecutar `composer schema:usage` en entorno con DB de verificación controlada.
   - Publicar evidencia versionada del resultado y desvíos.
2. **Definir gates de hardening obligatorios**
   - Seguridad, observabilidad, privacidad, operación, rollback y continuidad.
3. **Asegurar trazabilidad tenant-end-to-end**
   - Verificar aislamiento de datos por tenant en rutas críticas y reportes/export.
4. **Formalizar readiness operativa**
   - Backups/restore, runbooks de incidentes y ventanas de mantenimiento.
5. **Validar capacidad de operación sostenida**
   - Criterios de salud, alertas, logs accionables y soporte interno.

## 4) Pendientes de seguridad

- Threat model formal para rutas públicas (`/u/{slug}`, `/l/{slug}`, submit públicos).
- Revisión y endurecimiento de RBAC en acciones controlled.
- Política de secretos: inyección segura, rotación y no exposición en logs/docs.
- Revisión de privacidad de telemetría (incluyendo user-agent cuando aplique).
- Checklist de hardening de producción ejecutado con evidencia (no sólo declarado).

## 5) Pendientes de datos y tenancy

- Confirmar aislamiento por tenant en consultas, writes controlled y exportes.
- Definir contratos de minimización/retención para datos sensibles.
- Estándar de dataset de pruebas por ambiente (demo, piloto, preprod).
- Bloqueo operativo de uso de PII real en entornos no productivos.
- Validación cruzada código ↔ esquema real en ciclos regulares.

## 6) Pendientes de integraciones externas

Mantener desactivado por defecto:

- SMTP real;
- AWS/S3 remoto;
- IA externa;
- workers/cron reales;
- billing real.

Antes de habilitar cualquier integración:

- runbook de activación/rollback;
- permisos mínimos por servicio;
- monitoreo y auditoría de side effects;
- pruebas de falla y degradación controlada.

## 7) Pendientes de QA y manual testing

- Ampliar smoke/regresión para rutas críticas admin + públicas controlled.
- Ejecutar checklist manual E2E por fase (demo ampliada, piloto, preprod).
- Definir casos negativos de seguridad/tenancy/flags.
- Estandarizar evidencia de validaciones en cada PR de avance.

## 8) Pendientes de observabilidad y logging

- Baseline de métricas: errores, latencia, throughput, fallas de dependencias.
- Logging estructurado por módulo y correlación por request/session.
- Alertas mínimas para incidentes de seguridad y fallos funcionales críticos.
- Runbook de triage con tiempos objetivo internos.

## 9) Pendientes de despliegue VM/EC2

- Endurecer runbook VM/EC2 para operación repetible en entornos controlados.
- Checkpoint de configuración segura por ambiente (sin secretos en repo).
- Procedimiento de backup/restore probado en simulacro.
- Matriz de rollback ante despliegues fallidos.

## 10) Pendientes de documentación

- Mantener sincronía entre cierre maestro, reporte post-demo y backlog técnico.
- Publicar matriz de estado por fase (qué está permitido/prohibido).
- Documentar criterios de aceptación por módulo antes de piloto interno.
- Consolidar plan de transición demo → piloto → hardening preproducción.

## 11) Roadmap sugerido por fases

## Fase 1 — Demo privada (estado actual)

Objetivo: ejecución controlada con audiencia interna/acotada.

- Resultado objetivo: **Go con advertencias**.
- Integraciones reales: **desactivadas**.
- Datos: **ficticios/sintéticos**.

## Fase 2 — Demo ampliada controlada

Objetivo: ampliar cobertura funcional bajo guardrails.

- Incrementar evidencia funcional y manual testing.
- Reducir advertencias no críticas repetitivas.
- Mantener side effects externos reales apagados.

## Fase 3 — Piloto interno

Objetivo: operación interna más sostenida, aún no pública.

- Entorno más estable y monitoreado.
- Reglas operativas internas de soporte e incidentes.
- Validaciones de tenancy/seguridad más estrictas.

## Fase 4 — Hardening preproducción

Objetivo: cerrar brechas de seguridad, observabilidad y operación.

- Threat model aplicado y mitigaciones cerradas.
- Backups/restore/rollback probados.
- Gate de esquema sin warning por falta de DB en entorno objetivo.

## Fase 5 — Evaluación de producción SaaS

Objetivo: decidir si hay condiciones reales de salida a producción.

- Evaluación ejecutiva/técnica final.
- Sin cumplimiento de criterios, se mantiene No-Go para SaaS público.

## 12) Criterios Go/No-Go por fase

| Fase | Go | Go con advertencias | No-Go |
|---|---|---|---|
| Fase 1 Demo privada | Checklist completo + smoke/lint OK | warning controlado `schema:usage` por DB no disponible | fallos críticos, datos reales, secretos o integraciones reales activas |
| Fase 2 Demo ampliada | evidencia ampliada + regresión estable | warnings menores documentados sin impacto | brechas de seguridad/tenancy sin mitigación |
| Fase 3 Piloto interno | operación interna repetible + monitoreo base | incidentes menores controlados | incidentes recurrentes sin contención |
| Fase 4 Hardening preprod | hardening/rollback/backup verificados | desvíos menores con plan y fecha | bloqueantes de seguridad/operación abiertos |
| Fase 5 Evaluación SaaS | criterios técnicos y de riesgo cerrados | no aplica como estado final recomendado | cualquier brecha crítica abierta |

## 13) Riesgos aceptados (actuales)

1. Warning controlado de `schema:usage` en entornos aislados sin DB de verificación.
2. Cobertura parcial de integraciones reales al mantenerse en modo disabled/controlled.
3. Dependencia de ejecución disciplinada de checklists manuales en etapas tempranas.

## 14) Riesgos bloqueantes

1. Activación accidental o no controlada de integraciones reales.
2. Exposición de datos reales/PII/secretos en demo, logs o documentación.
3. Evidencia insuficiente de aislamiento tenant en rutas críticas.
4. Ausencia de plan probado de incidentes, backup/restore o rollback.
5. Persistencia de hallazgos críticos de seguridad sin mitigación.

## 15) Próximos PRs sugeridos

1. **PR-252A:** Evidencia `schema:usage` con DB de verificación controlada.
2. **PR-252B:** Threat model y mitigaciones de rutas públicas.
3. **PR-252C:** Checklist de hardening preproducción ejecutable con evidencias.
4. **PR-252D:** Matriz de tenancy y pruebas negativas obligatorias.
5. **PR-252E:** Plan de observabilidad/alertas y runbook de incidentes.
6. **PR-252F:** Plan de activación gradual de integraciones externas con rollback.

## 16) Declaración final

Core Admin queda en **Go con advertencias para demo privada controlada**.

Este documento **no autoriza producción SaaS pública** y fija el marco de pendientes y fases necesarias para evaluar, de forma controlada, cualquier transición futura.
