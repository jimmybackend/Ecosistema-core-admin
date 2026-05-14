<?php
$dryRun = (array) ($contentData['dryRun'] ?? []);
$actions = (array) ($dryRun['actions'] ?? []);
?>
<section class="eco-card">
  <h1>Workflow dry-run</h1>
  <p>Simulación administrativa: no ejecuta acciones reales ni escribe en base de datos.</p>
  <form method="post" action="/workflow/dry-run" class="eco-form">
    <input type="hidden" name="_csrf" value="<?= e((string) ($csrfToken ?? '')) ?>">
    <label>Trigger module <input type="text" name="trigger_module" value="<?= e((string) ($dryRun['trigger_module'] ?? '')) ?>"></label>
    <label>Trigger event <input type="text" name="trigger_event" value="<?= e((string) ($dryRun['trigger_event'] ?? '')) ?>"></label>
    <label>Source module <input type="text" name="source_module" value="<?= e((string) ($dryRun['source_module'] ?? '')) ?>"></label>
    <label>Source table <input type="text" name="source_table" value=""></label>
    <label>Source id <input type="text" name="source_id" value=""></label>
    <button type="submit">Simular evento</button>
  </form>
  <table class="eco-table"><tbody>
    <tr><td>Rule</td><td>#<?= e((string) ($dryRun['rule_id'] ?? 0)) ?> <?= e((string) ($dryRun['rule_name'] ?? '')) ?></td></tr>
    <tr><td>Matched</td><td><?= (($dryRun['matched'] ?? false) ? 'Sí' : 'No') ?></td></tr>
    <tr><td>Conditions present</td><td><?= (($dryRun['conditions_json_present'] ?? false) ? 'Sí' : 'No') ?></td></tr>
    <tr><td>Conditions exposed</td><td>No</td></tr>
    <tr><td>Execution</td><td>executed=false · db_write=false · external_calls=false</td></tr>
  </tbody></table>
  <h2>Acciones simuladas</h2>
  <table class="eco-table"><thead><tr><th>ID</th><th>Sort</th><th>Type</th><th>Target</th><th>Config present</th><th>would_execute</th><th>executed</th><th>blocked_reason</th></tr></thead><tbody>
    <?php if ($actions === []): ?><tr><td colspan="8">Sin acciones simuladas.</td></tr><?php else: foreach ($actions as $action): ?>
      <tr><td><?= e((string) ($action['action_id'] ?? 0)) ?></td><td><?= e((string) ($action['sort_order'] ?? 0)) ?></td><td><?= e((string) ($action['action_type'] ?? '')) ?></td><td><?= e((string) ($action['target_module'] ?? '')) ?></td><td><?= (($action['config_json_present'] ?? false) ? 'Sí' : 'No') ?></td><td><?= (($action['would_execute'] ?? false) ? 'Sí' : 'No') ?></td><td>No</td><td><?= e((string) ($action['blocked_reason'] ?? '')) ?></td></tr>
    <?php endforeach; endif; ?>
  </tbody></table>
</section>
