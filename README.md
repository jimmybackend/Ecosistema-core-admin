# Ecosistema Core Admin

Este repositorio contiene la aplicación operativa/admin **Ecosistema Core Admin**.

## Estado actual

Este PR corresponde a la **Capa 2 — Estructura base del proyecto**.

## Incluye

- Estructura base de carpetas `app/`, `bootstrap/`, `config/`, `routes/`, `storage/logs/`.
- Front controller mínimo en `public/index.php`.
- Enrutado básico para `/`.
- Compatibilidad con el sistema UI existente (`public/assets/css/ecosistema-ui.css`).

## No incluye aún

- Conexión a base de datos (PDO).
- Migraciones.
- Autenticación/login.
- API separada, workers o procesos externos.

## Ejecución local rápida

```bash
php -S localhost:8000 -t public
```

Luego abrir: <http://localhost:8000>
