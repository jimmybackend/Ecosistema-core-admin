# Core Admin — Plan de piloto interno controlado (PR #254)

- **Fecha base (UTC):** 2026-05-18
- **Repositorio:** `jimmybackend/Ecosistema-core-admin`
- **Precondición:** demo ampliada controlada completada con evidencia auditable
- **Resultado objetivo de fase:** **Go con advertencias**
- **Límite formal:** **No habilita producción SaaS pública**

## 1) Propósito del piloto interno

Ejecutar una operación interna controlada posterior a la demo ampliada para validar repetibilidad operativa, disciplina de guardrails y estabilidad funcional con usuarios internos autorizados, manteniendo datos ficticios/sanitizados y sin side effects externos reales.

Objetivos específicos:

1. consolidar evidencia de operación sostenida en entorno controlado;
2. validar criterios de soporte interno e incidentes menores;
3. medir riesgos bloqueantes antes de hardening preproducción;
4. convertir hallazgos en backlog trazable sin narrativa de salida productiva pública.

## 2) Diferencia entre demo ampliada, piloto interno y producción

| Etapa | Finalidad | Audiencia/usuarios | Datos | Integraciones externas | Resultado esperado |
|---|---|---|---|---|---|
| Demo ampliada controlada | Mostrar cobertura funcional extendida en sesión guiada | Audiencia interna acotada | Ficticios | Apagadas / simuladas | Go con advertencias |
| Piloto interno controlado | Operación interna repetible por ventana temporal definida | Usuarios internos autorizados en rol operativo | Ficticios o sanitizados | Apagadas / simuladas / controlled | Go con advertencias o No-Go técnico |
| Producción SaaS pública | Servicio abierto a clientes externos | Usuarios/tenants reales | Reales con controles de cumplimiento | Habilitadas con hardening | Go productivo (no aplica en este PR) |

## 3) Participantes permitidos

Permitidos (internos):

- líder técnico de Core Admin;
- operador funcional designado;
- seguridad/compliance interno;
- observador de producto;
- responsable de bitácora/reporte.

No permitidos:

- usuarios externos o clientes;
- proveedores sin NDA y sin autorización formal;
- cuentas no nominales compartidas fuera de control.

## 4) Entorno permitido

Solo en:

1. local controlado;
2. VM interna restringida;
3. EC2 temporal controlada con acceso limitado.

No permitido:

- ambientes productivos;
- exposición pública abierta;
- mezcla de datos reales con dataset demo/piloto.

## 5) Datos permitidos y prohibidos

### Permitidos

- dataset ficticio (`DEMO-*`, `CMP-DEMO-*`, `LEAD-DEMO-*`);
- datos sanitizados irreversiblemente anonimizados;
- correos `example.test`;
- métricas sintéticas para dashboards/read-only.

### Prohibidos

- PII real de clientes/colaboradores;
- secretos, tokens, llaves API reales;
- credenciales reales de SMTP/AWS/S3/IA;
- dumps productivos o capturas con datos sensibles.

## 6) Módulos incluidos

Incluidos en el piloto interno controlado:

1. autenticación interna + dashboard operativo;
2. usuarios/roles/permisos (cambios acotados y reversibles);
3. auditoría/health/logs seguros;
4. CRM leads/campaigns en lectura y transiciones controladas de dataset ficticio;
5. landing + URL Locator en modo read-only/controlled;
6. workflow/AI/reportes en dry-run;
7. notificaciones/drive/cloud con simulación controlada sin llamadas remotas.

## 7) Módulos excluidos

Quedan fuera del piloto:

- SMTP real y envío real;
- AWS/S3 remoto y signed URLs reales;
- IA externa con proveedores reales;
- workers/cron reales con side effects;
- billing/cobros reales;
- cualquier flujo que implique exposición pública SaaS.

## 8) Guardrails obligatorios

1. Flags sensibles en `false` durante toda la fase.
2. Acceso sólo por lista autorizada y cuentas nominales.
3. Evidencia sin secretos/PII en bitácora, capturas y reportes.
4. No cambios de esquema, no migraciones, no seeds productivos.
5. No declarar readiness de producción pública.
6. Toda desviación se registra con impacto, contención y dueño.

## 9) Criterios de entrada

Para iniciar piloto interno, se requiere:

