# Ecosistema Core Admin

Este repositorio contiene la aplicación operativa/admin **Ecosistema Core Admin**.

## Estado actual

Este PR corresponde a la **Capa 3 — Configuración de entorno y conexión PDO segura**.

## Incluye

- Carga local de variables de entorno desde `.env` (si existe), sin sobreescribir variables ya definidas por el entorno.
- Archivo de ejemplo `.env.example` para configuración base.
- Configuración de base de datos por entorno en `config/database.php`.
- Fábrica PDO (`app/Core/Database/PdoFactory.php`) con configuración segura para errores y prepared statements reales.
- Ruta técnica `/health/db` para validar conectividad PDO sin consultar tablas.

## No incluye aún

- Migraciones o creación de tablas.
- Consulta de `core_users`.
- Autenticación/login.
- Dashboard funcional.
- API separada, workers o procesos externos.

## Configuración de entorno

1. Copiar archivo de ejemplo:

```bash
cp .env.example .env
```

2. Editar `.env` y ajustar variables de base de datos según tu entorno:

- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `DB_CHARSET`
- `DB_COLLATION`

> Nota: `.env` no se debe commitear y contiene valores locales/sensibles.

## Ejecución local rápida

```bash
php -S 127.0.0.1:8000 -t public
```

Luego abrir: <http://127.0.0.1:8000>

## Validación de conexión PDO

Con el servidor corriendo, abrir:

- <http://127.0.0.1:8000/health/db>

La ruta responde:

- `OK` si logra abrir conexión PDO.
- `ERROR` si no conecta (sin exponer credenciales).

Esta capa valida conectividad de base de datos únicamente; **todavía no implementa login**.
