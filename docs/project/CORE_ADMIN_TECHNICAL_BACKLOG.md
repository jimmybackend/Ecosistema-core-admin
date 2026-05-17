# CORE ADMIN — Technical Backlog de pendientes reales (PR #223)

Fecha de corte: 2026-05-17.  
Repositorio: `jimmybackend/Ecosistema-core-admin`.

## 1) Objetivo y alcance

Este backlog consolida pendientes técnicos reales detectados en la fase de auditorías PR #209 a PR #222 para **Core Admin**.

Incluye únicamente trabajo dentro de este repositorio y separa explícitamente:
- bugs reales,
- mejoras de QA,
- documentación,
- seguridad,
- deuda técnica,
- roadmap.

Fuera de alcance:
- tareas de `Ecosistema-presentacion`,
- tareas de `Ecosistema-bd`,
- activación productiva de integraciones externas por default.

## 2) Fuentes auditadas

- `README.md`
- `docs/project/CORE_ADMIN_MODULE_STATUS.md`
- `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`
- `docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md`
- `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`
- `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md`
- `docs/ops/WORKERS_CRON_CURRENT_STATE.md`
- `routes/web.php`
- `scripts/smoke-check.php`
- `.env.example`
- `docs/project/CORE_ADMIN_POST_208_VERIFICATION_REPORT.md`

## 3) Formato y priorización

Escala de severidad usada: **Crítica / Alta / Media / Baja**.

Cada pendiente incluye:
- título,
- severidad,
- módulo,
- archivos,
- riesgo,
- criterio de cierre,
- PR sugerido futuro.

---

## 4) Backlog técnico priorizado

## A. Seguridad

### ISSUE SEC-01 — Hardening previo a activación real de integraciones externas
- **Severidad:** Crítica
- **Módulo:** Seguridad transversal (Mail/S3/AI/Workflow/Reports)
- **Archivos:** `README.md`, `.env.example`, `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`, `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md`
- **Riesgo:** habilitar SMTP/S3/IA/workflow/export write sin hardening puede exponer PII, secretos y provocar escritura externa no controlada.
- **Criterio de cierre:** checklist de hardening versionado + evidencia de controles mínimos (RBAC, logs, política de secretos, rollback) por capacidad sensible.
- **PR sugerido futuro:** `PR-SEC-HARDENING-GATES`.

### ISSUE SEC-02 — Threat model y observabilidad de rutas públicas
- **Severidad:** Alta
- **Módulo:** URL Locator / Landing público
- **Archivos:** `routes/web.php`, `docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md`, `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`
- **Riesgo:** exposición de `/u/{slug}`, `/l/{slug}`, `POST /l/{slug}/forms/{id}/submit` sin modelo de amenazas formal y telemetría suficiente.
- **Criterio de cierre:** threat model documentado + controles de abuso/traceabilidad definidos para rutas públicas + runbook de incidentes básico.
- **PR sugerido futuro:** `PR-SEC-PUBLIC-ROUTES-THREATMODEL`.

### ISSUE SEC-03 — Revisión de privacidad del flag de user-agent en URL Locator
- **Severidad:** Alta
- **Módulo:** URL Locator / Privacidad
- **Archivos:** `.env.example`, `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`
- **Riesgo:** mantener `ECOSISTEMA_URL_LOCATOR_COLLECT_USER_AGENT=true` como default puede elevar riesgo regulatorio si se activa flujo asociado.
- **Criterio de cierre:** decisión explícita y documentada: default `false` o justificación formal de minimización/retención.
- **PR sugerido futuro:** `PR-SEC-URL-UA-DEFAULT`.

## B. Bugs reales

### ISSUE BUG-01 — Ambigüedad documental de estado "documental/roadmap" vs expectativa operativa
- **Severidad:** Media
- **Módulo:** Estado de módulos (Billing/Workers/Integrations/Support)
- **Archivos:** `README.md`, `docs/project/CORE_ADMIN_MODULE_STATUS.md`, `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`, `docs/ops/WORKERS_CRON_CURRENT_STATE.md`
- **Riesgo:** sobrepromesa funcional al interpretar componentes roadmap como productivos.
- **Criterio de cierre:** mensajes de estado alineados y consistentes en README + matrices, sin contradicciones de capacidad real.
- **PR sugerido futuro:** `PR-DOCS-STATUS-CONSISTENCY`.

## C. Mejoras QA

### ISSUE QA-01 — Cobertura smoke para trazabilidad del nuevo backlog técnico
- **Severidad:** Media
- **Módulo:** QA documental
- **Archivos:** `scripts/smoke-check.php`, `docs/project/CORE_ADMIN_TECHNICAL_BACKLOG.md`
- **Riesgo:** pérdida de consistencia futura si el backlog deja de estar referenciado por reportes de cierre.
- **Criterio de cierre:** smoke valida que el reporte post-208 referencia el backlog técnico y que el documento existe.
- **PR sugerido futuro:** `PR-QA-SMOKE-BACKLOG-TRACE`.

