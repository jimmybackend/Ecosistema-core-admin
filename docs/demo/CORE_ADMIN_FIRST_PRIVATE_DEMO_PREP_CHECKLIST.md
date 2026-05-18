# Core Admin — Checklist de preparación de entorno y datos para primera demo privada

> **Advertencia de alcance:** este documento aplica a una **demo privada controlada** de Core Admin. **No** habilita producción SaaS pública ni operaciones con servicios reales.

## 0) Objetivo operativo

Usar esta checklist para dejar el entorno de demo en estado **Go/No-Go trazable**, minimizando riesgos de exposición, escritura no controlada o dependencia de integraciones reales.

## 1) Preparación de entorno (VM/EC2 controlada o local)

- [ ] Confirmar que el entorno elegido es **controlado** (VM/EC2 aislada o local), sin tráfico público abierto.
- [ ] Verificar acceso administrativo al host y al proyecto `Ecosistema-core-admin`.
- [ ] Confirmar instalación base y arranque según runbook de VM/EC2 para demo privada.
- [ ] Ejecutar dependencias y autoload del proyecto:
  - [ ] `composer install`
  - [ ] `composer dump-autoload`
- [ ] Confirmar que `.env` operativo existe localmente en la máquina de demo y **no** está versionado.

## 2) Verificación de `.env` seguro (sin commitear)

- [ ] Crear/actualizar `.env` desde `.env.vm.example` o `.env.example` según entorno.
- [ ] Confirmar que `.env` contiene sólo valores ficticios o internos de demo controlada.
- [ ] Confirmar que **no** hay secretos reales de producción en variables de correo, nube, IA o billing.
- [ ] Confirmar que no se sube `.env` a git (`git status` limpio de secretos).

## 3) Confirmación de flags críticas apagadas

Mantener explícitamente en `false` para la demo privada:

- [ ] `MAIL_SEND_ENABLED`
- [ ] `MAIL_ALLOW_TEST_SEND`
- [ ] `CLOUD_S3_ENABLED`
- [ ] `CLOUD_ALLOW_UPLOADS`
- [ ] `CLOUD_ALLOW_DOWNLOADS`
- [ ] `ECOSISTEMA_DRIVE_AWS_ENABLED`
- [ ] `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS`
- [ ] `ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS`
- [ ] `ECOSISTEMA_URL_LOCATOR_PUBLIC_REDIRECTS`
- [ ] `ECOSISTEMA_URL_LOCATOR_TRACKING_ENABLED`
- [ ] `ECOSISTEMA_LANDING_PUBLIC_RENDER_ENABLED`
- [ ] `ECOSISTEMA_LANDING_FORM_SUBMIT_ENABLED`
- [ ] `ECOSISTEMA_BROWSER_ANALYTICS_COLLECTOR_WRITE`
- [ ] `ECOSISTEMA_AI_PROVIDER_ENABLED`
- [ ] `ECOSISTEMA_AI_WRITE_PROPOSALS`
- [ ] `ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED`
- [ ] `ECOSISTEMA_REPORT_EXPORT_WRITE`

## 4) Preparación de tenant demo ficticio

- [ ] Crear o validar tenant **ficticio** exclusivo para demo privada.
- [ ] Confirmar naming explícito de demo (ej. `tenant-demo-privado`) para evitar confusión con tenants reales.
- [ ] Confirmar aislamiento del tenant demo frente a datos productivos/reales.
- [ ] Confirmar que cualquier escritura permitida quede acotada a contexto controlado/dry-run.

## 5) Preparación de usuarios demo ficticios

- [ ] Crear/validar usuario administrador demo ficticio.
- [ ] Crear/validar usuario operador demo ficticio (si aplica al guion).
- [ ] Usar correos no reales (dominio `example.test` o equivalente ficticio).
- [ ] Confirmar roles/permisos mínimos necesarios para recorrer el guion.
- [ ] Confirmar que no se reutilizan cuentas personales o cuentas de cliente.

## 6) Preparación de dataset mínimo ficticio

- [ ] Cargar sólo dataset mínimo necesario para narrar el flujo de demo.
- [ ] Confirmar ausencia total de PII real y de registros de clientes reales.
- [ ] Verificar consistencia básica para pantallas clave (dashboard, módulos principales, reportes read-only).
- [ ] Etiquetar dataset como “demo-controlado” para facilitar limpieza posterior.

## 7) Validación funcional previa (login, dashboard y rutas)

- [ ] Probar login con usuario demo principal.
- [ ] Confirmar carga del dashboard sin errores bloqueantes.
- [ ] Validar rutas principales previstas en guion (sin navegación improvisada).
- [ ] Verificar mensajes de error controlados en módulos no habilitados.

## 8) Validación de módulos por modo operativo

- [ ] **Read-only:** confirmar que consultas listan datos ficticios esperados.
- [ ] **Dry-run:** confirmar que simula ejecución sin efectos persistentes críticos.
- [ ] **Controlled:** confirmar que cualquier operación con efecto está protegida por flags/permisos y contexto.
- [ ] Registrar cualquier desviación como riesgo para decisión Go/No-Go.

## 9) Revisión de capturas/evidencia segura

- [ ] Verificar que capturas no exponen secretos (`.env`, tokens, keys, credenciales).
- [ ] Verificar que capturas no exponen datos personales reales.
- [ ] Verificar que nombres visibles sean de tenant/usuarios ficticios.
- [ ] Guardar evidencia con nomenclatura de demo privada y fecha.

## 10) Checklist técnico final Go/No-Go

- [ ] `composer dump-autoload`
- [ ] `php -l routes/web.php`
- [ ] `php -l scripts/smoke-check.php`
- [ ] `php -l scripts/schema-compatibility-check.php`
- [ ] `php -l scripts/schema-usage-check.php`
- [ ] `composer smoke`
- [ ] `composer schema:usage`
- [ ] Aceptar **warning controlado** en `schema:usage` cuando no haya DB disponible en entorno de demo.

## 11) Criterio de decisión

### Go

- [ ] Entorno controlado validado.
- [ ] Flags críticas apagadas.
- [ ] Tenant/usuarios/dataset 100% ficticios.
- [ ] Flujo login + dashboard + rutas principales estable.
- [ ] Sin bloqueantes de seguridad/privacidad.

### No-Go

- [ ] Se detectan datos reales, secretos o integraciones reales activas.
- [ ] Existen fallas bloqueantes en login, dashboard o rutas críticas de demo.
- [ ] No hay trazabilidad suficiente de estado técnico para presentación.

---

**Recordatorio:** esta preparación es para **primera demo privada controlada** de Core Admin. Cualquier paso hacia producción SaaS pública requiere hardening, gobierno de datos, seguridad, operación y aprobación formal fuera de esta checklist.
