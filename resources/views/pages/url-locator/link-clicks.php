<?php declare(strict_types=1); ?>
<section class="eco-card">
  <h1>URL Locator — Clicks por link #<?= e((string)($id ?? '')) ?> (read-only)</h1>
  <p><a class="eco-button btn" href="/url/locator/links/<?= e((string)($id ?? '0')) ?>">Volver al detalle</a></p>
  <p><strong>Privacidad:</strong> no se exponen IP, visitor_uuid, coordenadas ni user_agent completos.</p>
  <?php if (!empty($errorMessage)): ?><p class="eco-alert error"><?= e((string)$errorMessage) ?></p><?php endif; ?>
  <p>Total clicks link: <strong><?= e((string)($summary['total'] ?? 0)) ?></strong></p>
</section>
<section class="eco-card"><h2>Resumen por dispositivo</h2><ul><?php foreach (($summary['by_device_type'] ?? []) as $r): ?><li><?= e((string)($r['value'] ?? 'unknown')) ?>: <?= e((string)($r['total'] ?? 0)) ?></li><?php endforeach; ?></ul></section>
<section class="eco-card"><h2>Resumen por idioma detectado</h2><ul><?php foreach (($summary['by_detected_language'] ?? []) as $r): ?><li><?= e((string)($r['value'] ?? 'unknown')) ?>: <?= e((string)($r['total'] ?? 0)) ?></li><?php endforeach; ?></ul></section>
<section class="eco-card"><h2>Resumen por país</h2><ul><?php foreach (($summary['by_country'] ?? []) as $r): ?><li><?= e((string)($r['value'] ?? 'unknown')) ?>: <?= e((string)($r['total'] ?? 0)) ?></li><?php endforeach; ?></ul></section>
<section class="eco-card">
  <h2>Clicks del short link</h2>
  <?php if (empty($clicks)): ?><p>Sin clicks registrados para este short link.</p><?php else: ?>
  <div style="overflow:auto"><table class="eco-table"><thead><tr><th>clicked_at</th><th>slug</th><th>detected</th><th>selected</th><th>geo</th><th>device</th><th>browser</th><th>os</th><th>ip</th><th>ua</th><th>referer</th><th>clicked_url</th></tr></thead><tbody>
  <?php foreach ($clicks as $c): ?><tr><td><?= e((string)($c['clicked_at'] ?? '')) ?></td><td><?= e((string)($c['short_link_slug'] ?? '')) ?></td><td><?= e((string)($c['detected_language'] ?? '')) ?></td><td><?= e((string)($c['selected_language'] ?? '')) ?></td><td><?= e(trim((string)($c['country'] ?? '') . '/' . (string)($c['region'] ?? '') . '/' . (string)($c['city'] ?? ''), '/')) ?></td><td><?= e((string)($c['device_type'] ?? '')) ?></td><td><?= e((string)($c['browser_name'] ?? '')) ?></td><td><?= e((string)($c['os_name'] ?? '')) ?></td><td><?= !empty($c['ip_address_present']) ? e((string)($c['ip_address_preview'] ?? 'masked')) : 'false' ?></td><td><?= e((string)($c['user_agent_preview'] ?? '')) ?></td><td><?= e((string)($c['referer_preview'] ?? '')) ?></td><td><?= e((string)($c['clicked_url_preview'] ?? '')) ?></td></tr><?php endforeach; ?>
  </tbody></table></div><?php endif; ?>
</section>
