<section class="space-y-4">
  <h1>Workflow Template Install</h1>
  <p><a href="/workflow/templates/<?= rawurlencode((string) (($result['template']['key'] ?? ''))) ?>">← Volver a plantilla</a></p>
  <?php if (!empty($result['installed'])): ?>
    <p><strong>Estado:</strong> instalación completada.</p>
    <ul>
      <li>Rule ID: <?= (int) ($result['rule_id'] ?? 0) ?></li>
      <li>Actions creadas: <?= (int) ($result['actions_created'] ?? 0) ?></li>
      <li>Regla activa: no (requiere activación manual).</li>
    </ul>
  <?php else: ?>
    <p><strong>Estado:</strong> bloqueado.</p>
  <?php endif; ?>
  <h2>Señales de seguridad</h2>
  <ul>
    <li>DB write: <?= !empty($result['db_write']) ? 'sí' : 'no' ?></li>
    <li>Tenant desde sesión: <?= !empty($result['tenant_from_session']) ? 'sí' : 'no' ?></li>
    <li>config_json expuesto: no</li>
    <li>conditions_json expuesto: no</li>
  </ul>
</section>
