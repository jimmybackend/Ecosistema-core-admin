# Seguimiento PR #226 — Auditar y corregir Core/Auth/RBAC contra tablas core_* reales

## 1. Alcance ejecutado
- [x] Repositorio confirmado: `jimmybackend/Ecosistema-core-admin`
- [x] Fuente DB real usada: `adbbmis1_eco.sql`
- [x] Tablas del contrato revisadas
- [x] Archivos/rutas inspeccionados
- [x] No se modificó otro repositorio

## 2. Tablas y campos auditados
| Tabla real | Campos revisados | CRUD detectado | Archivos donde se usa | Estado |
|---|---|---|---|---|
| core_audit | tenant_id,user_id,module_code,action,entity_table,entity_id,old_values,new_values,ip_address,user_agent | C,R | app/Core/System/AuditLogger.php, app/Core/System/AuditRepository.php, routes/web.php | Corregido |
| core_roles | id,tenant_id,slug,name,description,is_system,created_at,updated_at | C,R,U | app/Core/Roles/*, app/Core/Permissions/PermissionRepository.php, routes/web.php | Con ajustes tenant |
| core_user_roles | tenant_id,user_id,role_id,assigned_by_user_id,assigned_at | C,R,D | app/Core/Users/UserRoleRepository.php, routes/web.php | OK |
| core_permissions | id,module_id,code,name,description,created_at | C,R,U | app/Core/Permissions/*, routes/web.php | OK |
| core_role_permissions | tenant_id,role_id,permission_id,created_at | C,R,D | app/Core/Permissions/PermissionRepository.php, routes/web.php | OK |
| core_users | tenant_id,email,username,password_hash,display_name,first_name,last_name,phone,user_type,status,last_login_at | C,R,U | app/Core/Users/*, app/Core/Auth/UserRepository.php, routes/web.php | Con ajustes tenant |
| core_sessions | tenant_id,user_id,session_token_hash,refresh_token_hash,source,ip_address,user_agent,expires_at,revoked_at | C,R,U | app/Core/Auth/SessionRepository.php | OK |
| core_modules | id,code,name,description,table_prefix,is_billable,is_core,status,created_at | C,R,U | app/Core/Modules/*, app/Core/Permissions/PermissionRepository.php, routes/web.php | OK |
| core_tenants | id,name,slug,status,timezone,locale | R,U | app/Core/Tenants/*, app/Core/Roles/RoleRepository.php, app/Core/Users/UserRepository.php | OK |

## 3. Hallazgos encontrados
| Severidad | Archivo | Función/ruta | Tabla | Campo | Problema | Acción tomada |
|---|---|---|---|---|---|---|
| Alta | app/Core/System/AuditLogger.php | log | core_audit | entity_type,before_data,after_data | INSERT usaba columnas inexistentes en contrato real | Se migró a entity_table/old_values/new_values y module_code |
| Alta | app/Core/System/AuditRepository.php | listRecent | core_audit | entity_type,before_data,after_data | SELECT consultaba columnas inexistentes | Se actualizó SELECT con columnas reales |
| Alta | routes/web.php | POST /roles, /roles/{id}, /users, /users/{id} | core_roles/core_users | tenant_id | tenant_id venía de request libre | tenant_id se fuerza desde sesión autenticada |

## 4. Cambios realizados
- [x] Repositories corregidos si usaban columnas inexistentes
- [x] Services corregidos si enviaban payload incorrecto
- [x] Routes corregidas si faltaba sesión/permiso/CSRF/tenant
- [x] Views corregidas si exponían campos sensibles
- [x] Smoke-check/schema-usage actualizado si aplica
- [x] Documentación `docs/schema-usage/` actualizada

## 5. Campos obligatorios de INSERT verificados
| Tabla | Campos mínimos reales | ¿El código los llena? | Fuente del valor | Observación |
|---|---|---|---|---|
| core_audit | action | Sí | payload de auditoría interna | Corregido a columnas reales |
| core_roles | tenant_id,name,slug | Sí | tenant_id de sesión, resto de formulario validado | Ya no acepta tenant libre |
| core_users | tenant_id,email,password_hash | Sí | tenant_id de sesión, email de formulario validado, hash interno | Ya no acepta tenant libre |
| core_role_permissions | tenant_id,role_id,permission_id | Sí | role tenant + contexto sesión | Valida pertenencia tenant |
| core_user_roles | tenant_id,user_id,role_id | Sí | tenant del usuario objetivo + roles validados | Asignación segura |

## 6. Reglas tenant/user verificadas
- [x] `tenant_id` se toma de sesión/contexto validado cuando aplica
- [x] `user_id`/`owner_user_id`/`created_by_user_id` no se aceptan libremente desde request cuando aplica
- [x] Lecturas administrativas filtran por tenant cuando la tabla es tenant-aware
- [x] Escrituras administrativas llenan tenant desde contexto seguro

## 7. Campos sensibles revisados
- [x] No se imprimen hashes completos
- [x] No se imprimen tokens completos
- [x] No se imprime `s3_key`, rutas internas o secretos
- [x] JSON sensible se muestra como preview, máscara o `*_present`

## 8. Validaciones ejecutadas
- [x] `composer dump-autoload`
- [x] `php -l routes/web.php`
- [x] `php -l scripts/smoke-check.php`
- [x] `composer smoke`
- [ ] `composer schema:usage` si existe

## 9. Resultado
- Estado: `Go con advertencias`
- Warnings aceptados: no se ejecutó `composer schema:usage` porque no está definido en el alcance obligatorio.
- Pendientes que pasan al backlog: endurecer filtrado tenant en listados globales de roles/usuarios si el producto decide vista estrictamente tenant-scoped para usuarios admin multi-tenant.
- Evidencia principal: cambios en `app/Core/System/AuditLogger.php`, `app/Core/System/AuditRepository.php`, `routes/web.php` y este checklist.
