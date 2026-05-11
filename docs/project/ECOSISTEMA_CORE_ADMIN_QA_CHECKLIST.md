# Ecosistema Core Admin — QA Checklist Manual

## 1) Login / Logout
- [ ] `GET /login` responde 200 y renderiza formulario.
- [ ] `POST /login` con credenciales válidas crea sesión y redirige a `/dashboard`.
- [ ] `POST /logout` invalida sesión y redirige a `/login`.

## 2) Dashboard
- [ ] `GET /dashboard` autenticado responde 200.
- [ ] Sin sesión, `GET /dashboard` redirige a `/login`.

## 3) Tenants
- [ ] Listado (`/tenants`) visible.
- [ ] Crear (`/tenants/create` + POST) operativo.
- [ ] Editar y cambio de estado operativo.

## 4) Usuarios
- [ ] Listado (`/users`) visible.
- [ ] Crear/editar/cambiar estado/cambiar password operativo.
- [ ] No mostrar `password_hash` en UI.

## 5) Roles
- [ ] Listado (`/roles`) visible.
- [ ] Crear/editar/cambiar estado operativo.

## 6) Permisos
- [ ] Listado (`/permissions`) visible.
- [ ] Crear/editar/cambiar estado operativo.
- [ ] Asignación de permisos a rol (`/roles/{id}/permissions`) operativa.

## 7) Módulos
- [ ] Listado (`/modules`) visible.
- [ ] Crear/editar/cambiar estado operativo.

## 8) Health
- [ ] `GET /system/health` visible.
- [ ] `POST /system/health/{id}/run` ejecuta check manual con respuesta controlada.

## 9) Logs
- [ ] `GET /system/logs` visible.

## 10) Auditoría
- [ ] `GET /system/audit` visible.

## 11) Mail
- [ ] `GET /mail`, `/mail/compose`, `/mail/messages/{id}` visibles.
- [ ] No renderizar `body_html` crudo inseguro.
- [ ] Sin envío real SMTP (limitación conocida).

## 12) Cloud
- [ ] `GET /cloud`, `/cloud/folders`, `/cloud/files/{id}` visibles.
- [ ] Sin subida/descarga S3 real (limitación conocida).

## 13) Onboarding
- [ ] `/onboarding`, `/onboarding/flows`, `/onboarding/runs/*` accesibles.
- [ ] Sin aprovisionamiento real (limitación conocida).

## 14) Health técnico DB
- [ ] `GET /health/db` responde JSON con `status` controlado (`ok` o `error`).

## 15) Seguridad básica
- [ ] CSRF presente en formularios POST.
- [ ] Redirección a `/login` sin sesión en rutas admin.
- [ ] Escape HTML básico (`e()`) en vistas para variables.
- [ ] No exponer: `password_hash`, `session_token_hash`, `refresh_token_hash`.
- [ ] No exponer: `DB_PASSWORD`, `AWS_SECRET`, `SECRET`, tokens planos ni secrets.
