# Estado real para presentación pública

Documento de referencia para hablar con lenguaje honesto en demo, preventa y material externo.

## Reglas de comunicación

- No afirmar producción total si el módulo está en demo/preproducción.
- No afirmar que **Billing/Integrations/Support/Workers** están terminados.
- No afirmar que la IA actúa sola sin revisión humana.
- No mostrar datos reales ni secretos.

## Estados operativos (definiciones)

- **Read-only**: consulta sin escritura.
- **Dry-run**: simulación sin impacto final.
- **Controlled**: ejecución real sólo con flags/permisos y contexto controlado.
- **Base operativa**: funcionalidad estable de administración y gobierno.

## Matriz resumida

| Módulo | Qué se puede mostrar | Estado | Qué no se debe prometer | Próximo paso |
|---|---|---|---|---|
| Núcleo admin + auth + permisos | Operación diaria base y control de accesos | Base operativa | Producto total end-to-end ya cerrado | End-to-end por rol |
| Auditoría + health | Evidencia de control y monitoreo base | Base operativa | Observabilidad enterprise completa | Alertas integradas |
| Módulos extendidos (CRM/Campaigns/Landing/URL/Workflow/Reports/Drive/AI) | Consultas, trazabilidad y simulaciones controladas | Mixto (read-only/dry-run/controlled) | Habilitación productiva total por defecto | Activación por entorno |
| Workers | Estado actual/documentación | Parcial/roadmap | Pipeline productivo completo activo | Implementación gradual |
| Billing / Integrations / Support | Roadmap y alcance esperado | Roadmap | Módulo terminado y operando comercialmente | Definir MVPs |

## Nota para presentadores

Si una capacidad depende de flag, decir explícitamente: “está disponible en modo controlled y se habilita por entorno”.
