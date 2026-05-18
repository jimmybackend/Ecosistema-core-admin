# Core Admin — Runbook de ejecución de demo privada controlada (PR #240)

## 1) Objetivo de la demo

Ejecutar una demostración técnica privada de Core Admin mostrando capacidades internas reales del repositorio en estado **operativo / read-only / dry-run / controlled**, sin activar servicios externos ni usar datos reales.

> Este runbook no declara readiness de producción SaaS pública.

## 2) Duración sugerida

- Preparación técnica previa: 20–30 min.
- Ejecución demo: 30–45 min.
- Cierre + limpieza: 10–15 min.
- Duración total recomendada: 60–90 min.

## 3) Preparación del entorno

1. Trabajar en `jimmybackend/Ecosistema-core-admin`.
2. Usar `.env` local/VM derivado de ejemplos seguros (`.env.example` / `.env.vm.example`).
3. Confirmar dataset ficticio (`example.test`, prefijos `DEMO-`).
4. Confirmar flags sensibles desactivadas (mail, s3/drive remoto, ia proveedor, workflow ejecución, export write/pii, registration).
5. Confirmar que no hay llaves/tokens reales visibles en terminal ni en UI.

## 4) Validaciones antes de iniciar

Ejecutar en orden:

```bash
composer dump-autoload
composer smoke
composer schema:usage
```

Validación complementaria recomendada:

```bash
php -l routes/web.php
php -l scripts/smoke-check.php
php -l scripts/schema-compatibility-check.php
php -l scripts/schema-usage-check.php
```

## 5) Manejo del resultado de validaciones

- Si `composer smoke` falla por error de aplicación: **No iniciar demo** hasta corregir.
- Si `composer schema:usage` sale con warning por DB no disponible en entorno local: marcar **Go con advertencias**, documentar causa y continuar solo con recorrido controlado de UI no destructivo.
- Si aparece cualquier evidencia de secretos o datos reales: **No-Go inmediato**.

## 6) Flujo sugerido de navegación (demo)

1. **Contexto y límites (2–3 min)**
   - Explicar que es demo privada controlada y no producción pública.
2. **Auth + Dashboard (5 min)**
   - Login de usuario demo, acceso a dashboard, logout planificado.
3. **Núcleo administrativo (10–15 min)**
   - Tenants, Users, Roles, Permissions, Modules (alcance interno).
4. **System/Audit (5–8 min)**
   - Health, logs, audit en lectura.
5. **Módulos en modo read-only/dry-run/controlled (10–15 min)**
   - CRM/Campaigns/Landing/URL Locator/Reports/Workflow según flags seguras.
6. **Cierre de alcance (2–3 min)**
   - Reiterar límites: sin SMTP real, sin S3 real, sin IA externa real, sin workers productivos.

## 7) Qué decir y qué no prometer

### Qué decir

- “Esta funcionalidad está en modo read-only.”
- “Aquí mostramos dry-run/simulación sin efectos externos.”
- “Las integraciones reales están intencionalmente desactivadas por seguridad.”
- “La evidencia de gate técnico para demo es smoke + schema usage.”

### Qué no prometer

- No afirmar salida a producción SaaS.
- No prometer envíos reales de correo desde demo.
- No prometer uploads/downloads reales a S3/AWS.
- No prometer ejecución real de proveedores IA ni workers productivos.
- No prometer billing real ni exportes con PII.

## 8) Cómo explicar módulos read-only

- Enfatizar que el objetivo es visualización/diagnóstico/consulta.
- Indicar explícitamente que no hay escritura operativa en ese recorrido.
- Referenciar su estado como decisión de control de riesgo para demo.

## 9) Cómo explicar módulos dry-run

- Aclarar que dry-run simula lógica de negocio sin side effects externos.
- Mostrar resultado esperado de simulación (status/mensaje/control).
- Confirmar que no se ejecutan llamadas reales a terceros.

## 10) Cómo explicar integraciones desactivadas

- Mail/SMTP: bloqueado por flags `MAIL_SEND_ENABLED=false` y `MAIL_ALLOW_TEST_SEND=false`.
- Cloud/Drive remoto: bloqueado por flags S3/Drive remotas en `false`.
- IA externa: bloqueada por `ECOSISTEMA_AI_PROVIDER_ENABLED=false`.
- Workflow ejecución real: bloqueada por `ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED=false`.
- Export sensible: bloqueado por `ECOSISTEMA_REPORT_EXPORT_WRITE=false` y `ECOSISTEMA_REPORT_EXPORT_INCLUDE_PII=false`.

## 11) Qué hacer si falla `schema:usage` por DB no disponible

1. Confirmar que el fallo corresponde a warning controlado de conectividad/DB ausente.
2. Registrar evidencia textual del warning.
3. Declarar resultado de corrida como **Go con advertencias**.
4. Ejecutar demo enfocada en rutas UI controladas no destructivas.
5. Registrar pendiente para re-ejecución en entorno con DB de verificación controlada.

## 12) Capturas seguras y prohibidas

### Capturas seguras

- Pantallas de login/dashboard con usuario demo ficticio.
- Vistas administrativas sin PII real.
- Pantallas de bloqueo por flags (guardrails).
- Resultados dry-run sin secretos.

### Capturas prohibidas

- `.env` completo o fragmentos con credenciales.
- Consola con tokens, passwords, hostnames sensibles o secretos.
- Datos de clientes reales (emails, teléfonos, documentos, IDs).
- Configuración de infraestructura real (AWS keys, SMTP reales, endpoints privados).

## 13) Cierre de demo

- Confirmar verbalmente alcance ejecutado y límites no productivos.
- Declarar resultado: Go / Go con advertencias / No-Go.
- Registrar hallazgos y backlog.
- Acordar siguientes pasos (siempre en entorno controlado).

## 14) Limpieza posterior

- Cerrar sesiones activas.
- Revocar credenciales temporales de demo.
- Eliminar archivos temporales/exportes de prueba.
- Verificar flags sensibles en `false`.
- Confirmar que no se versionaron secretos ni datos reales.
