# URL Locator redirect dry-run

Este flujo agrega una simulación protegida en `/url/locator/links/{id}/redirect-dry-run`.

## Qué valida
- Pertenencia del short link al tenant autenticado.
- Status (`active`, `inactive`, `expired`, `blocked`).
- Expiración por `expires_at`.
- Límite `max_clicks` vs `click_count`.
- Si requiere access token (`requires_access_token`) sin validar token real todavía.
- Resolución por `smart_type` y selección de idioma (`requested_language`, `Accept-Language`, default y fallback).

## Qué NO ejecuta
- No hace redirección pública.
- No escribe en `url_clicks`.
- No incrementa `url_short_links.click_count`.
- No ejecuta `header('Location')`.

## Salida segura
El resultado retorna `mode=dry-run`, `redirect_executed=false`, `db_write=false`, `click_logged=false`, `click_count_incremented=false`, `public_redirects=false` y solo previews seguras de URL.

## Preparación para PR #87
Este PR deja lista la resolución y validaciones de elegibilidad para implementar redirección pública real y tracking controlado en el PR #87.