- plan de demo ampliada ejecutado con resultado documentado;
- checklist de entorno/flags/dataset validada;
- validaciones técnicas mínimas ejecutadas (`smoke`, lint, schema checks);
- runbook de VM/EC2 controlada vigente;
- designación formal de responsables (operador, seguridad, bitácora).

## 10) Criterios de salida

### Go con advertencias (esperado)

- operación interna repetible por la ventana acordada;
- sin incidentes críticos de seguridad/tenancy;
- advertencias menores controladas y trazadas (incluye warning controlado de `schema:usage` por DB no disponible);
- backlog de mitigación priorizado con responsables y fecha objetivo.

### No-Go

- fuga/exposición de datos sensibles;
- activación accidental de integraciones reales;
- regresión crítica funcional repetida sin contención;
- falta de evidencia mínima auditable.

## 11) Plan de ejecución por fases

## Fase A — Preparación (D-5 a D-1)

- congelar alcance del piloto;
- validar entorno, flags y dataset permitido;
- ejecutar checks técnicos base;
- confirmar lista de participantes autorizados;
- preparar plantilla de bitácora y reporte.

## Fase B — Arranque controlado (Día 1)

- sesión de kickoff con reglas explícitas;
- corrida funcional base por módulos incluidos;
- pruebas negativas de guardrails (bloqueos esperados);
- registro de incidencias y advertencias.

## Fase C — Operación interna sostenida (Día 2–Día 5)

- ventanas operativas internas acotadas;
- seguimiento de estabilidad y trazabilidad de eventos;
- triage diario de incidentes menores;
- validación de disciplina de acceso y evidencia.

## Fase D — Cierre y decisión (Día 5 o fin de ventana)

- consolidar bitácora y resultados;
- clasificar Go con advertencias / No-Go;
- registrar riesgos residuales y mitigaciones;
- abrir siguientes PRs hacia hardening preproducción.

## 12) Validaciones antes / durante / después

### Antes

- `composer dump-autoload`
- `php -l routes/web.php`
- `php -l scripts/smoke-check.php`
- `php -l scripts/schema-compatibility-check.php`
- `php -l scripts/schema-usage-check.php`
- `composer smoke`
- `composer schema:usage`

### Durante

- confirmación recurrente de flags sensibles en `false`;
- monitoreo de logs sin datos sensibles;
- verificación de bloqueos de acciones no permitidas;
- trazabilidad de incidentes y su contención.

### Después

- limpieza de artefactos temporales;
- cierre de accesos temporales;
- consolidación de reporte Go/No-Go;
- carga de pendientes a backlog post-demo.

## 13) Riesgos bloqueantes

1. activación accidental de integraciones reales;
2. evidencia de PII real/secretos en entorno o documentación;
3. ruptura de aislamiento tenant en flujos críticos;
4. fallos críticos repetidos sin mitigación efectiva;
5. ausencia de reporte auditable de cierre.

## 14) Riesgos aceptados (controlados)

1. warning de `schema:usage` en entornos sin DB de verificación;
2. cobertura parcial de integraciones al permanecer en modo simulado;
3. dependencia de validaciones manuales con disciplina operativa.

## 15) Bitácora y reporte esperado

Bitácora mínima del piloto:

- fecha/hora inicio-fin por jornada;
- entorno usado (local/VM/EC2 controlada);
- módulos ejercitados;
- resultado de comandos de validación;
- incidentes, advertencias y contención aplicada;
- decisión diaria y decisión final.

Reporte final esperado:

- clasificación Go / Go con advertencias / No-Go;
- riesgos bloqueantes detectados y estado;
- riesgos aceptados y plan de mitigación;
- pendientes priorizados para roadmap.

## 16) Próximos pasos hacia hardening preproducción

1. cerrar brechas de seguridad/tenancy detectadas en piloto;
2. reforzar observabilidad y alertamiento mínimo accionable;
3. ejecutar evidencia de esquema en entorno con DB de verificación;
4. formalizar runbooks de incidentes, backup/restore y rollback;
5. preparar gate de hardening preproducción con criterios verificables.

## 17) Declaración final obligatoria

Este plan habilita únicamente un **piloto interno controlado** post-demo ampliada.

**No declara ni promete producción SaaS pública.**
