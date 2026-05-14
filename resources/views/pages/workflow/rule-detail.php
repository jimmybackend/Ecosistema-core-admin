<?php
$detail = (array) ($contentData['detail'] ?? []);
$rule = (array) ($detail['rule'] ?? []);
$actions = (array) ($detail['actions'] ?? []);
?>
<section class="eco-card">
  <h1>Workflow</h1>
  <p>Detalle read-only de regla y acciones asociadas.</p>
  <p><a href="/workflow/rules">← Volver a reglas</a> · <a href="/workflow/rules/<?= e((string) ($rule['id'] ?? 0)) ?>/runs">Ver ejecuciones</a></p>
  <table class="eco-table"><tbody>
    <tr><td>ID</td><td><?= e((string) ($rule['id'] ?? 0)) ?></td></tr>
    <tr><td>Name</td><td><?= e((string) ($rule['name'] ?? '')) ?></td></tr>
    <tr><td>Description</td><td><?= e((string) ($rule['description_preview'] ?? '')) ?></td></tr>
    <tr><td>Trigger module</td><td><?= e((string) ($rule['trigger_module'] ?? '')) ?></td></tr>
    <tr><td>Trigger event</td><td><?= e((string) ($rule['trigger_event'] ?? '')) ?></td></tr>
    <tr><td>Created by</td><td><?= e((string) ($rule['created_by_label'] ?? 'N/A')) ?></td></tr>
    <tr><td>Conditions present</td><td><?= (($rule['conditions_json_present'] ?? false) ? 'Sí' : 'No') ?></td></tr>
    <tr><td>Conditions exposed</td><td>No</td></tr>
  </tbody></table>
  <h2>Acciones (read-only)</h2>
  <table class="eco-table"><thead><tr><th>ID</th><th>Sort</th><th>Action type</th><th>Label</th><th>Target module</th><th>Config present</th><th>Config exposed</th><th>Is active</th><th>Created at</th></tr></thead><tbody>
  <?php if ($actions === []): ?><tr><td colspan="9">Sin acciones para esta regla.</td></tr><?php else: foreach ($actions as $action): ?>
    <tr><td><?= e((string) ($action['id'] ?? 0)) ?></td><td><?= e((string) ($action['sort_order'] ?? 0)) ?></td><td><?= e((string) ($action['action_type'] ?? '')) ?></td><td><?= e((string) ($action['action_type_label'] ?? '')) ?></td><td><?= e((string) ($action['target_module'] ?? '')) ?></td><td><?= (($action['config_json_present'] ?? false) ? 'Sí' : 'No') ?></td><td>No</td><td><?= (($action['is_active'] ?? false) ? 'Sí' : 'No') ?></td><td><?= e((string) ($action['created_at'] ?? '')) ?></td></tr>
  <?php endforeach; endif; ?>
  </tbody></table>
</section>
