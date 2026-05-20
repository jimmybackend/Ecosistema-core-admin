<?php
$message = is_array($message ?? null) ? $message : null;
$attachments = is_array($attachments ?? null) ? $attachments : [];
$sanitizeHtml = static function (string $html): string {
  $clean = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? '';
  $clean = preg_replace('/<iframe\b[^>]*>.*?<\/iframe>/is', '', $clean) ?? '';
  $clean = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? '';
  $clean = preg_replace('/(href|src)\s*=\s*(["\'])\s*javascript:[^\2]*\2/i', '$1="#"', $clean) ?? '';
  $clean = preg_replace('/<img\b[^>]*>/i', '<em>[imagen remota bloqueada]</em>', $clean) ?? '';
  return $clean;
};
$decode = static function (mixed $raw): string { $a = json_decode((string)$raw, true); return is_array($a) ? implode(', ', $a) : ''; };
?>
<section><h1>Detalle Mail</h1><p><a class="eco-button btn" href="/mail">Volver</a></p>
<?php if ($message===null): ?><div class="eco-alert" role="alert">Mensaje no encontrado.</div><?php else: ?>
<article class="eco-card"><p><strong>Subject:</strong> <?= e((string)($message['subject'] ?? '')) ?></p><p><strong>From:</strong> <?= e((string)($message['from_address'] ?? '')) ?></p><p><strong>To:</strong> <?= e($decode($message['to_addresses'] ?? '')) ?></p><p><strong>CC:</strong> <?= e($decode($message['cc_addresses'] ?? '')) ?></p><p><strong>Fechas:</strong> rcv <?= e((string)($message['received_at'] ?? '')) ?> | sent <?= e((string)($message['sent_at'] ?? '')) ?></p>
<?php if (trim((string)($message['body_html'] ?? ''))!==''): ?><div><?= $sanitizeHtml((string)$message['body_html']) ?></div><?php else: ?><pre><?= e((string)($message['body_text'] ?? '')) ?></pre><?php endif; ?>
</article><?php endif; ?>
<section class="eco-card"><h2>Adjuntos pendientes</h2>
<?php if ($attachments===[]): ?><p>Sin adjuntos pendientes.</p><?php else: ?><table class="eco-table"><thead><tr><th>Archivo</th><th>MIME</th><th>Tamaño</th><th>Estado</th><th>Fecha</th></tr></thead><tbody><?php foreach($attachments as $a): ?><tr><td><?= e((string)($a['original_filename'] ?? '')) ?></td><td><?= e((string)($a['mime_type'] ?? '')) ?></td><td><?= e((string)($a['size_bytes'] ?? '')) ?></td><td><?= e((string)($a['import_status'] ?? '')) ?><?php if (($a['import_status'] ?? '')!=='imported'): ?> · Adjunto pendiente de importar a Cloud<?php endif; ?></td><td><?= e((string)($a['created_at'] ?? '')) ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?>
</section></section>
