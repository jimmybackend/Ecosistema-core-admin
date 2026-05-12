<?php
$message = is_array($message ?? null) ? $message : null;
$availableFiles = is_array($availableFiles ?? null) ? $availableFiles : [];
$attachedFiles = is_array($attachedFiles ?? null) ? $attachedFiles : [];
$statusMessage = is_string($statusMessage ?? null) ? $statusMessage : null;
$errorMessage = is_string($errorMessage ?? null) ? $errorMessage : null;
$attachedIds = [];
foreach ($attachedFiles as $attachedFile) { $attachedIds[] = (int) ($attachedFile['id'] ?? 0); }
?>
<section>
  <h1>Adjuntos de borrador</h1>
  <div class="eco-alert" role="status">No se suben archivos desde Mail en este PR.</div>
  <?php if ($statusMessage): ?><div class="eco-alert" role="status"><?= e($statusMessage) ?></div><?php endif; ?>
  <?php if ($errorMessage): ?><div class="eco-alert" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>

  <?php if ($message === null): ?>
    <article class="eco-card"><div class="eco-alert" role="alert">Borrador no encontrado.</div></article>
  <?php else: ?>
    <article class="eco-card">
      <p><strong>ID:</strong> <?= e((string) ($message['id'] ?? '')) ?></p>
      <p><strong>Asunto:</strong> <?= e((string) ($message['subject'] ?? '')) ?></p>
      <p><strong>From:</strong> <?= e((string) ($message['from_address'] ?? '')) ?></p>
      <p><strong>Estado:</strong> <span class="eco-badge"><?= ((int) ($message['is_draft'] ?? 0) === 1) ? 'borrador' : 'no borrador' ?></span></p>
      <p>
        <a class="eco-button btn" href="/mail/messages/<?= e((string) ($message['id'] ?? '0')) ?>/send-preview">Ver preview de envío</a>
      </p>
    </article>

    <section class="eco-card" style="margin-top:16px;">
      <h2>Adjuntos ya asociados</h2>
      <?php if ($attachedFiles === []): ?>
        <div class="eco-alert" role="status">Sin adjuntos asociados.</div>
      <?php else: ?>
      <table class="eco-table" style="width:100%;"><thead><tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Tamaño</th><th>Estado</th></tr></thead><tbody>
      <?php foreach ($attachedFiles as $file): ?>
        <tr>
          <td><?= e((string) ($file['id'] ?? '')) ?></td>
          <td><?= e((string) ($file['original_name'] ?? '')) ?></td>
          <td><span class="eco-badge"><?= e((string) ($file['mime_type'] ?? '')) ?></span></td>
          <td><?= e((string) ($file['size_bytes'] ?? '')) ?></td>
          <td><span class="eco-badge"><?= e((string) ($file['status'] ?? '')) ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody></table>
      <?php endif; ?>
    </section>

    <section class="eco-card" style="margin-top:16px;">
      <h2>Seleccionar archivos Cloud existentes</h2>
      <form method="post" action="/mail/messages/<?= e((string) ($message['id'] ?? '0')) ?>/attachments">
        <input type="hidden" name="_csrf" value="<?= e((string) $csrfToken) ?>">
        <table class="eco-table" style="width:100%;"><thead><tr><th>Adjuntar</th><th>ID</th><th>Nombre</th><th>Tipo</th><th>Tamaño</th><th>Estado</th></tr></thead><tbody>
        <?php foreach ($availableFiles as $file): ?>
          <?php $fid = (int) ($file['id'] ?? 0); ?>
          <tr>
            <td><input class="eco-form-control" type="checkbox" name="cloud_file_ids[]" value="<?= e((string) $fid) ?>" <?= in_array($fid, $attachedIds, true) ? 'checked' : '' ?>></td>
            <td><?= e((string) $fid) ?></td>
            <td><?= e((string) ($file['original_name'] ?? '')) ?></td>
            <td><span class="eco-badge"><?= e((string) ($file['mime_type'] ?? '')) ?></span></td>
            <td><?= e((string) ($file['size_bytes'] ?? '')) ?></td>
            <td><span class="eco-badge"><?= e((string) ($file['status'] ?? '')) ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody></table>
        <button type="submit" class="eco-button btn">Guardar adjuntos lógicos</button>
      </form>
    </section>
  <?php endif; ?>
</section>
