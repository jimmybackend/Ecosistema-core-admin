# Core Admin — Checklist final pre-demo privada (día de ejecución) (PR #245)

- **Fecha base (UTC):** 2026-05-18
- **Repositorio:** `jimmybackend/Ecosistema-core-admin`
- **Uso:** ejecutar el mismo día de la demo, antes de abrir pantalla o compartir sesión.
- **Alcance:** operativo/documental para demo privada controlada.

> Esta checklist final **no reemplaza** el runbook ni el dataset seguro; los resume para ejecución rápida del día.
>
> Esta checklist **no certifica** salida a producción SaaS pública.

## 1) Antes de compartir pantalla

- [ ] Confirmé que estoy en el entorno correcto (local/VM controlada de demo).
- [ ] Confirmé usuario demo ficticio activo (`example.test`).
- [ ] Confirmé tenant demo ficticio activo (`DEMO-*`).
- [ ] Confirmé que no hay datos reales visibles en dashboard/módulos.
- [ ] Confirmé que `.env` no se abrirá durante la sesión.
- [ ] Confirmé terminal limpia (sin secretos/tokens/passwords en historial visible).
- [ ] Confirmé navegador sin pestañas sensibles abiertas.
- [ ] Confirmé flags críticas en `false` (mail, s3/drive remoto, ia externa, workflow real, billing/export sensible).

## 2) Validaciones técnicas previas

- [ ] `composer dump-autoload` ejecutado (autoload actualizado).
- [ ] `composer smoke` sin fallos críticos nuevos.
- [ ] `composer schema:usage` en **OK** o en **warning controlado** (ej. DB no disponible en entorno demo).
- [ ] Rutas principales accesibles (login, dashboard, módulos de recorrido).
- [ ] Login demo probado antes de iniciar.
- [ ] Dashboard carga correctamente.
- [ ] Dataset ficticio visible y consistente.

## 3) Verificación de datos demo

- [ ] Emails únicamente con dominio `example.test`.
- [ ] Nombres y alias únicamente ficticios.
- [ ] Campañas con prefijo `CMP-DEMO-`.
- [ ] Leads con prefijo `LEAD-DEMO-`.
- [ ] Archivos demo sin metadata sensible.
- [ ] Reportes con KPIs sintéticos.
- [ ] Sin PII real en pantallas, tablas, logs o exportes.

## 4) Guardrails de integraciones (deben quedar apagadas)

- [ ] SMTP real desactivado.
- [ ] AWS/S3 real desactivado.
- [ ] IA externa desactivada.
- [ ] Workers reales desactivados.
- [ ] Billing real desactivado.
- [ ] Exports con PII desactivados.
- [ ] Registros públicos abiertos desactivados.

## 5) Durante la demo

- [ ] Abrí con mensaje explícito de límites de la demo.
- [ ] Seguí solo el guion aprobado (10–15 min).
- [ ] Diferencié claramente estados: **operativo / read-only / dry-run / controlled**.
- [ ] Evité promesas improvisadas de producción SaaS pública.
- [ ] No abrí `.env`.
- [ ] No abrí logs crudos con contenido sensible.
- [ ] No mostré JSON completo sensible.
- [ ] No mostré IP ni user-agent completos.
- [ ] No ejecuté acciones reales externas.

## 6) Manejo de incidentes (respuesta inmediata)

- [ ] Si aparece dato real: pausar demo, ocultar pantalla, declarar incidente, volver a dataset ficticio o cerrar sesión.
- [ ] Si aparece secreto/token/password: detener compartición, rotar/invalidar secreto, registrar incidente.
- [ ] Si falla login: usar usuario demo de respaldo o reiniciar sesión controlada.
- [ ] Si falla un módulo: continuar con ruta alternativa del guion (read-only/dry-run) y registrar pendiente.
- [ ] Si falla `schema:usage`: confirmar si es warning controlado por DB; clasificar **Go con advertencias** y documentar.
- [ ] Si aparece error técnico: mostrar mensaje breve de contención, no exponer trazas sensibles, continuar recorrido seguro.
- [ ] Si se comparte pantalla incorrecta: cortar share inmediato, corregir ventana, reiniciar contexto de límites.

## 7) Después de la demo

- [ ] Cerrar sesión de usuario demo.
- [ ] Detener pantalla compartida.
- [ ] Revisar capturas compartidas y eliminar material sensible si existiera.
- [ ] Apagar VM/instancia temporal si aplica.
- [ ] Limpiar archivos/datos temporales de sesión.
- [ ] Registrar feedback recibido.
- [ ] Registrar pendientes y riesgos para backlog.
- [ ] Confirmar que no quedaron servicios reales activos.

## 8) Resultado de la demo (plantilla)

- **Fecha:**
- **Presentador:**
- **Audiencia:**
- **Entorno:**
- **Resultado:** Go / Go con advertencias / No-Go
- **Advertencias:**
- **Pendientes:**
- **Decisión siguiente:**

## 9) Frase final recomendada

> “Esta demo confirma preparación para demo privada controlada, no salida a producción SaaS pública.”


## Actualización de ejecución real en VM controlada (2026-05-19)

- Repo actualizado y limpio en `main` (commit `836d0db`, PR #257).
- Nginx y PHP-FPM operativos (`fastcgi_pass unix:/run/php/php8.5-fpm.sock`).
- `GET /login` validado en local y público con `HTTP 200`.
- `POST /login` validado con `HTTP 302 Found` y `Location: /dashboard`.
- Dashboard confirmado visible en navegador.
- DB remota `adbbmis1_eco` autorizada por IP pública de la VM en Remote MySQL / Manage Access Hosts.
- Causa raíz del fallo inicial: `.env` ilegible para `www-data` por `chmod 600`.
- Corrección aplicada: owner deploy user + group `www-data` + `chmod 640` para `.env`.
- Pendiente obligatorio preprod/prod: rotar `DB_PASSWORD`, `APP_KEY` y `CORE_REGISTRATION_INVITE_CODE`.
- `composer schema:usage` en validación real reporta 5 incompatibilidades pendientes (`mail_messages.status`, `os_ai_proposals.id`, `os_ai_proposals.module_code`, `os_ai_proposals.entity_table`, `os_ai_proposals.entity_id`) sin bloquear login.
