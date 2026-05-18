# Core Admin — Plan de demo ampliada controlada (PR #253)

- **Fecha base (UTC):** 2026-05-18
- **Repositorio:** `jimmybackend/Ecosistema-core-admin`
- **Precondición:** primera demo privada cerrada y readiness maestro vigente
- **Resultado esperado de esta fase:** **Go con advertencias**
- **Límite explícito:** **No habilita producción SaaS pública**

## 1) Propósito de la demo ampliada

Definir y ejecutar una segunda demo controlada posterior a la primera demo privada para:

1. ampliar cobertura funcional con más módulos y transiciones narrativas;
2. reforzar validación manual técnica/operativa sin side effects externos reales;
3. obtener señal de continuidad hacia etapa de piloto interno, sin declarar salida productiva;
4. consolidar evidencia auditable para decisión formal **Go / Go con advertencias / No-Go**.

## 2) Diferencias contra la primera demo privada

Comparada con la demo de 10–15 minutos y foco base, la demo ampliada cambia en:

- **duración:** pasa a 20–30 minutos;
- **cobertura:** incorpora más módulos en un hilo end-to-end;
- **profundidad:** más validaciones manuales y pruebas negativas durante sesión;
- **evidencia:** capturas y bitácora más estructuradas por tramo;
- **decisión:** criterios de Go/No-Go más explícitos para transición a siguiente fase.

Se mantiene sin cambios:

- dataset 100% ficticio;
- integraciones sensibles apagadas (SMTP, AWS/S3/Drive remoto, IA externa, workers reales, billing real);
- prohibición de datos reales, secretos y narrativa de producción pública.

## 3) Audiencia sugerida

Audiencia interna mixta (acotada):

- liderazgo técnico;
- operación/plataforma;
- seguridad/compliance interno;
- producto (observador).

Tamaño recomendado: 6–12 personas, con operador técnico designado y responsable de registro de evidencia.

## 4) Entorno permitido

Solo:

1. local controlado;
2. VM interna restringida;
3. EC2 controlada temporal con acceso limitado.

No permitido:

- exposición pública abierta;
- entorno productivo o con datos reales;
- activación de servicios externos reales.

## 5) Dataset ficticio ampliado

Base de referencia: `docs/demo/CORE_ADMIN_SAFE_DEMO_DATASET.md`.

Extensión recomendada para demo ampliada:

- **Tenant demo:** 1 tenant principal (`DEMO-TNT-001`) + 1 tenant espejo de contraste (`DEMO-TNT-002`) sin mezcla de datos.
- **Usuarios demo:** 5–7 usuarios ficticios (owner, operador, auditor, analista, soporte).
- **Leads demo:** 8–12 registros (`LEAD-DEMO-xxxx`) con estados variados.
- **Campaigns demo:** 3–4 campañas (`CMP-DEMO-xxx`) en `draft/scheduled`.
- **Landing/URL demo:** 2 landings y 3 slugs de URL Locator controlados.
- **Workflow/Reportes:** insumos sintéticos solo para simulación dry-run/controlled.

Reglas obligatorias:

- correos solo `example.test`;
- sin PII real;
- sin secretos/token reales;
- nomenclatura demo consistente para trazabilidad.

## 6) Módulos a mostrar

Cobertura sugerida (20–30 min):

1. Login + Dashboard (operativo).
2. Usuarios / Roles / Permisos (operativo).
3. Auditoría + Health + Logs (operativo con visualización segura).
4. CRM Leads/Campaigns (read-only).
5. Landing + URL Locator (read-only/controlado según ruta).
6. Workflow/AI/Report export (dry-run).
7. Drive/Cloud/Mail notifications (controlled, sin integración real).

## 7) Módulos a mantener read-only

- dashboards de analytics/reporting;
- listados CRM/campaigns;
- vistas de seguimiento y auditoría histórica;
- consultas de landing y atribución.

## 8) Módulos a mantener dry-run

- generación de sugerencias IA;
- ejecución de workflows con acciones simuladas;
- exportes/reportes con escritura deshabilitada;
- previsualizaciones de notificación sin envío.

## 9) Módulos a mantener controlled

- cambios administrativos acotados (roles/permisos demo);
- acciones de URL Locator/Landing permitidas en dataset demo;
- operaciones de Drive/Cloud en modo protegido sin llamada remota;
- transiciones CRM permitidas bajo usuario operador demo.

## 10) Flujo sugerido (20–30 minutos)

