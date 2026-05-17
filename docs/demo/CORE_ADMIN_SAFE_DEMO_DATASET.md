# CORE Admin — Safe Demo Dataset (mínimo y no productivo)

Documento para definir un dataset de demo/QA **ficticio**, mínimo y seguro para `Ecosistema-core-admin`, sin exponer datos reales, secretos ni credenciales.

## 1) Principios obligatorios

- Este dataset es **solo demo/QA local**; no se declara como productivo.
- Todo dato debe ser sintético/ficticio (empresa, personas, correos, teléfonos, dominios, campañas).
- No versionar contraseñas, hashes reales, tokens, API keys, secretos ni dumps SQL con datos reales.
- Mantener por defecto flags críticas en `false` para evitar envíos, integraciones remotas o escrituras externas.
- Respetar estado real por módulo: operativo, read-only, dry-run, controlled por flags y roadmap.

## 2) Perfil de entorno para demo segura

Configurar demo con defaults seguros documentados:

- SMTP/envío real desactivado.
- AWS/S3/Drive remoto desactivado.
- Integraciones IA externas desactivadas.
- Workers/cron productivos no habilitados para demo.

Referencia base de flags: `docs/project/ECOSISTEMA_FLAGS_SAFE_DEFAULTS.md` y `docs/security/CORE_ADMIN_FLAGS_PERMISSIONS_SECURITY_MATRIX.md`.

## 3) Tenant demo (ficticio)

Usar un único tenant mínimo:

- `tenant_code`: `demo_nova`
- `tenant_name`: `Nova Demo Labs`
- `status`: `active`
- `timezone`: `UTC`
- `locale`: `es_MX`

> Prohibido reutilizar nombres de clientes reales o marcas registradas internas.

## 4) Usuarios demo (ficticios) y contraseñas

Usuarios sugeridos:

1. **Owner demo**
   - Nombre: `Alicia Demo`
   - Email: `alicia.demo+owner@example.test`
2. **Operador demo**
   - Nombre: `Bruno Operador`
   - Email: `bruno.operador@example.test`
3. **Auditor demo**
   - Nombre: `Carla Auditoria`
   - Email: `carla.audit@example.test`

Reglas de contraseñas:

- Crear contraseñas **solo localmente** (ejemplo: generador local + vault de desarrollo temporal).
- No commitear contraseñas en markdown, seeds, capturas, logs ni `.env.example`.
- Si se requiere reset durante demo, regenerar localmente y destruir al cierre.

## 5) Roles y permisos demo (mínimos)

Roles sugeridos:

- `demo_super_admin` (uso restringido al owner demo)
- `demo_operator`
- `demo_auditor_readonly`

Permisos mínimos por rol:

- `demo_super_admin`: acceso administrativo interno para recorrido técnico de demo.
- `demo_operator`: operación sobre módulos internos permitidos en demo, evitando acciones productivas externas.
- `demo_auditor_readonly`: acceso de lectura a dashboards, auditoría, reportes dry-run/read-only.

Recomendación: mapear permisos usando las rutas reales y matriz de estados, evitando habilitar permisos que requieran integraciones reales.

## 6) Módulos incluidos en dataset demo

Incluir solo registros mínimos que permitan renderizar vistas y ejecutar smoke/QA manual:

- Core/Auth + Tenants/Users/Roles/Permissions (núcleo administrativo).
- Dashboard/System/Audit (lectura y diagnóstico).
- CRM/Campaigns/Landing/URL Locator/Reports/Workflow en modalidad de lectura/dry-run/controlada.
- Cloud/Drive únicamente con metadatos demo locales (sin S3 real).

Excluir de ejecución real:

- Envío SMTP real.
- Upload/download remoto real con AWS/S3.
- Ejecución de provider IA externo.
- Workers productivos completos.

## 7) Datos ficticios sugeridos por vista (read-only / dry-run)

### 7.1 Campaigns demo

- `CMP-DEMO-001` — `Lanzamiento Nova Primavera`
- Estado visual: `draft` o `scheduled` (no envío real)
- Métricas sintéticas: aperturas/clicks simulados

### 7.2 Landing demo

- `landing_slug`: `demo-nova-primavera`
- `title`: `Landing Demo Nova`
- CTA y copy genéricos, sin datos de cliente real

### 7.3 Leads demo

- `LEAD-DEMO-0001` — `Elena Prospecto` — `elena.prospecto@example.test`
- `LEAD-DEMO-0002` — `Mario Interesado` — `mario.interesado@example.test`
- Teléfonos sintéticos no reales (ej. formato reservado ficticio)

### 7.4 Reports demo

- Reporte `Funnel Demo Q2`
- Exportaciones sólo en dry-run cuando aplique
- KPIs de muestra claramente marcados como sintéticos

### 7.5 Archivos demo (sin S3 real)

- Estructura lógica de carpetas/archivos ficticios en DB local
- Nombres ejemplo: `demo-brief.pdf`, `demo-assets.zip`
- Sin URLs firmadas reales ni buckets productivos

## 8) Reglas duras de sanitización

Nunca incluir en dataset/capturas/logs:

- Correos reales.
- Teléfonos reales.
- URLs privadas/internas de cliente.
- Tokens, JWT, API keys, secretos, credenciales.
- Hashes de contraseñas provenientes de entornos reales.

Convención recomendada:

- Correos: dominio `example.test`.
- URLs: `https://demo.local/...` o `https://example.test/...`.
- IDs/folios: prefijos `DEMO-`.

## 9) Limpieza post-demo

Al cerrar demo/QA:

1. Desactivar usuarios demo o eliminar tenant demo según política del entorno local.
2. Invalidar y rotar cualquier credencial temporal usada durante la sesión.
3. Limpiar adjuntos/archivos demo locales y registros de ejecución temporal.
4. Verificar que no queden exports con datos de sesión en carpetas compartidas.
5. Confirmar flags críticas nuevamente en `false`.

Checklist mínimo de cierre:

- [ ] Sin cuentas demo activas innecesarias.
- [ ] Sin secretos temporales vigentes.
- [ ] Sin archivos demo fuera de entorno local controlado.
- [ ] Sin cambios de seguridad persistentes.

## 10) Validación recomendada para PRs documentales

Para cambios documentales de dataset demo, ejecutar:

```bash
composer dump-autoload
php -l routes/web.php
php -l scripts/smoke-check.php
composer smoke
```

Si algún comando no puede ejecutarse por limitaciones del entorno local, documentarlo explícitamente en el PR.

## Referencias internas

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
