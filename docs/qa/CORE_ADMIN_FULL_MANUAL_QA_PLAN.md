# Core Admin — Plan QA manual completo módulo por módulo (PR #256)

- **Fecha base (UTC):** 2026-05-18
- **Repositorio:** `jimmybackend/Ecosistema-core-admin`
- **Estado objetivo de salida QA:** **Go con advertencias**
- **Límite formal:** este plan **no** declara producción SaaS pública.

## 1) Propósito

Establecer un plan de QA manual integral, técnico y trazable para validar Core Admin módulo por módulo antes de avanzar de demo ampliada hacia piloto interno/preproducción controlada, manteniendo guardrails de seguridad, privacidad y operación sin integraciones reales.

## 2) Alcance

Incluye:

- verificación funcional manual por módulo (UI/rutas/acciones);
- validación de permisos y guardrails (RBAC + flags);
- validación de datos permitidos/prohibidos;
- clasificación final por criterios **Go / Go con advertencias / No-Go**;
- registro de evidencia segura y hallazgos convertibles a backlog.

No incluye:

- cambios de esquema/migraciones;
- activación de SMTP/AWS-S3/IA externa/workers/billing reales;
- uso de datos reales/PII real/secretos;
- declaración de salida a SaaS público.

## 3) Ambiente recomendado

Ambientes permitidos para ejecución QA:

1. local controlado;
2. VM interna restringida;
3. EC2 controlada temporal con acceso acotado.

Condiciones obligatorias:

- flags sensibles en `false` por defecto;
- dataset 100% ficticio;
- acceso de QA con perfiles separados (`qa_admin`, `qa_operator_ro`, `qa_limited`, `public`);
- bitácora de evidencia sin secretos ni datos reales.

## 4) Datos permitidos

- tenants/usuarios demo (`DEMO-*`);
- emails de prueba (`example.test`);
- leads/campaigns sintéticos (`LEAD-DEMO-*`, `CMP-DEMO-*`);
- IDs técnicos de ejemplo sin relación con personas reales;
- logs/auditoría con payload sanitizado.

## 5) Datos prohibidos

- PII real (nombres, teléfonos, emails corporativos reales, direcciones reales);
- credenciales reales, tokens, llaves, secretos;
- datos productivos de clientes;
- exportes con datos sensibles no anonimizados;
- capturas que expongan secretos del entorno.

## 6) Criterios generales de salida

## Go

- flujo crítico auth/dashboard estable;
- módulos en alcance verificados con evidencias completas;
- guardrails activos y respetados;
- sin hallazgos críticos abiertos.

## Go con advertencias (objetivo esperado)

- flujo crítico estable;
- hallazgos menores/medios con mitigación y dueño;
- warning controlado de `composer schema:usage` aceptado cuando la DB de verificación no esté disponible en entorno aislado;
- sin exposición de datos sensibles ni side effects reales.

## No-Go

- falla crítica en auth/RBAC/tenancy/seguridad;
- activación no autorizada de integración real;
- evidencia de datos reales/secretos;
- regresiones severas sin workaround documentado.

## 7) Matriz de módulos a probar

| Módulo | Estado esperado | Resultado QA esperado |
|---|---|---|
| Auth/Login/Register controlado | Operativo controlado | Login/logout/reset controlado + bloqueo de registro fuera de política |
| Dashboard | Operativo | Widgets/counters/render sin error crítico |
| Users/Roles/Permissions | Operativo controlado | CRUD administrativo + pruebas negativas RBAC |
| System/Audit/Logs/Health | Operativo/read-only | Visualización estable y auditable |
| Onboarding | Controlled/dry-run | Ejecución de pasos simulados sin side effects externos |
| Cloud/Drive | Controlled | Navegación metadata + bloqueo de remoto real |
| Mail/Mail Notifications | Controlled/dry-run | Preview/cola simulada sin envío SMTP real |
| Landing | Read-only/controlled | Render/admin controlado y submit bajo flags |
| Browser Analytics | Read-only/controlled | Consultas visibles + collector write bloqueado |
| CRM | Read-only/controlled | Leads/followups/campaigns en dataset demo |
| Campaigns | Read-only/controlled | Cockpit estable + creación sólo dry-run/controlada |
| Workflow | Dry-run/controlled | Simulación y bloqueo de ejecución real |
| Reports | Read-only/dry-run | Visualización y export dry-run sin PII real |
| AI/VitaOS | Dry-run/controlled | Asistencia simulada/bloqueada sin proveedor externo |

