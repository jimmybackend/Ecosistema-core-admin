<?php $dryRun = $contentData['dryRun'] ?? null; $id = (int)($contentData['id'] ?? 0); $errorMessage = $contentData['errorMessage'] ?? null; ?>
<section class="eco-card"> 
  <h1>CRM Followup Task Dry-Run</h1>
  <p><strong>Modo:</strong> simulación segura (sin INSERT en crm_tasks).</p>
  <p><a href="/crm/leads/<?= $id ?>">← Volver al lead</a></p>
  <?php if (is_string($errorMessage) && $errorMessage !== ''): ?><p><?= htmlspecialchars($errorMessage) ?></p><?php endif; ?>
  <form method="post" action="/crm/leads/<?= $id ?>/followup-task-dry-run" class="eco-form"> 
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">
    <label>Assigned user ID <input type="number" name="assigned_user_id" min="1" required></label><br>
    <label>Título <input type="text" name="title" maxlength="255" required></label><br>
    <label>Descripción <textarea name="description" maxlength="2000"></textarea></label><br>
    <label>Due at <input type="datetime-local" name="due_at" required></label><br>
    <label>Prioridad <select name="priority"><option value="low">low</option><option value="medium">medium</option><option value="high">high</option></select></label><br>
    <button type="submit">Simular tarea</button>
    <button type="submit" formaction="/crm/leads/<?= $id ?>/followup-tasks">Crear tarea</button>
  </form>
  <?php if (is_array($dryRun)): ?>
    <h2>Resultado</h2><ul>
      <li>mode: <?= htmlspecialchars((string)($dryRun['mode'] ?? '')) ?></li><li>enabled: <?= !empty($dryRun['enabled']) ? 'true' : 'false' ?></li><li>would_create_task: <?= !empty($dryRun['would_create_task']) ? 'true' : 'false' ?></li><li>db_write: false</li>
    </ul>
  <?php endif; ?>
</section>
