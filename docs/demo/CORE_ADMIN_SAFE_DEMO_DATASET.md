# CORE Admin — Safe Demo Dataset (mínimo, práctico y no productivo)

Documento operativo para preparar un dataset de demo/QA **ficticio**, seguro y reutilizable en `Ecosistema-core-admin`, alineado con checklist y runbook de demo privada controlada.

> Alcance: documentación de operación demo (no seed ejecutable, no esquema, no migraciones).

## 1) Principios obligatorios

- Este dataset es **solo demo/QA local o VM interna controlada**.
- Todo dato debe ser sintético/ficticio: tenant, personas, correos, teléfonos, campañas, archivos y métricas.
- Correos de demo siempre con dominio `example.test`.
- IDs/códigos de demo con prefijos `DEMO-` (y `CMP-DEMO-` para campañas).
- No versionar contraseñas, hashes reales, tokens, API keys, secretos ni dumps reales.
- Mantener flags críticas en `false` para evitar envíos reales, integraciones remotas o ejecuciones externas.
- No usar ni exponer datos reales de clientes, proveedores o personal interno.

## 2) Perfil de entorno para demo segura (guardrails)

Mantener configuración de demo con defaults seguros:

- SMTP/envío real desactivado.
- AWS/S3/Drive remoto desactivado.
- Proveedor IA externo desactivado.
- Workflow con ejecución real desactivada.
- Exportes de reportes solo dry-run y sin PII.

Referencia de flags y matriz de seguridad:

- `docs/demo/CORE_ADMIN_PRIVATE_DEMO_CHECKLIST.md`
- `docs/demo/CORE_ADMIN_PRIVATE_DEMO_RUNBOOK.md`
- `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md`
- `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`
- `.env.example`
- `.env.vm.example`

## 3) Dataset ficticio sugerido por módulo (no ejecutable)

## A) Tenant demo

Usar un tenant principal ficticio:

- `tenant_code`: `DEMO-TNT-001`
- `tenant_name`: `Esforzados Demo Controlado`
- `slug`: `esforzados-demo-controlado`
- `timezone`: `America/Mexico_City`
- `locale`: `es_MX`
- `status`: `active`

Notas:

- No reutilizar nombres comerciales reales ni alias de clientes.
- Mantener nomenclatura simple para trazabilidad en demo.

## B) Usuarios demo

Definir tres usuarios demo mínimos:

1. **Owner demo**
   - `display_name`: `Alicia Demo Owner`
   - `email`: `alicia.owner@example.test`
2. **Operador demo**
   - `display_name`: `Bruno Demo Operador`
   - `email`: `bruno.operador@example.test`
3. **Auditor demo**
   - `display_name`: `Carla Demo Auditor`
   - `email`: `carla.auditor@example.test`

Regla obligatoria:

- Las contraseñas **no se versionan** en markdown, seeds, scripts, capturas ni `.env.example`.
- Si se regeneran para sesión de demo, se crean localmente y se invalidan al cierre.

## C) Roles demo

Roles sugeridos y límites:

- `demo_super_admin`
  - Uso: owner demo durante recorrido técnico.
  - Límite: no habilitar integraciones externas ni bypass de guardrails.
- `demo_operator`
  - Uso: operación interna en vistas permitidas de demo.
  - Límite: evitar acciones productivas externas y escritura sensible.
- `demo_auditor_readonly`
  - Uso: revisión de dashboard/auditoría/reportes en lectura.
  - Límite: sin edición, sin exportes sensibles, sin acciones activas.

## D) CRM / Leads

Cargar entre 3 y 5 leads ficticios. Ejemplo práctico:

- `LEAD-DEMO-0001` — `Elena Prospecto` — `elena.prospecto@example.test` — `+52-555-010-0001` — `new`
- `LEAD-DEMO-0002` — `Mario Interesado` — `mario.interesado@example.test` — `+52-555-010-0002` — `contacted`
- `LEAD-DEMO-0003` — `Nora Seguimiento` — `nora.seguimiento@example.test` — `+52-555-010-0003` — `qualified`
- `LEAD-DEMO-0004` — `Pablo Demo` — `pablo.demo@example.test` — `+52-555-010-0004` — `lost` (opcional)

Notas de privacidad:

- Teléfonos claramente ficticios (no números reales de clientes).
- Notas de lead sin PII real ni referencias internas privadas.

## E) Campaigns

Registrar 2 campañas demo:

- `CMP-DEMO-001` — `Campaña Demo Primavera` — estado `draft`
- `CMP-DEMO-002` — `Campaña Demo Reactivación` — estado `scheduled`

Métricas sintéticas sugeridas (solo visualización):

- `sent_count`: 0
- `open_rate`: 37.5
- `click_rate`: 12.0
- `conversion_rate`: 4.2

## F) Landing

Ejemplo de landing ficticia:

- `slug`: `demo-landing-core-admin`
- `title`: `Landing Demo Core Admin`
- `cta_primary`: `Solicitar diagnóstico demo`
- `cta_secondary`: `Ver flujo controlado`
- `body_copy`: texto genérico de demostración técnica sin datos reales.

