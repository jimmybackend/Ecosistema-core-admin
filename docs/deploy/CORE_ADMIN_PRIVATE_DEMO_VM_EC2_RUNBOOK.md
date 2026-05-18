# Core Admin — Runbook de ejecución en VM interna / EC2 controlada para demo privada (PR #247)

- **Fecha base (UTC):** 2026-05-18
- **Repositorio:** `jimmybackend/Ecosistema-core-admin`
- **Estado esperado:** **Go con advertencias** (warning controlado permitido para `schema:usage` sin DB)
- **Declaración de alcance:** **Este runbook NO habilita producción SaaS pública**.

## 1) Objetivo y alcance

Definir una guía operativa, segura y trazable para levantar y ejecutar **demo privada controlada** de Core Admin en:

- entorno local;
- VM interna;
- instancia EC2 controlada.

Este runbook aplica solo a ejecución de demo en entorno aislado, con dataset ficticio y guardrails activos.

Fuera de alcance:

- salida a producción SaaS pública;
- activación de integraciones reales;
- exposición de datos reales, secretos o infraestructura sensible.

## 2) Entorno permitido

Se permite únicamente:

1. **Local**: estación de operador autorizada, sin compartir secretos.
2. **VM interna**: red interna/corporativa, acceso restringido a equipo demo.
3. **EC2 controlada**: instancia temporal, acceso por lista blanca o túnel privado.

No permitido:

- entornos públicos abiertos sin control de acceso;
- reutilizar entornos productivos;
- mezclar datos reales con dataset de demo.

## 3) Requisitos mínimos de servidor

Perfil mínimo recomendado para VM/EC2 de demo:

- CPU: 2 vCPU
- RAM: 4 GB (recomendado 8 GB para holgura de demo)
- Disco: 20 GB SSD
- SO: Linux 64-bit actualizado (LTS)
- PHP/Composer disponibles según proyecto
- Conectividad saliente **no obligatoria** para demo (preferible restringida)

Controles mínimos de seguridad de host:

- usuario no root para operación;
- SSH con llave y acceso restringido;
- firewall activo (solo puertos necesarios);
- logs del sistema sin exposición pública.

## 4) Preparación segura de `.env` (sin commitear secretos)

1. Basar archivo en `.env.example` o `.env.vm.example`.
2. Crear `.env` local en la VM/EC2 (nunca versionarlo).
3. Usar valores ficticios o placeholders para credenciales.
4. Verificar que no existan secretos reales en historial de shell, capturas o notas.

Comandos sugeridos:

```bash
cp .env.vm.example .env
# editar .env de forma local/segura, sin commit
```

Reglas obligatorias:

- no subir `.env` al repositorio;
- no copiar llaves reales de SMTP/AWS/IA;
- no usar endpoints productivos.

## 5) Flags que deben permanecer apagadas

Para demo privada controlada, mantener en `false` (mínimo):

- `MAIL_SEND_ENABLED`
- `MAIL_ALLOW_TEST_SEND`
- `CLOUD_S3_ENABLED`
- `CLOUD_ALLOW_UPLOADS`
- `CLOUD_ALLOW_DOWNLOADS`
- `S3_DRIVE_ALLOW_REMOTE_CALLS`
- `S3_DRIVE_ALLOW_REMOTE_UPLOADS`
- `S3_DRIVE_ALLOW_REMOTE_DOWNLOADS`
- `ECOSISTEMA_DRIVE_AWS_ENABLED`
- `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS`
- `ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS`
- `ECOSISTEMA_AI_PROVIDER_ENABLED`
- `ECOSISTEMA_AI_WRITE_PROPOSALS`
- `ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED`
- `ECOSISTEMA_WORKFLOW_ACTION_SEND_EMAIL`
- `ECOSISTEMA_WORKFLOW_ACTION_WEBHOOK`
- `ECOSISTEMA_REPORT_EXPORT_WRITE`
- `ECOSISTEMA_REPORT_EXPORT_INCLUDE_PII`
- `ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECTS`
- `ECOSISTEMA_URL_LOCATOR_TRACKING_ENABLED`

Regla de oro: si una flag puede disparar side effects externos o PII, queda apagada.

## 6) Validación previa a abrir la demo

Ejecutar en este orden:

```bash
composer dump-autoload
php -l routes/web.php
php -l scripts/smoke-check.php
php -l scripts/schema-compatibility-check.php
php -l scripts/schema-usage-check.php
composer smoke
composer schema:usage
```

Criterio:

- errores de lint/smoke: **No-Go**;
- `schema:usage` con warning por DB no disponible en entorno aislado: **Go con advertencias** (documentado).

## 7) Manejo del warning `schema:usage` sin DB

Si no hay DB de verificación disponible:

1. Confirmar que el warning es por conectividad/ausencia de DB, no por regresión de código.
2. Registrar evidencia del warning en bitácora de demo.
3. Clasificar corrida como **Go con advertencias**.
4. Continuar solo con recorrido de UI controlado, read-only/dry-run.
5. Programar re-ejecución de `schema:usage` en entorno con DB controlada.

## 8) Reglas de acceso y exposición pública

Para VM/EC2 de demo:

- acceso solo a operadores autorizados;
- endpoint protegido (VPN, túnel o allowlist IP);
- no indexación pública;
- no credenciales compartidas por canales inseguros;
- sesión de demo con usuario ficticio de alcance limitado;
- prohibido exponer `.env`, logs crudos o pantallas con secretos.

## 9) Ejecución y Go/No-Go de entorno

Checklist rápida antes de compartir pantalla:

- [ ] Entorno es local/VM interna/EC2 controlada (no productivo).
- [ ] Dataset ficticio cargado (`example.test`, prefijos `DEMO-`, `CMP-DEMO-`, `LEAD-DEMO-`).
- [ ] Flags sensibles en `false`.
- [ ] Comandos de validación ejecutados.
- [ ] `composer smoke` sin fallos críticos.
- [ ] `composer schema:usage` en OK o warning controlado.
- [ ] Sin secretos visibles en terminal/navegador.

Decisión:

- **Go:** todo en verde.
- **Go con advertencias:** warning controlado documentado (ej. `schema:usage` sin DB).
- **No-Go:** fallos críticos, datos reales o integraciones reales activas.

## 10) Limpieza post-demo

Al cerrar la sesión:

1. Cerrar sesión de usuario demo.
2. Detener compartición de pantalla.
3. Eliminar archivos temporales y exportes de prueba.
4. Apagar VM/instancia temporal si aplica.
5. Confirmar que no quedaron servicios externos activados.
6. Rotar/invalidar credenciales temporales de demo.
7. Registrar resultado y pendientes (Go / Go con advertencias / No-Go).

## 11) Advertencia final obligatoria

> Este entorno y este runbook están diseñados exclusivamente para **demo privada controlada**.  
> **No** constituyen habilitación de **producción SaaS pública** ni autorización para activar integraciones reales.