## 8) Checklist por módulo

> Formato fijo en cada módulo: **rutas/pantallas**, **acciones permitidas**, **acciones prohibidas**, **datos esperados**, **privacidad/sensibles**, **criterio Go/No-Go**, **evidencia segura**.

### 8.1 Auth/Login/Register controlado

- **Rutas/pantallas:** `/login`, `/logout`, `/dashboard` (redirect), pantallas de auth controladas por política interna.
- **Acciones permitidas:** login con usuario demo válido, logout, validación de error de credenciales inválidas, expiración de sesión por timeout.
- **Acciones prohibidas:** alta libre no autorizada, bypass de auth, uso de usuarios reales.
- **Datos esperados:** cuentas demo internas y mensajes controlados sin stacktrace.
- **Privacidad/sensibles:** ocultar credenciales en capturas; nunca registrar password.
- **Criterio Go/No-Go:** Go si auth base funciona y bloqueos no autorizados responden controlado.
- **Evidencia segura:** captura de login OK/fail + evidencia de redirección y logout.

### 8.2 Dashboard

- **Rutas/pantallas:** `/dashboard`.
- **Acciones permitidas:** abrir dashboard autenticado, validar render de tarjetas/indicadores.
- **Acciones prohibidas:** exponer métricas con datos reales.
- **Datos esperados:** datos sintéticos consistentes con tenant demo.
- **Privacidad/sensibles:** no mostrar identificadores sensibles en screenshots.
- **Criterio Go/No-Go:** Go si carga estable sin 500 y métricas coherentes demo.
- **Evidencia segura:** captura dashboard + timestamp.

### 8.3 Users/Roles/Permissions

- **Rutas/pantallas:** `/users`, `/roles`, `/permissions`, `/modules`.
- **Acciones permitidas:** CRUD controlado sobre entidades demo, asignación de roles/permisos demo, prueba negativa con `qa_limited`.
- **Acciones prohibidas:** modificar cuentas reales o permisos fuera de alcance QA.
- **Datos esperados:** entidades demo trazables y reversibles.
- **Privacidad/sensibles:** no exponer correos reales ni hashes/sesiones.
- **Criterio Go/No-Go:** Go si RBAC aplica correctamente (200 autorizado / 403 no autorizado).
- **Evidencia segura:** capturas antes/después + evidencia 403 en usuario limitado.

### 8.4 System/Audit/Logs/Health

- **Rutas/pantallas:** `/system/health`, `/system/logs`, `/system/audit`, `/audit/events`.
- **Acciones permitidas:** consulta de salud, logs, auditoría y eventos.
- **Acciones prohibidas:** edición destructiva de logs o exposición de secretos.
- **Datos esperados:** trazas técnicas sanitizadas, estado coherente del entorno.
- **Privacidad/sensibles:** enmascarar tokens/headers y datos de sesión.
- **Criterio Go/No-Go:** Go si observabilidad básica está disponible sin filtrar secretos.
- **Evidencia segura:** capturas de cada vista + nota de sanitización aplicada.

### 8.5 Onboarding

