<?php
/** @var array<string,mixed>|null $result */
/** @var string|null $errorMessage */
$result = isset($result) && is_array($result) ? $result : null;
$errorMessage = isset($errorMessage) ? (string) $errorMessage : null;
?>
<h1>Attribution Rollup Dry-run</h1>
<p><strong>Ruta:</strong> /attribution/rollups/dry-run</p>
<p><strong>Modo:</strong> Simulación en memoria (sin INSERT/UPDATE/DELETE).</p>

<?php if ($errorMessage !== null && $errorMessage !== ''): ?><div class="eco-alert eco-alert--danger"><?= e($errorMessage) ?></div><?php endif; ?>

<form method="post" action="/attribution/rollups/dry-run" class="eco-form">
  <input type="hidden" name="_csrf" value="<?= e((string)($csrfToken ?? '')) ?>">
  <label>Fecha inicio <input type="date" name="start_date" required value="<?= e((string)($_POST['start_date'] ?? date('Y-m-01'))) ?>"></label>
  <label>Fecha fin <input type="date" name="end_date" required value="<?= e((string)($_POST['end_date'] ?? date('Y-m-d'))) ?>"></label>
  <button type="submit">Simular</button>
</form>

<?php if ($result !== null): ?>
  <h2>Resultado</h2>
  <table class="eco-table">
    <tr><th>enabled</th><td><?= !empty($result['enabled']) ? 'true' : 'false' ?></td></tr>
    <tr><th>allowed</th><td><?= !empty($result['allowed']) ? 'true' : 'false' ?></td></tr>
    <tr><th>db_write</th><td><?= !empty($result['db_write']) ? 'true' : 'false' ?></td></tr>
    <tr><th>blocked_reason</th><td><?= e((string)($result['blocked_reason'] ?? '')) ?></td></tr>
  </table>
  <h3>Métricas</h3>
  <ul>
    <li>Clicks: <?= (int)($result['metrics']['clicks'] ?? 0) ?></li>
    <li>Visits: <?= (int)($result['metrics']['visits'] ?? 0) ?></li>
    <li>Sessions: <?= (int)($result['metrics']['sessions'] ?? 0) ?></li>
    <li>Form submits: <?= (int)($result['metrics']['submissions'] ?? 0) ?></li>
    <li>Attributions: <?= (int)($result['metrics']['attributions'] ?? 0) ?></li>
  </ul>
  <h3>Top campañas</h3>
  <table class="eco-table">
    <tr><th>Campaign</th><th>Attributions</th></tr>
    <?php foreach ((array)($result['by_campaign'] ?? []) as $row): ?>
      <tr><td><?= e((string)($row['campaign_label'] ?? 'Campaign')) ?></td><td><?= (int)($row['attributions_count'] ?? 0) ?></td></tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>


<h2>Generar rollup controlado (escritura protegida)</h2>
<form method="post" action="/attribution/rollups/generate" class="eco-form">
  <input type="hidden" name="_csrf" value="<?= e((string)($csrfToken ?? '')) ?>"> 
  <label>Fecha rollup <input type="date" name="rollup_date" required value="<?= e((string)($_POST['rollup_date'] ?? date('Y-m-d'))) ?>"></label>
  <button type="submit">Generar (controlado)</button>
</form>
