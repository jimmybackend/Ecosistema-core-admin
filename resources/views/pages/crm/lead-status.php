<?php $id=(int)($contentData['id']??0); $statusData=$contentData['statusData']??[]; ?>
<section class="eco-card">
  <h1>CRM Lead Status</h1>
  <p><a href="/crm/leads/<?= $id ?>">← Volver al lead</a></p>
  <?php if (!is_array($statusData) || empty($statusData['ok'])): ?>
    <p><?= htmlspecialchars((string)($statusData['error'] ?? 'Sin datos.')) ?></p>
  <?php else: ?>
    <ul>
      <li>lead_id: <?= $id ?></li>
      <li>current_status: <?= htmlspecialchars((string)($statusData['current_status'] ?? '')) ?></li>
      <li>write_enabled: <?= !empty($statusData['write_enabled']) ? 'true' : 'false' ?></li>
      <li>pii_preview_only: true</li>
    </ul>
    <form method="post" action="/crm/leads/<?= $id ?>/status" class="eco-form">
      <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">
      <label>Nuevo status
        <select name="status" required>
          <?php foreach ((array)($statusData['allowed_statuses'] ?? []) as $opt): ?>
            <option value="<?= htmlspecialchars((string)$opt) ?>"><?= htmlspecialchars((string)$opt) ?></option>
          <?php endforeach; ?>
        </select>
      </label><br>
      <label>Campaign lead id (opcional) <input type="number" min="1" name="campaign_lead_id"></label><br>
      <label>Temperature (opcional)
        <select name="temperature"><option value="">(sin cambio)</option><option value="cold">cold</option><option value="warm">warm</option><option value="hot">hot</option></select>
      </label><br>
      <label>Score 0-100 (opcional) <input type="number" min="0" max="100" step="0.01" name="score"></label><br>
      <button type="submit">Actualizar status</button>
    </form>
  <?php endif; ?>
</section>
