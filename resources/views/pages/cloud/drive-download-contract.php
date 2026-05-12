<?php
$contract = (array)($contentData['contract'] ?? []);
$checks = (array)($contract['required_checks'] ?? []);
$blockedInputs = (array)($contract['blocked_inputs'] ?? []);
$forbiddenOps = (array)($contract['forbidden_operations'] ?? []);
$auditEvents = (array)($contract['expected_audit'] ?? []);
$modes = (array)($contract['allowed_modes'] ?? []);
?>
<div class="eco-card">
  <h1>Contrato de descarga Drive</h1>
  <p>
    <a class="eco-button btn" href="/cloud/drive">Volver a Ecosistema Drive</a>
    <a class="eco-button btn" href="/cloud/drive/summary">Resumen Drive</a>
    <a class="eco-button btn" href="/cloud/drive/access">Política de acceso Drive</a>
    <a class="eco-button btn" href="/cloud/drive/files">Archivos Drive</a>
  </p>

  <div class="eco-alert eco-alert--warning">No hay descarga real en este PR.</div>

  <h2>Modos permitidos</h2>
  <p>
    <?php foreach ($modes as $mode): ?>
      <span class="eco-badge"><?= e((string)$mode) ?></span>
    <?php endforeach; ?>
  </p>

  <h2>Validaciones futuras requeridas</h2>
  <table class="eco-table">
    <thead><tr><th>#</th><th>Validación</th></tr></thead>
    <tbody>
    <?php foreach ($checks as $index => $check): ?>
      <tr><td><?= e((string)($index + 1)) ?></td><td><?= e((string)$check) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <h2>Entradas bloqueadas</h2>
  <table class="eco-table">
    <thead><tr><th>#</th><th>Entrada</th></tr></thead>
    <tbody>
    <?php foreach ($blockedInputs as $index => $blocked): ?>
      <tr><td><?= e((string)($index + 1)) ?></td><td><?= e((string)$blocked) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <h2>Operaciones prohibidas en este PR</h2>
  <table class="eco-table">
    <thead><tr><th>#</th><th>Operación</th></tr></thead>
    <tbody>
    <?php foreach ($forbiddenOps as $index => $operation): ?>
      <tr><td><?= e((string)($index + 1)) ?></td><td><?= e((string)$operation) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <h2>Auditoría esperada</h2>
  <table class="eco-table">
    <thead><tr><th>Evento</th></tr></thead>
    <tbody>
    <?php foreach ($auditEvents as $event): ?>
      <tr><td><?= e((string)$event) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
