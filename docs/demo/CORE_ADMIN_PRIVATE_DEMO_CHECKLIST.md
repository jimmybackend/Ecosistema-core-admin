# Core Admin — Checklist de demo privada controlada (PR #241)

- **Fecha base:** 2026-05-18 (UTC)
- **Repositorio:** `jimmybackend/Ecosistema-core-admin`
- **Objetivo:** ejecutar una demo privada controlada, con datos ficticios y guardrails activos, sin activar servicios externos reales.
- **Alcance:** preparación operativa y validación previa (no certifica producción SaaS).

## 1) Precondiciones

- [ ] Demo limitada a entorno privado controlado (local/VM interna).
- [ ] No se usarán datos reales de clientes, proveedores ni personal interno.
- [ ] Se usará tenant demo y usuarios ficticios (`example.test`).
- [ ] Se confirma narrativa técnica: estados **operativo**, **read-only**, **dry-run**, **controlled**.
- [ ] Se confirma explícitamente que esta demo **NO** es salida a producción SaaS.
- [ ] Se confirma que el alcance de la sesión es técnico-operativo y de validación previa.
- [ ] Se confirma que no se habilitarán integraciones externas reales durante la sesión.

## 2) Entorno permitido

- [ ] Entorno local permitido (equipo de desarrollo bajo control interno).
- [ ] VM interna permitida (acceso restringido por red interna/VPN).
- [ ] EC2 permitido **solo** si está aislado, controlado y sin exposición pública innecesaria.
- [ ] No se expone la demo a Internet abierta salvo necesidad explícita y mitigada.
- [ ] HTTPS recomendado cuando la demo se comparte por red (VM/EC2 controlada).
- [ ] Acceso limitado a personas autorizadas (equipo técnico/operativo definido).

## 3) Datos permitidos

- [ ] Tenant demo ficticio (sin datos de cliente real).
- [ ] Usuarios demo ficticios (owner, operador, auditor).
- [ ] Correos únicamente con dominio `example.test`.
- [ ] Nombres/alias estrictamente ficticios.
- [ ] Campañas, leads y reportes de demo con prefijos `DEMO-` / `CMP-DEMO-`.
- [ ] Archivos de demo sin información sensible ni metadatos privados.

## 4) Datos prohibidos

- [ ] Clientes reales (nombres comerciales/razones sociales reales).
- [ ] Correos reales de personas u organizaciones reales.
- [ ] Teléfonos reales.
- [ ] Contraseñas reales o reutilizadas de entornos reales.
- [ ] Tokens, API keys, JWTs o secretos reales.
- [ ] Dumps reales de base de datos o exportes productivos.
- [ ] Claves AWS reales.
- [ ] Credenciales SMTP reales.
- [ ] Información personal/financiera real (PII/finanzas).

## 5) Flags y servicios externos (deben permanecer `false`)

Verificar en `.env.example`, `.env.vm.example` y `.env` efectivo de demo:

- [ ] `MAIL_SEND_ENABLED=false`
- [ ] `MAIL_ALLOW_TEST_SEND=false`
- [ ] `CLOUD_S3_ENABLED=false`
- [ ] `CLOUD_ALLOW_UPLOADS=false`
- [ ] `CLOUD_ALLOW_DOWNLOADS=false`
- [ ] `ECOSISTEMA_DRIVE_AWS_ENABLED=false`
- [ ] `ECOSISTEMA_DRIVE_ALLOW_REMOTE_CALLS=false`
- [ ] `ECOSISTEMA_DRIVE_ALLOW_SIGNED_URLS=false`
- [ ] `ECOSISTEMA_DRIVE_ALLOW_REMOTE_UPLOADS=false`
- [ ] `ECOSISTEMA_DRIVE_ALLOW_REMOTE_DOWNLOADS=false`
- [ ] `ECOSISTEMA_AI_PROVIDER_ENABLED=false`
- [ ] `ECOSISTEMA_AI_WRITE_PROPOSALS=false`
- [ ] `ECOSISTEMA_WORKFLOW_EXECUTION_ENABLED=false`
- [ ] `ECOSISTEMA_REPORT_EXPORT_WRITE=false`
- [ ] `ECOSISTEMA_REPORT_EXPORT_INCLUDE_PII=false`
- [ ] `CORE_REGISTRATION_ENABLED=false`

## 6) Validaciones previas obligatorias

