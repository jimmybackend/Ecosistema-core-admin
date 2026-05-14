<?php
/** @var array<string,mixed>|null $result */
$result = isset($result) && is_array($result) ? $result : null;
?>
<h1>Attribution Rollup Generate</h1>
<p><strong>Ruta:</strong> /attribution/rollups/generate</p>
<p><strong>Estado:</strong> Controlado por flags, con bloqueo seguro si no hay idempotencia confirmada.</p>

<?php if ($result === null): ?>
  <div class="eco-alert eco-alert--warning">No hay resultado para mostrar.</div>
<?php else: ?>
  <table class="eco-table">
    <tr><th>allowed</th><td><?= !empty($result['allowed']) ? 'true' : 'false' ?></td></tr>
    <tr><th>db_write</th><td><?= !empty($result['db_write']) ? 'true' : 'false' ?></td></tr>
    <tr><th>written</th><td><?= !empty($result['written']) ? 'true' : 'false' ?></td></tr>
    <tr><th>blocked_reason</th><td><?= e((string) ($result['blocked_reason'] ?? '')) ?></td></tr>
    <tr><th>rollup_date</th><td><?= e((string) ($result['rollup_date'] ?? '')) ?></td></tr>
  </table>
  <h3>Metrics preview</h3>
  <ul>
    <?php foreach ((array) ($result['metrics_preview'] ?? []) as $key => $value): ?>
      <li><?= e((string) $key) ?>: <?= (int) $value ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
