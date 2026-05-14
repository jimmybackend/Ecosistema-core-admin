<?php
$result = (array) ($contentData['result'] ?? []);
?>
<section class="eco-card">
  <h1>Workflow execution</h1>
  <p>Resultado de ejecución controlada por flags (safe output).</p>
  <table class="eco-table"><tbody>
    <tr><td>Run ID</td><td><?= e((string) ($result['run_id'] ?? 0)) ?></td></tr>
    <tr><td>Status</td><td><?= e((string) ($result['status'] ?? 'canceled')) ?></td></tr>
    <tr><td>Actions executed</td><td><?= e((string) ($result['actions_executed'] ?? 0)) ?></td></tr>
    <tr><td>Actions blocked</td><td><?= e((string) ($result['actions_blocked'] ?? 0)) ?></td></tr>
  </tbody></table>
  <?php if (((int) ($result['run_id'] ?? 0)) > 0): ?>
    <p><a href="/workflow/runs/<?= e((string) ($result['run_id'] ?? 0)) ?>">Ver run detail</a></p>
  <?php endif; ?>
  <h2>Safe logs</h2>
  <ul>
    <?php foreach ((array) ($result['safe_logs'] ?? []) as $log): ?>
      <li>[<?= e((string) ($log['level'] ?? 'info')) ?>] <?= e((string) ($log['message'] ?? '')) ?></li>
    <?php endforeach; ?>
  </ul>
</section>