- **Rutas/pantallas:** vistas de onboarding habilitadas en Core Admin.
- **Acciones permitidas:** revisar pasos/estado y ejecutar simulaciones permitidas.
- **Acciones prohibidas:** ejecuciones con side effects externos.
- **Datos esperados:** corridas demo y estados coherentes en modo controlado/dry-run.
- **Privacidad/sensibles:** sin datos personales reales en formularios de onboarding.
- **Criterio Go/No-Go:** Go si flujo es trazable y controlado sin efectos externos.
- **Evidencia segura:** captura de pasos + resultado de simulación.

### 8.6 Cloud/Drive

- **Rutas/pantallas:** `/cloud`, vistas de Drive, buckets, acceso, summary.
- **Acciones permitidas:** navegar metadata y operaciones simuladas.
- **Acciones prohibidas:** upload/download remoto real, signed URL real en producción.
- **Datos esperados:** archivos/carpetas demo, respuesta de bloqueo o dry-run.
- **Privacidad/sensibles:** no exponer rutas reales, keys, ARN o credenciales.
- **Criterio Go/No-Go:** Go si guardrails bloquean remoto real y UI permanece estable.
- **Evidencia segura:** captura de vistas + mensaje de bloqueo/control.

### 8.7 Mail/Mail Notifications

- **Rutas/pantallas:** módulo Mail, templates, preview, cola/notificaciones.
- **Acciones permitidas:** previsualizar plantillas y ejecutar dry-run de envío.
- **Acciones prohibidas:** envío SMTP real.
- **Datos esperados:** mensajes simulados y trazabilidad de cola mock.
- **Privacidad/sensibles:** no incluir correos reales ni contenido sensible.
- **Criterio Go/No-Go:** Go si no hay envío real y feedback de bloqueo es claro.
- **Evidencia segura:** captura preview + evidencia de no-envío real.

### 8.8 Landing

- **Rutas/pantallas:** admin de landings y rutas públicas controladas `/l/{slug}`.
- **Acciones permitidas:** revisión de páginas/forms demo y pruebas de render controlado.
- **Acciones prohibidas:** publicación abierta no autorizada o submit real productivo.
- **Datos esperados:** landings demo, submit dry-run/controlado según flags.
- **Privacidad/sensibles:** prohibido contenido con PII real.
- **Criterio Go/No-Go:** Go si render/submit obedecen flags y no exponen datos reales.
- **Evidencia segura:** captura de vista admin + respuesta pública controlada.

### 8.9 Browser Analytics

- **Rutas/pantallas:** dashboards/listados analytics y collector técnico.
- **Acciones permitidas:** consultar pageviews/eventos demo y validar collector en modo write-off.
- **Acciones prohibidas:** ingesta real no controlada de tracking.
- **Datos esperados:** métricas sintéticas y respuestas controladas en collector.
- **Privacidad/sensibles:** no recolectar IP/identificadores reales en evidencia.
- **Criterio Go/No-Go:** Go si consultas son estables y escritura real permanece bloqueada.
- **Evidencia segura:** captura dashboards + request/response controlado del collector.

### 8.10 CRM

- **Rutas/pantallas:** leads, detalle, followups y relaciones de campaña CRM.
- **Acciones permitidas:** consultas y transiciones permitidas en dataset demo.
- **Acciones prohibidas:** manipulación de datos reales o automatismos externos.
- **Datos esperados:** registros `LEAD-DEMO-*` coherentes.
- **Privacidad/sensibles:** no registrar teléfonos/emails reales.
- **Criterio Go/No-Go:** Go si navegación y estados demo son consistentes.
- **Evidencia segura:** capturas de listado/detalle/followups con datos ficticios.

### 8.11 Campaigns

- **Rutas/pantallas:** cockpit y flujos de campañas.
- **Acciones permitidas:** visualización y dry-run/control de creación/atribución.
- **Acciones prohibidas:** activación de campañas reales o envíos externos.
- **Datos esperados:** campañas `CMP-DEMO-*` en draft/scheduled controlado.
- **Privacidad/sensibles:** sin audiencias reales ni segmentos productivos.
- **Criterio Go/No-Go:** Go si cockpit responde y creación real queda bloqueada/simulada.
- **Evidencia segura:** captura cockpit + resultado dry-run.

