# Ecosistema Core Admin

Aplicación administrativa interna del ecosistema. Este README prioriza el **estado operativo real** y separa con claridad lo estable de lo que está en read-only, dry-run, controlled por flags o solo documental.

## Resumen operativo real
- Núcleo administrativo funcional para autenticación/sesión, dashboard, tenants, users, roles, permissions, modules y superficies de salud/auditoría.
- Existen módulos adicionales visibles por rutas y documentación (Mail, Cloud/Drive, URL Locator, Browser Analytics, Landing, CRM, Workflow, Reports, Campaigns, AI), pero su operación real depende de modo read-only, dry-run o flags desactivados por defecto.
- Estado canónico por módulo: `docs/project/CORE_ADMIN_MODULE_STATUS.md`.
- Matriz técnica extendida: `docs/project/CORE_ADMIN_MODULE_STATUS_MATRIX.md`.

Documentación complementaria:
- Matriz comercial pública de estado: `docs/estado_modulos.md`.
- Guía de demo/showcase honesta: `docs/project/CORE_ADMIN_SHOWCASE_DEMO_GUIDE.md`.
- Checklist de preparación para demo controlada: `docs/project/CORE_ADMIN_DEMO_READINESS_CHECKLIST.md`.
- Notas de release para showcase controlado: `docs/project/CORE_ADMIN_SHOWCASE_RELEASE_NOTES.md`.
- Checklist de cierre de riesgos técnicos H: `docs/project/ECOSISTEMA_RISK_H_CLOSURE.md`.
- Contrato de descarga controlada de Drive: `docs/project/ECOSISTEMA_DRIVE_DOWNLOAD_CONTRACT.md`.
- Mapa técnico ruta-service-tabla: `docs/project/CORE_ADMIN_ROUTE_SERVICE_TABLE_MAP.md`.

## Material visual
- Diagramas base para presentación (sin datos reales): `docs/diagramas.md`.
- Reglas de assets y privacidad para material visual: `assets/README.md`.

## Instalación local
```bash
composer install
cp .env.example .env
# Configura DB_* según tu entorno
php -S 127.0.0.1:8000 -t public
```

Nota de base de datos (canónica para Core Admin): `docs/project/CORE_ADMIN_DATABASE_CANONICAL_NAME.md`.

Comandos útiles:
```bash
composer dump-autoload
composer smoke
```

## Regla única de base de datos runtime
- En Core Admin, el nombre de base de datos **efectivo en runtime** se toma únicamente de `DB_DATABASE` en `.env` (`config/database.php`).
- Para la referencia operativa actual de VM/producción de este repositorio, el valor canónico documentado es `adbbmis1_eco`.
- `ecosistema` puede aparecer como denominación histórica/conceptual del sistema, pero **no** debe usarse como instrucción operativa de conexión.
- Si un entorno usa otro nombre físico de base, debe ajustarse solo en su `.env` local/deploy, nunca hardcodeado en código o docs operativas contradictorias.

## Estado del proyecto por tipo de operación

### Operativo base
- Core/Auth: login, logout, sesión, dashboard y health DB.
- Tenants/Users/Roles/Permissions: CRUD administrativo del núcleo.
- Security/Audit: superficies de health, logs y auditoría de eventos.
- Base Modules: catálogo/base de módulos administrativos.

### Read-only
- Workflow: runs y vistas operativas principalmente de consulta.
- Reports: foco actual en lectura/diagnóstico administrativo.
- Cloud/Drive: parte de la navegación/summary/contratos opera como consulta controlada.
- URL Locator, Landing Pages, Browser Analytics, CRM/Campaigns: varias vistas y listados se usan en modo lectura según flags y matriz.

### Dry-run
- Workflow: simulaciones en `/workflow/dry-run` y dry-run por regla.
- URL Locator: redirect dry-run.
- Landing Pages: rutas `*dry-run*` para render/submit controlado.
- Reports y Campaigns: exportación/creación dry-run.
- AI: propuestas/insights de prueba sin operación autónoma productiva.

### Controlled por flags
- Mail/Notifications: SMTP real y envíos efectivos sujetos a flags.
- Cloud/Drive: llamadas remotas y AWS/S3 reales sujetos a flags.
- AI: proveedor externo y escritura de propuestas sujetos a flags.
- Workflow: ejecución real sujeta a flag de ejecución.
- CRM/Campaigns, URL Locator, Landing Pages, Browser Analytics, Reports: activación y escritura sujetas a flags `*_ENABLED`, `*_DRY_RUN`, `*_WRITE`.

