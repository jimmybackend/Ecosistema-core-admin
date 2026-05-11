# Ecosistema Core Admin

Este repositorio contiene la aplicación operativa/admin **Ecosistema Core Admin**.

## Estado actual

Este PR corresponde a la **Capa 5 — Crear login visual**.

## Incluye

- Vista visual de login en `resources/views/layouts/auth.php` y `resources/views/pages/auth/login.php`.
- Ruta `GET /login` para renderizar pantalla de acceso administrativo (solo visual).
- Ruta `POST /login` con respuesta controlada indicando que la autenticación real aún no está implementada.
- Enlace visual `Login visual` en el header actual para navegar a `/login`.
- Se mantiene home (`/`) con layout admin y health técnico (`/health/db`).

## No incluye aún

- Login real o middleware de autenticación.
- Sesiones reales de usuario.
- Consulta de `core_users`.
- Consulta de tablas para autenticación.
- Cookies de autenticación.
- Migraciones o cambios de base de datos.
- API separada, workers o frontend público.

## Ejecución local rápida

```bash
php -S 127.0.0.1:8000 -t public
```

Luego abrir:

- Home/layout base: <http://127.0.0.1:8000/>
- Login visual (sin auth real): <http://127.0.0.1:8000/login>
- Health PDO técnico: <http://127.0.0.1:8000/health/db>

## Qué validar manualmente

- `/` carga el layout administrativo visual base usando `public/assets/css/ecosistema-ui.css`.
- `/login` responde con formulario visual (email, password, recordar sesión, botón entrar y enlace de regreso).
- `POST /login` responde mensaje controlado sin autenticar ni crear sesión.
- `/health/db` responde `OK` o `ERROR` controlado sin exponer credenciales.
- No se consulta `core_users` en esta capa.

Esta capa deja listo el login visual para el siguiente paso: **conectar autenticación real con `core_users`**.
