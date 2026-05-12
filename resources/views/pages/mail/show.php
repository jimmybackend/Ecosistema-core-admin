<?php $message = is_array($message ?? null) ? $message : null; ?>
<section><h1>Detalle Mail</h1>
<?php if ($message===null): ?><div class="eco-alert" role="alert">Mensaje no encontrado.</div><?php else: ?>
<article class="eco-card">
<p><strong>Subject:</strong> <?= e((string)($message['subject'] ?? '')) ?></p>
<p><strong>From:</strong> <?= e((string)($message['from_address'] ?? '')) ?></p>
<p><strong>To:</strong> <?= e((string)($message['to_addresses'] ?? '')) ?></p>
<?php if (!empty($message['cc_addresses'])): ?><p><strong>CC:</strong> <?= e((string)$message['cc_addresses']) ?></p><?php endif; ?>
<p><strong>Direction:</strong> <span class="eco-badge"><?= e((string)($message['direction'] ?? '')) ?></span></p>
<p><strong>Scope:</strong> <span class="eco-badge"><?= e((string)($message['mail_scope'] ?? '')) ?></span></p>
<p><strong>Body text:</strong><br><?= nl2br(e((string)($message['body_text'] ?? ''))) ?></p>
<p><strong>Body html (escapado):</strong><br><pre><?= e((string)($message['body_html'] ?? '')) ?></pre></p>
<p><strong>created_at:</strong> <?= e((string)($message['created_at'] ?? '')) ?> | <strong>received_at:</strong> <?= e((string)($message['received_at'] ?? '')) ?> | <strong>sent_at:</strong> <?= e((string)($message['sent_at'] ?? '')) ?></p>
<p><a class="eco-button btn" href="/mail/messages/<?= e((string)($message['id'] ?? '0')) ?>/send-preview">Preview envío</a></p>
<div class="eco-alert" role="status">Los adjuntos salientes se habilitarán en un PR posterior.</div>
</article><?php endif; ?></section>

<?php $attachments = is_array($attachments ?? null) ? $attachments : []; ?>
<section class="eco-card" style="margin-top:16px;">
<h2>Adjuntos</h2>
<?php if ($attachments === []): ?><div class="eco-alert" role="alert">Adjuntos: no disponibles todavía en esta instalación.</div><?php else: ?>
<table class="eco-table"><thead><tr><th>Nombre</th><th>Tipo</th><th>Tamaño (bytes)</th><th>Estado</th><th>Fecha</th></tr></thead><tbody>
<?php foreach ($attachments as $attachment): ?><tr><td><?= e((string)($attachment['original_name'] ?? '')) ?></td><td><span class="eco-badge"><?= e((string)($attachment['mime_type'] ?? '')) ?></span></td><td><?= e((string)($attachment['size_bytes'] ?? '')) ?></td><td><span class="eco-badge"><?= e((string)($attachment['status'] ?? '')) ?></span></td><td><?= e((string)($attachment['uploaded_at'] ?? '')) ?></td></tr><?php endforeach; ?>
</tbody></table><?php endif; ?>
</section>
