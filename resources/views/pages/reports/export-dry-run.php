<?php
/** @var array<string,mixed>|null $result */
$result = isset($result) && is_array($result) ? $result : null;
?>
<h1>Reports export dry-run</h1>
<p><strong>Ruta:</strong> /reports/exports/dry-run</p>
<p><strong>Modo:</strong> simulación segura sin crear archivos ni registros en reports_exports.</p>
<form method="post" action="/reports/exports/dry-run" class="eco-form">
  <input type="hidden" name="_csrf" value="<?= e((string)($csrfToken ?? '')) ?>">
  <label>Reporte
    <select name="report_type" required>
      <option value="marketing_funnel">Marketing funnel</option>
      <option value="lead_performance">Lead performance</option>
      <option value="dashboard_inventory">Dashboard inventory</option>
    </select>
  </label>
  <label>Formato
    <select name="format" required>
      <option value="csv">CSV</option>
      <option value="xlsx">XLSX</option>
    </select>
  </label>
  <label>Límite preview <input type="number" min="1" max="50" name="limit" value="10"></label>
  <button type="submit">Simular exportación</button>
</form>

<?php if ($result !== null): ?>
<h2>Resultado</h2>
<p><strong>allowed:</strong> <?= !empty($result['allowed']) ? 'true' : 'false' ?> · <strong>blocked_reason:</strong> <?= e((string)($result['blocked_reason'] ?? '')) ?></p>
<p><strong>Columnas permitidas:</strong> <?= e(implode(', ', (array)($result['allowed_columns'] ?? []))) ?></p>
<table class="eco-table">
  <tr>
    <?php foreach ((array)($result['allowed_columns'] ?? []) as $column): ?>
      <th><?= e((string)$column) ?></th>
    <?php endforeach; ?>
  </tr>
  <?php foreach ((array)($result['rows_preview'] ?? []) as $row): ?>
    <tr>
      <?php foreach ((array)($result['allowed_columns'] ?? []) as $column): ?>
        <td><?= e((string)($row[$column] ?? '')) ?></td>
      <?php endforeach; ?>
    </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
