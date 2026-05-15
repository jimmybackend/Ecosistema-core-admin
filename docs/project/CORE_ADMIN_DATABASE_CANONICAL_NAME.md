# CORE ADMIN — Nombre canónico de base de datos

## Objetivo
Aclarar qué nombre de base debe usar **Ecosistema Core Admin** en instalación, operación y documentación.

## Regla operativa (Core Admin)
1. El nombre de base **no se hardcodea** en código de aplicación.
2. El nombre operativo se toma de `DB_DATABASE` en `.env`.
3. Para esta instalación/referencia actual, la base esperada es `adbbmis1_eco`, salvo configuración explícita distinta en `.env`.

## Contexto histórico
- En documentación histórica o de diseño puede aparecer `ecosistema` como nombre unificado.
- Esa referencia histórica **no reemplaza** la configuración real de Core Admin.
- Ante diferencias entre texto histórico y entorno, prevalece la configuración activa (`.env` + `config/database.php`).

## Evidencia en este repositorio
- `config/database.php` usa `Env::get('DB_DATABASE', '')` para resolver la base de conexión en runtime.
- `.env.example` y `.env.vm.example` muestran `DB_DATABASE=adbbmis1_eco` como valor esperado de referencia.

## Guía para PRs futuros
- Mantener documentación consistente con la regla: Core Admin usa la base configurada en `.env`.
- Evitar introducir strings de nombres de base en consultas, repositorios o servicios.
- Si una guía menciona `ecosistema`, aclarar si es contexto histórico o conceptual, no un valor obligatorio de runtime.
