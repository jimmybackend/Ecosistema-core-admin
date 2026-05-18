# Core Admin — Plan de observabilidad y monitoreo en entorno controlado (PR #257)

- **Fecha base (UTC):** 2026-05-18
- **Repositorio:** `jimmybackend/Ecosistema-core-admin`
- **Ámbito operativo:** demo ampliada, piloto interno y preproducción controlada
- **Resultado objetivo de fase:** **Go con advertencias**
- **Límite formal:** **No habilita producción SaaS pública**

## 1) Propósito

Definir cómo observar y monitorear Core Admin durante:

1. demo ampliada controlada;
2. piloto interno controlado;
3. preproducción controlada.

Este plan busca trazabilidad operativa, detección temprana de riesgos y evidencia auditable sin exponer datos sensibles ni activar operaciones fuera de guardrails.

## 2) Alcance

Incluye, en entornos controlados (local, VM interna o EC2 controlada):

- módulos de Core Admin en estados operativo/read-only/dry-run/controlled;
- monitoreo de logs técnicos sanitizados;
- health checks funcionales mínimos;
- seguimiento de errores HTTP/PHP y sesiones;
- estado de jobs/workers/cron simulados o explícitamente apagados;
- controles básicos de seguridad y rendimiento operativo;
- revisión de disponibilidad básica de disco/DB según entorno.

Fuera de alcance:

- producción SaaS pública;
- telemetría pública externa obligatoria;
- activación de integraciones reales por defecto.

## 3) Principios de privacidad y manejo seguro de evidencia

Reglas obligatorias:

1. no registrar secretos (`.env`, llaves, credenciales, tokens);
2. no registrar passwords ni hashes reales de credenciales;
3. no registrar dumps reales de DB o payloads completos sensibles;
4. no imprimir `payload_json`, `metadata_json` ni `context_json` completos;
5. no exponer IP ni user-agent completos en reportes públicos;
6. no capturar ni usar PII real en bitácoras, pruebas o evidencias;
7. sanitizar capturas/terminal antes de compartir.

## 4) Señales mínimas a monitorear

Durante demo/piloto/preproducción controlada se debe monitorear, como mínimo:

1. disponibilidad de login y dashboard;
2. errores HTTP (especialmente 500/502/503);
3. errores PHP y excepciones visibles;
4. sesiones expiradas/invalidaciones inesperadas;
5. intentos de login fallidos repetidos;
6. warnings de `composer smoke` y `composer schema:usage`;
7. estado de flags críticas (riesgo en `false` por defecto);
8. estado de módulos según política (read-only/dry-run/controlled);
9. uso básico de disco y riesgo de saturación;
10. estado de DB cuando aplique en el entorno;
11. estado de workers/cron como apagados o controlados.

## 5) Logs permitidos

Se permiten únicamente logs con contenido técnico y sanitizado:

- eventos técnicos de ejecución;
- mensajes de error sin secretos;
- IDs internos no sensibles;
- estados generales de módulos/flags;
- métricas agregadas (conteos, tasas, tendencias);
- resultados resumidos de checks operativos.

## 6) Logs prohibidos

Queda prohibido registrar o publicar:

- contenido de `.env`;
- tokens;
- API keys;
- passwords;
- hashes reales de credenciales;
- `payload_json` completo;
- `metadata_json` completo;
- `context_json` completo;
- IP/user-agent completos en documentación pública;
- stack traces sensibles con rutas/secretos;
- datos reales de clientes/colaboradores.

## 7) Health checks sugeridos

Checklist mínimo por corrida:

1. la app responde (home o endpoint de login);
2. login carga correctamente;
3. dashboard carga para usuario permitido;
4. DB disponible si el entorno requiere DB activa;
5. `composer smoke` sin fallos críticos;
6. `composer schema:usage` en OK o warning controlado;
7. flags sensibles apagadas;
8. no hay integraciones externas activas (SMTP/AWS/S3/IA/workers reales/billing).

## 8) Alertas sugeridas para entorno controlado

Alertas mínimas recomendadas (manuales o semi-manuales):

1. fallo crítico de `composer smoke`;
2. error 500 repetido;
3. login no disponible;
4. DB no disponible cuando sí se espera disponible;
5. flag crítica activada por error;
6. aparición de secreto en logs/evidencia;
7. espacio en disco bajo;
8. stack trace sensible visible.

## 9) Procedimiento operativo de revisión

## 9.1 Antes de demo/piloto

1. validar entorno permitido (local/VM/EC2 controlada);
2. confirmar dataset ficticio/sanitizado;
3. confirmar flags de riesgo en `false`;
4. ejecutar validaciones técnicas base (`dump-autoload`, lint, smoke, schema:usage);
5. abrir bitácora de sesión con responsable y hora de inicio.

## 9.2 Durante la sesión

1. observar disponibilidad de login/dashboard;
2. registrar errores HTTP/PHP y eventos de sesión;
3. confirmar que módulos respetan estado esperado (read-only/dry-run/controlled);
4. vigilar ausencia de secretos/PII en logs y pantalla;
5. escalar de inmediato cualquier señal No-Go.

## 9.3 Después de la sesión

1. consolidar hallazgos y clasificación de severidad;
2. documentar resultado Go / Go con advertencias / No-Go;
3. listar advertencias controladas y mitigación;
4. convertir pendientes a backlog con dueño y fecha objetivo;
5. cerrar accesos temporales y evidencias sensibles temporales.

## 10) Criterios de decisión

## Go

- señales mínimas en estado correcto;
- sin errores críticos bloqueantes;
- sin exposición de secretos/PII;
- sin integraciones reales activas fuera de control.

## Go con advertencias (resultado esperado de fase)

- operación estable para entorno controlado;
- warnings menores controlados y documentados;
- warning de `schema:usage` aceptado si se debe exclusivamente a DB no disponible en entorno aislado.

## No-Go

- secretos o datos reales expuestos;
- integraciones externas activas no autorizadas;
- fallos críticos repetidos sin contención;
- errores 500 bloqueantes en flujo principal;
- stack trace sensible visible en interfaz/evidencia.

## 11) Pendientes para backlog

1. integrar monitoreo real al avanzar en preproducción;
2. definir dashboards operativos por módulo;
3. definir retención y acceso de logs por ambiente;
4. definir alertas automáticas y umbrales técnicos;
5. definir sanitización automática de logs/evidencias;
6. re-ejecutar `composer schema:usage` con DB real de verificación disponible.

## 12) Matriz mínima de evidencia requerida

Por cada sesión de demo/piloto/preproducción controlada, registrar:

- fecha/hora inicio-fin (UTC);
- entorno usado (local/VM/EC2 controlada);
- resultado de health checks;
- resultado de `composer smoke` y `composer schema:usage`;
- alertas/incidentes y contención;
- decisión final de sesión (Go / Go con advertencias / No-Go);
- pendientes creados en backlog.

## 13) Declaración de límites

Este plan define observabilidad/monitoreo para **entornos controlados** de Core Admin.

No constituye ni declara habilitación de **producción SaaS pública**.
