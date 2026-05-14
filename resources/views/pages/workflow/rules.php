<?php
$workflow = (array) ($contentData['workflow'] ?? []);
$summary = (array) ($workflow['summary'] ?? []);
$items = (array) ($workflow['items'] ?? []);
?>
<section class="eco-card">
  <h1>Workflow</h1>
  <p>Listado read-only de reglas. No expone conditions_json ni permite ejecución.</p>
  <table class="eco-table"><thead><tr><th>Módulo trigger</th><th>Activa</th><th>Total</th></tr></thead><tbody>
    <?php if ($summary === []): ?><tr><td colspan="3">Sin resumen para el tenant actual.</td></tr><?php else: foreach ($summary as $row): ?>
      <tr><td><?= e((string) ($row['trigger_module'] ?? '')) ?></td><td><?= (($row['is_active'] ?? false) ? 'Sí' : 'No') ?></td><td><?= e((string) ($row['total_rules'] ?? 0)) ?></td></tr>
    <?php endforeach; endif; ?>
  </tbody></table>
  <table class="eco-table" style="margin-top:1rem;"><thead><tr><th>ID</th><th>Name</th><th>Trigger module</th><th>Trigger event</th><th>Is active</th><th>Actions count</th><th>Conditions present</th><th>Conditions exposed</th><th>Created at</th><th>Updated at</th><th>Detalle</th></tr></thead><tbody>
    <?php if ($items === []): ?><tr><td colspan="11">Sin reglas para el tenant actual.</td></tr><?php else: foreach ($items as $item): ?>
      <tr>
        <td><?= e((string) ($item['id'] ?? 0)) ?></td><td><?= e((string) ($item['name'] ?? '')) ?></td><td><?= e((string) ($item['trigger_module'] ?? '')) ?></td><td><?= e((string) ($item['trigger_event'] ?? '')) ?></td><td><?= (($item['is_active'] ?? false) ? 'Sí' : 'No') ?></td><td><?= e((string) ($item['actions_count'] ?? 0)) ?></td><td><?= (($item['conditions_json_present'] ?? false) ? 'Sí' : 'No') ?></td><td>No</td><td><?= e((string) ($item['created_at'] ?? '')) ?></td><td><?= e((string) ($item['updated_at'] ?? '')) ?></td><td><a href="/workflow/rules/<?= e((string) ($item['id'] ?? 0)) ?>">Abrir</a></td>
      </tr>
    <?php endforeach; endif; ?>
  </tbody></table>
</section>
