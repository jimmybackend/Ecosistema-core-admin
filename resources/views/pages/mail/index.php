<?php
$messages = is_array($messages ?? null) ? $messages : [];
$statusMessage = is_string($statusMessage ?? null) ? $statusMessage : null;
$errorMessage = is_string($errorMessage ?? null) ? $errorMessage : null;
$summary = static function (mixed $json): string { $arr = json_decode((string)$json, true); return is_array($arr) ? implode(', ', array_slice($arr, 0, 2)) : (string)$json; };
?>
<section>
  <h1>Mail</h1>
  <p>Listado mínimo de mensajes por tenant/usuario autenticado. La autorización fina queda para PR posterior.</p>
  <?php if ($statusMessage): ?><div class="eco-alert" role="status"><?= e($statusMessage) ?></div><?php endif; ?>
  <?php if ($errorMessage): ?><div class="eco-alert" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>
  <article class="eco-card"><div style="margin-bottom:.75rem;"><a class="eco-button btn" href="/mail/compose">Nuevo borrador</a> <a class="eco-button btn" href="/mail/settings">Configuración SMTP</a></div>
  <table class="eco-table" style="width:100%"><thead><tr><th>ID</th><th>Dir</th><th>From</th><th>To</th><th>Subject</th><th>Flags</th><th>Fechas</th><th>Acciones</th></tr></thead><tbody>
  <?php if ($messages===[]): ?><tr><td colspan="8">No hay mensajes para mostrar.</td></tr><?php else: foreach($messages as $m): ?><tr>
    <td><?= e((string)$m['id']) ?></td><td><span class="eco-badge"><?= e((string)$m['direction']) ?></span></td><td><?= e((string)$m['from_address']) ?></td><td><?= e($summary($m['to_addresses'] ?? '')) ?></td><td><?= e((string)($m['subject'] ?? '')) ?></td>
    <td><span class="eco-badge">R:<?= e((string)$m['is_read']) ?></span> <span class="eco-badge">S:<?= e((string)$m['is_starred']) ?></span> <span class="eco-badge">D:<?= e((string)$m['is_draft']) ?></span> <span class="eco-badge">A:<?= e((string)$m['has_attachments']) ?></span></td>
    <td><?= e((string)($m['received_at'] ?? '')) ?><br><?= e((string)($m['sent_at'] ?? '')) ?><br><?= e((string)($m['created_at'] ?? '')) ?></td>
    <td><a class="eco-button btn" href="/mail/messages/<?= e((string)$m['id']) ?>">Ver</a>
    <form method="post" action="/mail/messages/<?= e((string)$m['id']) ?>/read" style="display:inline-block;"><input type="hidden" name="_csrf" value="<?= e((string)$csrfToken) ?>"><button class="eco-button btn" type="submit">Read</button></form>
    <form method="post" action="/mail/messages/<?= e((string)$m['id']) ?>/star" style="display:inline-block;"><input type="hidden" name="_csrf" value="<?= e((string)$csrfToken) ?>"><button class="eco-button btn" type="submit">Star</button></form>
    <form method="post" action="/mail/messages/<?= e((string)$m['id']) ?>/trash" style="display:inline-block;"><input type="hidden" name="_csrf" value="<?= e((string)$csrfToken) ?>"><button class="eco-button btn" type="submit">Trash</button></form></td>
  </tr><?php endforeach; endif; ?></tbody></table></article>
</section>