### 8.12 Workflow

- **Rutas/pantallas:** templates, reglas y ejecución de workflow.
- **Acciones permitidas:** revisar definición y ejecutar dry-run.
- **Acciones prohibidas:** ejecución real de acciones externas.
- **Datos esperados:** salida de simulación con trazabilidad por paso.
- **Privacidad/sensibles:** sin payloads con secretos o PII real.
- **Criterio Go/No-Go:** Go si workflow simula correctamente y execution real está bloqueada.
- **Evidencia segura:** captura de plantilla/regla + resultado simulación.

### 8.13 Reports

- **Rutas/pantallas:** funnel, lead performance, exports.
- **Acciones permitidas:** consulta de reportes y export dry-run.
- **Acciones prohibidas:** export real con PII o distribución externa.
- **Datos esperados:** reportes demo y simulación de export.
- **Privacidad/sensibles:** no incluir PII real ni archivos sensibles en evidencia.
- **Criterio Go/No-Go:** Go si visualización y dry-run funcionan bajo guardrails.
- **Evidencia segura:** capturas de reportes + respuesta de export dry-run.

### 8.14 AI/VitaOS

- **Rutas/pantallas:** asistencia AI, resumen de lead, insight de campaña (dry-run).
- **Acciones permitidas:** invocar funciones simuladas/bloqueadas según configuración.
- **Acciones prohibidas:** llamada a proveedor IA externo real.
- **Datos esperados:** respuesta mock/controlada con mensaje de guardrail.
- **Privacidad/sensibles:** nunca enviar datos reales de clientes/prompts con secretos.
- **Criterio Go/No-Go:** Go si no se dispara proveedor externo y UX reporta estado controlado.
- **Evidencia segura:** captura de respuesta dry-run/bloqueada.

## 9) Manejo de bugs

Clasificación mínima:

- **Severidad:** Crítica / Alta / Media / Baja.
- **Prioridad:** P0 / P1 / P2 / P3.
- **Tipo:** Funcional / Seguridad / Privacidad / Datos / UX / Observabilidad.

Reglas:

- Crítica/Alta en auth, permisos, tenancy, seguridad o datos sensibles => candidato directo a **No-Go**.
- Media/Baja con mitigación y owner/fecha => puede mantener **Go con advertencias**.
- Todo hallazgo debe mapearse a backlog con responsable, ETA y criterio de cierre.

## 10) Formato de registro de hallazgos

Plantilla sugerida por hallazgo:

- `ID`: QA-YYYYMMDD-###
- `Módulo`:
- `Ruta/Pantalla`:
- `Precondición`:
- `Paso a paso`:
- `Resultado esperado`:
- `Resultado actual`:
- `Severidad/Prioridad`:
- `Impacto`:
- `Evidencia` (captura/log sanitizado):
- `Owner`:
- `Estado` (Abierto/En curso/Cerrado):
- `Decisión gate` (Afecta Go/No-Go: Sí/No):

## 11) Salida esperada del QA

Entrega final obligatoria:

1. matriz de módulos completa con estado por caso (`PASS`, `PASS con advertencia`, `FAIL`, `Pendiente entorno`);
2. consolidado de hallazgos con severidad y dueños;
3. clasificación final de gate: **Go / Go con advertencias / No-Go**;
4. lista de pendientes para backlog priorizada por riesgo;
5. evidencia técnica de validaciones ejecutadas.

## 12) Validaciones técnicas mínimas (pre/post QA)

```bash
composer dump-autoload
php -l routes/web.php
php -l scripts/smoke-check.php
php -l scripts/schema-compatibility-check.php
php -l scripts/schema-usage-check.php
composer smoke
composer schema:usage
```

Resultado esperado de esta fase documental: **Go con advertencias**, aceptando warning controlado de `schema:usage` cuando no exista DB disponible en entorno aislado.
