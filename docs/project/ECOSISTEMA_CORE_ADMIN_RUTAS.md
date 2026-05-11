# Ecosistema Core Admin — Rutas actuales

| Método | Ruta | Módulo | Requiere sesión | Acción | Estado actual |
|---|---|---|---|---|---|
| GET | / | Home/Auth | No | Redirección a login/dashboard según sesión | Activo |
| GET | /login | Auth | No | Form login | Activo |
| POST | /login | Auth | No | Iniciar sesión | Activo |
| POST | /logout | Auth | Sí | Cerrar sesión | Activo |
| GET | /dashboard | Dashboard | Sí | Panel principal | Activo |
| GET | /tenants | Tenants | Sí | Listar tenants | Activo |
| GET | /tenants/create | Tenants | Sí | Form crear tenant | Activo |
| POST | /tenants | Tenants | Sí | Guardar tenant | Activo |
| GET | /tenants/{id}/edit | Tenants | Sí | Form editar tenant | Activo |
| POST | /tenants/{id} | Tenants | Sí | Actualizar tenant | Activo |
| POST | /tenants/{id}/status | Tenants | Sí | Cambiar estado tenant | Activo |
| GET | /users | Usuarios | Sí | Listar usuarios | Activo |
| GET | /users/create | Usuarios | Sí | Form crear usuario | Activo |
| POST | /users | Usuarios | Sí | Guardar usuario | Activo |
| GET | /users/{id}/edit | Usuarios | Sí | Form editar usuario | Activo |
| POST | /users/{id} | Usuarios | Sí | Actualizar usuario | Activo |
| POST | /users/{id}/status | Usuarios | Sí | Cambiar estado usuario | Activo |
| POST | /users/{id}/password | Usuarios | Sí | Cambiar password usuario | Activo |
| GET | /roles | Roles | Sí | Listar roles | Activo |
| GET | /roles/create | Roles | Sí | Form crear rol | Activo |
| POST | /roles | Roles | Sí | Guardar rol | Activo |
| GET | /roles/{id}/edit | Roles | Sí | Form editar rol | Activo |
| POST | /roles/{id} | Roles | Sí | Actualizar rol | Activo |
| POST | /roles/{id}/status | Roles | Sí | Cambiar estado rol | Activo |
| GET | /permissions | Permisos | Sí | Listar permisos | Activo |
| GET | /permissions/create | Permisos | Sí | Form crear permiso | Activo |
| POST | /permissions | Permisos | Sí | Guardar permiso | Activo |
| GET | /permissions/{id}/edit | Permisos | Sí | Form editar permiso | Activo |
| POST | /permissions/{id} | Permisos | Sí | Actualizar permiso | Activo |
| POST | /permissions/{id}/status | Permisos | Sí | Cambiar estado permiso | Activo |
| GET | /roles/{id}/permissions | Permisos/Roles | Sí | Form asignación permisos a rol | Activo |
| POST | /roles/{id}/permissions | Permisos/Roles | Sí | Guardar permisos de rol | Activo |
| GET | /modules | Módulos | Sí | Listar módulos | Activo |
| GET | /modules/create | Módulos | Sí | Form crear módulo | Activo |
| POST | /modules | Módulos | Sí | Guardar módulo | Activo |
| GET | /modules/{id}/edit | Módulos | Sí | Form editar módulo | Activo |
| POST | /modules/{id} | Módulos | Sí | Actualizar módulo | Activo |
| POST | /modules/{id}/status | Módulos | Sí | Cambiar estado módulo | Activo |
| GET | /system/health | System | Sí | Listado health checks | Activo |
| POST | /system/health/{id}/run | System | Sí | Ejecutar health check manual | Activo |
| GET | /system/logs | System | Sí | Ver logs | Activo |
| GET | /system/audit | System | Sí | Ver auditoría | Activo |
| GET | /mail | Mail | Sí | Bandeja/listado | Activo |
| GET | /mail/messages/{id} | Mail | Sí | Ver mensaje | Activo |
| GET | /mail/compose | Mail | Sí | Redactar borrador | Activo |
| POST | /mail/drafts | Mail | Sí | Guardar borrador | Activo |
| POST | /mail/messages/{id}/read | Mail | Sí | Marcar leído/no leído | Activo |
| POST | /mail/messages/{id}/star | Mail | Sí | Marcar destacado | Activo |
| POST | /mail/messages/{id}/trash | Mail | Sí | Enviar a papelera | Activo |
| GET | /cloud | Cloud | Sí | Listado archivos recientes | Activo |
| GET | /cloud/files/{id} | Cloud | Sí | Ver archivo | Activo |
| POST | /cloud/files/{id}/archive | Cloud | Sí | Archivar archivo | Activo |
| POST | /cloud/files/{id}/trash | Cloud | Sí | Enviar a papelera | Activo |
| GET | /cloud/folders | Cloud | Sí | Listar carpetas | Activo |
| GET | /cloud/folders/create | Cloud | Sí | Form crear carpeta | Activo |
| POST | /cloud/folders | Cloud | Sí | Guardar carpeta | Activo |
| POST | /cloud/folders/{id}/trash | Cloud | Sí | Papelera carpeta | Activo |
| GET | /onboarding | Onboarding | Sí | Dashboard onboarding | Activo |
| GET | /onboarding/flows | Onboarding | Sí | Listado flows | Activo |
| GET | /onboarding/runs/create | Onboarding | Sí | Form crear run | Activo |
| POST | /onboarding/runs | Onboarding | Sí | Guardar run | Activo |
| GET | /onboarding/runs/{id} | Onboarding | Sí | Ver run | Activo |
| POST | /onboarding/runs/{id}/start | Onboarding | Sí | Iniciar run | Activo |
| POST | /onboarding/runs/{id}/cancel | Onboarding | Sí | Cancelar run | Activo |
| POST | /onboarding/run-steps/{id}/status | Onboarding | Sí | Actualizar estado step | Activo |
| GET | /health/db | Health técnico | No | Health de conexión DB (JSON) | Activo |
