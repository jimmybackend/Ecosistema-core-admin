<?php $workflow = (array) ($contentData['workflow'] ?? []); $summary = (array) ($workflow['summary'] ?? []); $items = (array) ($workflow['items'] ?? []); ?>
<section class="space-y-4">
<h1>Workflow Runs</h1><p><a href="/workflow">← Volver a workflow</a></p><p><strong>Modo:</strong> read-only. Sin ejecución ni reintentos.</p>
<h2>Resumen por estado</h2>
<?php if ($summary === []): ?><p>Sin ejecuciones registradas.</p><?php else: ?><ul><?php foreach ($summary as $row): ?><li><?= e((string) ($row['status'] ?? '')) ?>: <?= e((string) ($row['total_runs'] ?? 0)) ?></li><?php endforeach; ?></ul><?php endif; ?>
<table><thead><tr><th>ID</th><th>Rule</th><th>Source module</th><th>Status</th><th>Input JSON</th><th>Output JSON</th><th>Error preview</th><th>Started</th><th>Finished</th><th>Created</th><th>Detalle</th></tr></thead><tbody>
<?php if ($items === []): ?><tr><td colspan="11">No hay runs para este tenant.</td></tr><?php else: foreach ($items as $item): ?><tr>
<td><?= e((string) ($item['id'] ?? 0)) ?></td><td><?= e((string) ($item['rule_name'] ?? 'N/A')) ?></td><td><?= e((string) ($item['source_module'] ?? '')) ?></td><td><?= e((string) ($item['status'] ?? '')) ?></td>
<td><?= (($item['input_json_present'] ?? false) ? 'Sí' : 'No') ?></td><td><?= (($item['output_json_present'] ?? false) ? 'Sí' : 'No') ?></td><td><?= e((string) ($item['error_message_preview'] ?? '')) ?></td>
<td><?= e((string) ($item['started_at'] ?? '')) ?></td><td><?= e((string) ($item['finished_at'] ?? '')) ?></td><td><?= e((string) ($item['created_at'] ?? '')) ?></td>
<td><a href="/workflow/runs/<?= e((string) ($item['id'] ?? 0)) ?>">Abrir</a></td></tr><?php endforeach; endif; ?>
</tbody></table></section>