### Documental / roadmap
- Billing: documentado, no declarado como completo/productivo en este repositorio.
- Integrations: superficies parciales/documentales; no asumir operación productiva end-to-end.
- Support: estado documental/parcial, sin promesa de completitud operativa.
- Privacy/Compliance: políticas y lineamientos documentados; implementación operativa depende de hardening/configuración por entorno.
- Jobs/Workers productivos completos: objetivo de roadmap, no estado actual.

### Limitaciones vigentes
- No SMTP real por defecto.
- No AWS/S3 real por defecto.
- No workers productivos completos activos.
- Flags avanzados en `false` por defecto (`.env.example`).
- No usar datos reales ni secretos en repositorio/entornos de demo.
- Referencia de contacto público aprobado: `contacto.md` y `docs/politica_contacto_publico.md`.

## Estado real de cron/workers (sin ambigüedad)
- Estado actual documentado: `docs/ops/WORKERS_CRON_CURRENT_STATE.md`.
- `cron-runner` solo tiene dos jobs controlados (health checks y limpieza de sesiones).
- **No hay workers productivos activos todavía**.
- **No ejecuta AWS/S3 real**.
- **No envía correos masivos**.
- No hay colas productivas activas para IA/webhooks/procesamiento real de archivos.

## Rutas principales reales (referencia corta)
- Auth y base: `/login`, `POST /login`, `POST /logout`, `/dashboard`, `/health/db`.
- Core admin: `/tenants`, `/users`, `/roles`, `/permissions`, `/modules`.
- System: `/system/health`, `/system/logs`, `/system/audit`, `/audit/events`.
- Cloud/Drive: `/cloud`, `/cloud/drive`, `/cloud/drive/files`, `/cloud/drive/folders`, `/cloud/drive/summary`.
- Mail: `/mail`, `/mail/compose`, `/mail-notifications`.
- Workflow: `/workflow`, `/workflow/runs`, `/workflow/dry-run`.
- Reports/Campaigns: `/reports/marketing-funnel`, `/reports/lead-performance`, `/campaigns`.
- Landing/URL/Analytics/CRM/AI (operación condicionada): `/landing`, `/url/locator`, `/browser/analytics`, `/crm`, `POST /ai/assist`.

Referencia completa de rutas: `routes/web.php`.

## Seguridad y flags
- Matriz de seguridad operativa (flags/permisos/CSRF/tenant/PII): `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`.
- Matriz de defaults seguros de flags controlled: `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md`.
- Reglas de consentimiento/privacidad para analytics/tracking/IP/geo: `docs/project/ECOSISTEMA_ANALYTICS_PRIVACY_CONSENT.md`.
- No subir secretos ni commitear `.env`; usar `.env.example` como plantilla.
- Configuración por defecto segura: sin SMTP real, sin AWS/S3 real, sin proveedor IA externo.
- Registro inicial controlado por flag: `CORE_REGISTRATION_ENABLED=false` por defecto.
- Asignación de permisos de rol alineada al esquema canónico: `core_role_permissions` se reemplaza usando `tenant_id` resuelto del rol (sin `tenant_id` libre desde request).

Referencia: `.env.example`.

## Pruebas / smoke
Ejecuta:
```bash
composer smoke
# o directamente
php scripts/smoke-check.php
```

El smoke valida estructura/carga/sintaxis y controles básicos; no reemplaza pruebas funcionales completas ni activa integraciones externas.

## Verificación opcional de compatibilidad de esquema DB
`composer smoke` ejecuta además un chequeo opcional **read-only** para detectar desalineaciones críticas entre código y esquema real cuando la DB está disponible:

```bash
composer schema:check
# o
php scripts/schema-compatibility-check.php
```

Qué valida:
- Existencia de columnas críticas en `INFORMATION_SCHEMA.COLUMNS` para tablas clave del Core Admin.
- Mínimos críticos en tablas reales: `core_users`, `core_sessions`, `core_tenants`, `core_roles`, `core_permissions`, `core_role_permissions`, `core_user_roles`, `core_modules`, `core_audit`, `cloud_files`, `cloud_folders`, `mail_messages`, `notifications_queue`, `crm_leads`, `crm_marketing_campaigns`.

Qué **no** valida:
- Tipos de datos, índices, FKs, triggers ni performance de consultas.
- No reemplaza migraciones, pruebas funcionales ni auditorías de integridad completas.

Garantías de seguridad:
- No ejecuta `INSERT`, `UPDATE`, `DELETE`, migraciones, seeds ni DDL.
- Solo usa consultas de lectura (`INFORMATION_SCHEMA`/`SELECT`).
- Si la DB no está disponible o no está configurada, emite `WARN` y termina sin fatal para no bloquear smoke normal.