Aclaración:

- No habilitar formularios con datos reales durante demo.
- Si se muestra submit, mantener en dry-run/controlled según flags.

## G) Browser Analytics

Datos únicamente sintéticos para render de paneles:

- `sessions`: 120
- `pageviews`: 340
- `events`: 58
- `top_pages`: `/dashboard`, `/crm/leads`, `/reports`

Reglas:

- No recolectar IP real completa.
- No recolectar user-agent completo real.
- Uso estrictamente visual (read-only/dry-run) para demo.

## H) Reports

Reportes ficticios sugeridos:

- `RPT-DEMO-001` — `Funnel Demo Q2`
- `RPT-DEMO-002` — `Estado Campañas Demo`

KPIs sintéticos:

- leads nuevos: `35`
- leads calificados: `12`
- campañas activas (demo): `2`
- tasa conversión demo: `4.2%`

Reglas:

- Exportaciones solo dry-run.
- No incluir PII en salidas.

## I) Workflow

Simulación de reglas/ejecuciones:

- `WF-DEMO-001` — `Lead nuevo -> tarea interna` (dry-run)
- `WF-DEMO-002` — `Lead calificado -> notificación interna` (dry-run)

Reglas:

- Sin workers reales.
- Sin acciones externas (email/webhook) activas.
- Mostrar solo resultado simulado/controlado.

## J) Cloud / Drive

Estructura ficticia local (metadatos demo):

- Carpeta `DEMO-DRIVE-ROOT`
  - `demo-brief-core-admin.pdf`
  - `demo-activos-campania.zip`

Reglas:

- Sin S3 real.
- Sin URLs firmadas reales.
- Sin buckets productivos.

## K) Mail / Notifications

Elementos demo sugeridos:

- `MAIL-TPL-DEMO-001` — `Plantilla bienvenida demo`
- `MAIL-TPL-DEMO-002` — `Plantilla seguimiento demo`
- Cola visual en estado simulado/read-only.

Reglas:

- Sin envío SMTP real.
- Sin destinatarios reales.

## L) AI / VitaOS

Ejemplos de simulación:

- Prompt demo: `"Genera resumen ficticio del lead LEAD-DEMO-0002"`
- Resultado demo: texto sintético de ejemplo, sin datos reales.
- Propuesta demo: `AIPROP-DEMO-001` en estado `dry-run`.

Reglas:

- Provider externo apagado.
- Escritura de propuestas solo dry-run/controlled.

## 4) Dataset mínimo para primera demo (10–15 min)

Conjunto mínimo recomendado:

- 1 tenant demo.
- 3 usuarios demo.
- 3 roles demo.
- 3 leads ficticios.
- 2 campañas ficticias.
- 1 landing ficticia.
- 1 reporte ficticio.
- 1 flujo de workflow en dry-run.
- 2 archivos demo ficticios en cloud/drive local.

## 5) Checklist de sanitización del dataset

Antes de presentar, validar:

- [ ] No hay correos reales.
- [ ] No hay teléfonos reales.
- [ ] No hay nombres de clientes reales.
- [ ] No hay rutas internas privadas expuestas.
- [ ] No hay tokens.
- [ ] No hay credenciales.
- [ ] No hay dumps reales.
- [ ] No hay exports con PII.
- [ ] No hay capturas mostrando `.env`.

## 6) Criterio de aceptación del dataset

El dataset se considera aceptable **solo si**:

- todos los datos son ficticios;
- se usa `example.test` en correos de demo;
- flags críticas permanecen apagadas (`false`);
- no requiere servicios externos reales;
- no requiere cambios de esquema;
- no requiere migraciones;
- no expone secretos ni credenciales.

## 7) Limpieza post-demo

Al cerrar sesión de demo:

1. Desactivar o remover usuarios demo temporales según política del entorno.
2. Rotar/inutilizar credenciales temporales usadas en la sesión.
3. Limpiar archivos de export demo y adjuntos temporales.
4. Confirmar flags críticas en `false`.
5. Registrar hallazgos y pendientes de hardening para backlog.

## 8) Validación recomendada para PRs documentales demo

```bash
composer dump-autoload
php -l routes/web.php
php -l scripts/smoke-check.php
php -l scripts/schema-compatibility-check.php
php -l scripts/schema-usage-check.php
composer smoke
composer schema:usage
```

Si `composer schema:usage` retorna warning controlado por DB no disponible, registrar el resultado como **Go con advertencias**.

## Referencias internas

- `docs/demo/CORE_ADMIN_PRIVATE_DEMO_CHECKLIST.md`
- `docs/demo/CORE_ADMIN_PRIVATE_DEMO_RUNBOOK.md`
- `docs/schema-usage/checklists/PR_241_completar_checklist_demo_privada_controlada_core_admin.md`
- `docs/project/CORE_ADMIN_DEMO_GO_NO_GO.md`
- `docs/project/CORE_ADMIN_SCHEMA_AUDIT_PHASE_CLOSURE_PR225_PR238.md`
- `README.md`
- `.env.example`
- `.env.vm.example`
