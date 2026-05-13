<?php
/** @var array<string,mixed> $contentData */
$capabilities = (array)($contentData['capabilities'] ?? []);
?>
<section class="eco-card">
  <h1>Landing Pages</h1>
  <p><strong>Modo:</strong> read-only. No se realizan escrituras en DB, publicación pública ni tracking.</p>
  <ul>
    <li>pages_read: <?= !empty($capabilities['pages_read']) ? 'true' : 'false' ?></li>
    <li>page_detail_read: <?= !empty($capabilities['page_detail_read']) ? 'true' : 'false' ?></li>
    <li>pages_write: <?= !empty($capabilities['pages_write']) ? 'true' : 'false' ?></li>
    <li>public_render: <?= !empty($capabilities['public_render']) ? 'true' : 'false' ?></li>
  </ul>
  <p><a href="/landing/pages">Ver listado de páginas</a></p>
</section>