- [ ] `composer dump-autoload` ejecutado (autoload actualizado).
- [ ] `php -l routes/web.php` sin errores de sintaxis.
- [ ] `php -l scripts/smoke-check.php` sin errores de sintaxis.
- [ ] `php -l scripts/schema-compatibility-check.php` sin errores de sintaxis.
- [ ] `php -l scripts/schema-usage-check.php` sin errores de sintaxis.
- [ ] `composer smoke` sin fallos críticos nuevos.
- [ ] `composer schema:usage` ejecutado en OK o con advertencia controlada (por ejemplo, DB no disponible en entorno de demo).
- [ ] Revisión manual de `.env.example` y `.env.vm.example` completada.
- [ ] Confirmación de que no hay secretos versionados en el repositorio.

## 7) Módulos que se pueden mostrar (por estado)

### Operativo

- [ ] Login + Dashboard.
- [ ] Administración interna de usuarios / roles / permisos.
- [ ] Auditoría y system health/logs como capacidades internas.

### Read-only

- [ ] CRM en consulta (sin promesa de operación productiva externa).
- [ ] Campaigns en visualización y revisión de estado.
- [ ] Browser analytics en consulta.
- [ ] Landing en consulta administrativa.
- [ ] Reports de lectura sin export sensible.

### Dry-run

- [ ] Workflow en simulación sin ejecución real externa.
- [ ] Flujos de report export en simulación cuando aplique.
- [ ] AI/VitaOS en simulación (sin proveedor externo activo).

### Controlled

- [ ] Cloud/Drive con guardrails y bloqueo de remotos reales.
- [ ] Mail notifications con envíos reales desactivados.
- [ ] Onboarding/acciones sensibles solo bajo control explícito y sin impacto productivo.

> No prometer producción SaaS ni comportamiento productivo externo durante la demo.

## 8) Módulos/acciones que NO deben ejecutarse como reales

- [ ] Envío real de correo (SMTP).
- [ ] Subida real a S3.
- [ ] Descarga real desde S3.
- [ ] Llamadas IA externas.
- [ ] Workers/ejecuciones reales de producción.
- [ ] Billing real.
- [ ] Report exports con PII real.
- [ ] Registros públicos abiertos sin control.
- [ ] Formularios públicos con datos reales.

## 9) Guion breve de demo (recorrido recomendado)

- [ ] Abrir contexto: demo privada controlada, no producción.
- [ ] Login con usuario ficticio demo.
- [ ] Recorrido por dashboard.
- [ ] Recorrido por usuarios/roles/permisos.
- [ ] Recorrido por auditoría/logs.
- [ ] Mostrar un módulo en estado read-only.
- [ ] Mostrar un flujo en estado dry-run.
- [ ] Cerrar con límites técnicos y siguientes pasos.

## 10) Capturas permitidas y prohibidas

### Permitidas

- [ ] Pantallas con datos ficticios.
- [ ] Estados generales de módulos.
- [ ] Checklists y evidencia de validaciones.
- [ ] Reportes sin PII.

### Prohibidas

- [ ] `.env` completo o fragmentos sensibles.
- [ ] Tokens/keys/secretos.
- [ ] IPs completas y datos de infraestructura no anonimizados.
- [ ] User-agent completo.
- [ ] JSON crudo sensible.
- [ ] Rutas internas privadas no destinadas a difusión.
- [ ] Stack traces con contexto sensible.
- [ ] Credenciales de cualquier tipo.

## 11) Criterio Go / No-Go antes de demo

### Go

- [ ] No hay fallos críticos en validaciones base.
- [ ] Dataset ficticio confirmado.
- [ ] Flags seguras en estado correcto.
- [ ] Servicios externos reales apagados.
- [ ] Narrativa de límites clara y repetible por el presentador.

### No-Go

- [ ] Se detectan datos reales.
- [ ] Se detectan secretos/credenciales expuestas.
- [ ] Hay servicios externos activos sin control.
- [ ] `composer smoke` falla críticamente.
- [ ] Hay schema mismatch crítico no mitigado.
- [ ] No se puede explicar claramente qué es demo y qué no es producción.

## 12) Checklist post-demo

- [ ] Cerrar sesión de usuarios demo.
- [ ] Remover/desactivar usuarios temporales si aplica.
- [ ] Borrar datos demo temporales si aplica.
- [ ] Apagar VM/instancia temporal si aplica.
- [ ] Revisar logs post-sesión.
- [ ] Confirmar que no se compartieron capturas sensibles.
- [ ] Registrar hallazgos, riesgos y feedback.

## 13) Resultado final

- **Estado:** [ ] Pendiente  [ ] Go  [ ] Go con advertencias  [ ] No-Go
- **Responsable:**
- **Fecha:**
- **Observaciones:**