### ISSUE QA-02 — Evidencia estándar de verificación mínima en PRs documentales
- **Severidad:** Baja
- **Módulo:** Proceso QA
- **Archivos:** `docs/project/CORE_ADMIN_LOCAL_VERIFICATION_RUNBOOK.md`, `scripts/smoke-check.php`
- **Riesgo:** PRs documentales sin ejecución consistente de comandos mínimos dificultan auditoría.
- **Criterio de cierre:** plantilla/checklist de PR exige evidencia de `composer dump-autoload`, `php -l`, `composer smoke` (o justificación de entorno).
- **PR sugerido futuro:** `PR-QA-DOC-VERIFICATION-STANDARD`.

## D. Documentación

### ISSUE DOC-01 — Trazabilidad explícita backlog ↔ reporte post-208
- **Severidad:** Baja
- **Módulo:** Documentación de gobernanza técnica
- **Archivos:** `docs/project/CORE_ADMIN_POST_208_VERIFICATION_REPORT.md`, `docs/project/CORE_ADMIN_TECHNICAL_BACKLOG.md`
- **Riesgo:** pendientes estratégicos sin ancla documental principal.
- **Criterio de cierre:** reporte post-208 enlaza explícitamente este backlog como continuación de ejecución.
- **PR sugerido futuro:** `PR-DOCS-POST208-BACKLOG-LINK`.

## E. Deuda técnica

### ISSUE TECH-01 — Completar trazabilidad de rutas/controladores pendientes en el map
- **Severidad:** Alta
- **Módulo:** Inventario técnico de rutas
- **Archivos:** `docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md`, `routes/web.php`
- **Riesgo:** huecos de trazabilidad impiden auditar superficie real y ownership de implementación.
- **Criterio de cierre:** 100% de entradas de rutas críticas con controlador/servicio/estado confirmado o nota explícita de vacío real.
- **PR sugerido futuro:** `PR-TECH-ROUTE-MAP-CLOSURE`.

### ISSUE TECH-02 — Matrices con tablas "no confirmado" en módulos mixtos
- **Severidad:** Media
- **Módulo:** Inventario de datos
- **Archivos:** `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`, `docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md`
- **Riesgo:** decisiones de activación sin visibilidad completa de persistencia real por módulo.
- **Criterio de cierre:** matrices actualizadas con estado confirmado/documental por tabla para módulos mixtos.
- **PR sugerido futuro:** `PR-TECH-MATRIX-CONFIRMATION`.

## F. Roadmap

### ISSUE RDM-01 — Plan de workers/cron productivos por etapas
- **Severidad:** Alta
- **Módulo:** Workers/Operaciones
- **Archivos:** `docs/ops/WORKERS_CRON_CURRENT_STATE.md`, `README.md`
- **Riesgo:** no existe operación productiva completa de workers; riesgo de brecha entre demo y operación real.
- **Criterio de cierre:** roadmap por etapas con precondiciones técnicas, observabilidad y criterios de salida por worker crítico.
- **PR sugerido futuro:** `PR-ROADMAP-WORKERS-PROD-STAGES`.

### ISSUE RDM-02 — Política de activación gradual por módulo controlled
- **Severidad:** Media
- **Módulo:** Gobernanza de features por flags
- **Archivos:** `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md`, `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`, `.env.example`
- **Riesgo:** activaciones ad-hoc sin secuencia estándar incrementan riesgo operativo y de cumplimiento.
- **Criterio de cierre:** guía única de activación gradual con prechecks, rollout, rollback y evidencia requerida.
- **PR sugerido futuro:** `PR-ROADMAP-CONTROLLED-ACTIVATION-POLICY`.

---

## 5) Orden recomendado de ejecución (siguientes PRs)

1. `PR-SEC-HARDENING-GATES`
2. `PR-SEC-PUBLIC-ROUTES-THREATMODEL`
3. `PR-TECH-ROUTE-MAP-CLOSURE`
4. `PR-SEC-URL-UA-DEFAULT`
5. `PR-TECH-MATRIX-CONFIRMATION`
6. `PR-ROADMAP-WORKERS-PROD-STAGES`
7. `PR-QA-SMOKE-BACKLOG-TRACE`
8. `PR-QA-DOC-VERIFICATION-STANDARD`

## 6) Nota de límites

Este backlog **no** incluye ni propone trabajo de `Ecosistema-presentacion` ni `Ecosistema-bd`. Se limita al estado técnico y documental verificable en `jimmybackend/Ecosistema-core-admin`.
