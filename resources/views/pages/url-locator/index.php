<?php
$summary = (array)($contentData['summary'] ?? []);
$capabilities = (array)($contentData['capabilities'] ?? []);
$errorMessage = $contentData['errorMessage'] ?? null;
?>
<div class="eco-card">
  <h1>URL Locator</h1>
  <div class="eco-alert eco-alert--warning">Módulo en modo read-only: sin creación de links, sin redirecciones públicas y sin tracking de clicks.</div>
  <?php if (is_string($errorMessage) && $errorMessage !== ''): ?><div class="eco-alert"><?= e($errorMessage) ?></div><?php endif; ?>
  <p><a class="eco-button btn" href="/url/locator/links">Ver short links</a></p>
  <table class="eco-table"><tbody>
    <tr><td>Total</td><td><span class="eco-badge"><?= e((string)($summary['total'] ?? 0)) ?></span></td></tr>
    <tr><td>Capacidad links_read</td><td><?= !empty($capabilities['links_read']) ? 'true' : 'false' ?></td></tr>
    <tr><td>Capacidad links_write</td><td><?= !empty($capabilities['links_write']) ? 'true' : 'false' ?></td></tr>
    <tr><td>Capacidad public_redirects</td><td><?= !empty($capabilities['public_redirects']) ? 'true' : 'false' ?></td></tr>
  </tbody></table>
</div>
