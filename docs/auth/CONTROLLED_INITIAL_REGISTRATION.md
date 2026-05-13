# Registro inicial controlado (Core Admin)

Este flujo permite crear **un usuario inicial** desde navegador durante el setup de una VM, sin habilitar registro público general.

## Principios de seguridad
- Está **apagado por defecto** (`CORE_REGISTRATION_ENABLED=false`).
- Requiere `invite_code` y validación segura contra `CORE_REGISTRATION_INVITE_CODE`.
- No crea tenants automáticamente.
- No crea roles automáticamente.
- No modifica esquema de base de datos ni usa migraciones/seeds.

## Variables de entorno
- `CORE_REGISTRATION_ENABLED=false`
- `CORE_REGISTRATION_MODE=first_user`
- `CORE_REGISTRATION_INVITE_CODE=`
- `CORE_REGISTRATION_DEFAULT_TENANT_ID=`
- `CORE_REGISTRATION_DEFAULT_ROLE_ID=`

## Uso recomendado en VM (temporal)
1. Configura en `.env`:
   - `CORE_REGISTRATION_ENABLED=true`
   - `CORE_REGISTRATION_MODE=first_user`
   - `CORE_REGISTRATION_INVITE_CODE` con un código fuerte temporal
   - `CORE_REGISTRATION_DEFAULT_TENANT_ID` con un tenant existente
   - `CORE_REGISTRATION_DEFAULT_ROLE_ID` opcional
2. Abre `/register` y crea la cuenta inicial.
3. Inicia sesión en `/login`.
4. Vuelve a desactivar registro:
   - `CORE_REGISTRATION_ENABLED=false`
   - (recomendado) limpia `CORE_REGISTRATION_INVITE_CODE`

## Alcance funcional
- Si `CORE_REGISTRATION_MODE=first_user`, se bloquea el registro cuando ya existe un usuario para el tenant configurado.
- Si `CORE_REGISTRATION_DEFAULT_ROLE_ID` está configurado y es válido para el tenant, se intenta asignar rol.
- Si no se puede completar la asignación de rol, el usuario puede quedar creado y la asignación debe completarse desde un proceso administrativo controlado.

## Fuera de alcance
- No es registro público multiusuario.
- No incluye recuperación de contraseña, verificación por email, MFA o captcha.


## Flujo recomendado para VM (onboarding inicial controlado)
En la VM:
```bash
cp .env.example .env
```

Editar `.env` (valores de ejemplo, sin secretos reales):
```dotenv
CORE_REGISTRATION_ENABLED=true
CORE_REGISTRATION_MODE=first_user
CORE_REGISTRATION_INVITE_CODE=CAMBIAR_POR_UN_CODIGO_TEMPORAL_SEGURO
CORE_REGISTRATION_DEFAULT_TENANT_ID=1
CORE_REGISTRATION_DEFAULT_ROLE_ID=
```

Luego abrir:
- `http://34.42.148.158/register`

Crear la cuenta inicial y verificar acceso en `/login`.

Después apagar registro:
```dotenv
CORE_REGISTRATION_ENABLED=false
```

Importante:
- No commitear `.env`.
- No dejar `CORE_REGISTRATION_ENABLED=true` en producción.
- No poner el invite code real en README ni docs.
- No exponer passwords ni tokens.
