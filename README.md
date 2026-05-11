# Ecosistema Core Admin

Este repositorio contiene la aplicación operativa/admin **Ecosistema Core Admin**.

## Estado actual

Este PR corresponde a la **Capa 4 — Crear layout base reutilizable**.

## Incluye

- Estructura mínima de vistas PHP reutilizables en `resources/views` (layout, parciales y página inicial).
- Clase de renderizado `App\Http\View\View` para cargar vistas desde `resources/views` sin motor externo.
- Helper global `e()` para escape HTML con `htmlspecialchars`, `ENT_QUOTES` y `UTF-8`.
- Ruta `/` renderizando layout admin base (sin login, sin sesiones, sin consultas SQL).
- Ruta `/health/db` se mantiene como validación técnica de conexión PDO.

## No incluye aún

- Login real o middleware de autenticación.
- Sesiones reales de usuario.
- Consulta de `core_users`.
- Migraciones o cambios de base de datos.
- API separada, workers o frontend público.

## Ejecución local rápida

```bash
php -S 127.0.0.1:8000 -t public
```

Luego abrir:

- Home/layout base: <http://127.0.0.1:8000/>
- Health PDO técnico: <http://127.0.0.1:8000/health/db>

## Qué validar manualmente

- `/` carga el layout administrativo visual base usando `public/assets/css/ecosistema-ui.css`.
- `/` no abre conexión PDO ni ejecuta consultas SQL.
- `/health/db` responde `OK` o `ERROR` controlado sin exponer credenciales.

Esta capa deja la base visual lista para el siguiente paso: login visual, previo a la autenticación real.
