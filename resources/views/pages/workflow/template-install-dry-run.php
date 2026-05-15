<?php
$dryRun = (array) ($contentData['dryRun'] ?? []);
$template = (array) ($dryRun['selected_template'] ?? []);
$rule = (array) ($dryRun['rule_preview'] ?? []);
$actions = (array) ($dryRun['actions_preview'] ?? []);
?>
<section class="eco-card">
  <h1>Workflow Template Install Dry-Run</h1>
  <p><a href="/workflow/templates/<?= rawurlencode((string) ($template['key'] ?? '')) ?>">← Volver a plantilla</a></p>
  <p>Simulación segura: no crea registros en <code>workflow_rules</code> ni <code>workflow_actions</code>.</p>
  <ul>
    <li><strong>Template key:</strong> <?= e((string) ($template['key'] ?? '')) ?></li>
    <li><strong>Template:</strong> <?= e((string) ($template['name'] ?? '')) ?></li>
    <li><strong>Flag habilitada:</strong> <?= !empty($dryRun['feature_enabled']) ? 'true' : 'false' ?></li>
    <li><strong>DB write:</strong> <?= !empty($dryRun['db_write']) ? 'true' : 'false' ?></li>
    <li><strong>Blocked reasons:</strong> <?= e(implode(', ', (array) ($dryRun['blocked_reasons'] ?? []))) ?></li>
  </ul>

  <h2>Rule preview</h2>
  <?php if ($rule === []): ?>
    <p>Sin regla simulada.</p>
  <?php else: ?>
    <table class="eco-table"><tbody>
      <tr><th>name</th><td><?= e((string) ($rule['name'] ?? '')) ?></td></tr>
      <tr><th>trigger</th><td><?= e((string) ($rule['trigger_module'] ?? '')) ?> / <?= e((string) ($rule['trigger_event'] ?? '')) ?></td></tr>
      <tr><th>description_preview</th><td><?= e((string) ($rule['description_preview'] ?? '')) ?></td></tr>
      <tr><th>conditions_json_exposed</th><td>false</td></tr>
    </tbody></table>
  <?php endif; ?>

  <h2>Actions preview</h2>
  <?php if ($actions === []): ?>
    <p>Sin acciones simuladas.</p>
  <?php else: ?>
    <table class="eco-table">
      <thead><tr><th>#</th><th>action_type</th><th>target_module</th><th>config_json_present</th><th>config_json_exposed</th></tr></thead>
      <tbody>
      <?php foreach ($actions as $action): ?>
        <tr>
          <td><?= (int) ($action['sort_order'] ?? 0) ?></td>
          <td><?= e((string) ($action['action_type'] ?? '')) ?></td>
          <td><?= e((string) ($action['target_module'] ?? '')) ?></td>
          <td><?= !empty($action['config_json_present']) ? 'true' : 'false' ?></td>
          <td>false</td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

  <form method="post" action="/workflow/templates/<?= rawurlencode((string) ($template['key'] ?? '')) ?>/install-dry-run">
    <input type="hidden" name="_csrf" value="<?= e((string) ($csrfToken ?? '')) ?>">
    <button type="submit">Re-simular</button>
  </form>
</section>