## Min 0–3: apertura y guardrails

- declarar alcance y límites (demo ampliada controlada);
- confirmar no producción SaaS pública;
- validar verbalmente datos ficticios y flags sensibles en `false`.

## Min 3–8: núcleo operativo

- login, dashboard y navegación base;
- usuarios/roles/permisos con un cambio controlado reversible;
- health/auditoría/logs (sin exponer payload sensible).

## Min 8–14: recorrido funcional extendido

- CRM/campaigns read-only;
- landing + URL Locator en navegación de evidencia;
- ejemplo de validación de frontera tenant (visual/operativa).

## Min 14–20: simulaciones y control

- workflow/AI/export en dry-run;
- notifications/mail preview sin envío real;
- drive/cloud en modo controlled sin side effects externos.

## Min 20–26: pruebas negativas y preguntas

- verificar bloqueo esperado cuando acción requiere integración real;
- revisar respuesta controlada ante intento no permitido;
- sección de Q&A orientada a riesgos y próximos pasos.

## Min 26–30: cierre y decisión

- recapitular resultados por módulo;
- registrar clasificación preliminar Go/No-Go;
- acordar pendientes y dueños.

## 11) Criterios de decisión

## Go

- validaciones técnicas sin errores críticos;
- flujo demo ejecutado completo;
- sin exposición de datos sensibles;
- sin activación accidental de integraciones reales.

## Go con advertencias (esperado)

- flujo central correcto;
- advertencias controladas documentadas y mitigadas;
- warning de `composer schema:usage` aceptado cuando no existe DB de verificación en entorno aislado.

## No-Go

- falla crítica funcional o de seguridad;
- evidencia de datos reales/secretos en sesión;
- side effects externos no controlados;
- incumplimiento de guardrails obligatorios.

## 12) Riesgos específicos de la demo ampliada

1. **Sobreextensión de alcance** por querer cubrir demasiados módulos.
2. **Confusión de estados** (read-only vs dry-run vs controlled).
3. **Falsa percepción de producción** por narrativa no acotada.
4. **Riesgo de data leak** por capturas sin sanitizar.
5. **Dependencia de entorno** (warning DB no disponible para schema:usage).

Mitigación: moderación estricta del guion, operador técnico + escriba, y checklist de cierre inmediato.

## 13) Validaciones previas obligatorias

- `composer dump-autoload`
- `php -l routes/web.php`
- `php -l scripts/smoke-check.php`
- `php -l scripts/schema-compatibility-check.php`
- `php -l scripts/schema-usage-check.php`
- `composer smoke`
- `composer schema:usage`

Resultado esperado: **Go con advertencias** cuando el warning de `schema:usage` provenga solo de falta de DB en entorno aislado.

## 14) Validaciones posteriores obligatorias

1. Confirmar que flags sensibles permanecen en `false`.
2. Verificar que no se generaron side effects externos.
3. Confirmar limpieza de evidencias (sin secretos/PII).
4. Registrar resultado final y advertencias en reporte post-demo.
5. Convertir hallazgos en pendientes trazables de backlog/roadmap.

## 15) Evidencia que debe capturarse

- bitácora con hora inicio/fin y entorno usado;
- resultado de comandos de validación;
- lista de módulos mostrados/no mostrados;
- incidentes/advertencias y contención aplicada;
- decisión final (Go / Go con advertencias / No-Go);
- acciones con responsable y fecha objetivo.

Toda evidencia debe excluir datos reales, secretos, tokens o credenciales.

## 16) Preguntas esperadas

1. ¿Qué parte está realmente operativa hoy y qué queda en simulación?
2. ¿Qué impide declarar producción SaaS pública ahora?
3. ¿Cuál es el riesgo del warning de `schema:usage` y cómo se cerrará?
4. ¿Qué módulos podrían pasar primero a piloto interno?
5. ¿Qué controles evitan envíos/ejecuciones externas accidentales?
6. ¿Qué evidencia se exige para pasar de demo ampliada a siguiente fase?

## 17) Próximos pasos después de la demo ampliada

1. Publicar reporte post-demo ampliada con clasificación final y evidencia.
2. Actualizar backlog post-demo y roadmap con hallazgos reales.
3. Priorizar cierre de warning de esquema con entorno DB de verificación.
4. Definir alcance y criterio de entrada a piloto interno controlado.
5. Mantener declaración explícita: Core Admin listo para demos controladas, no para SaaS público.
