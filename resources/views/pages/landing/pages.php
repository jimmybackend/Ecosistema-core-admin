<?php
/** @var array<string,mixed> $contentData */
$summary = (array)($contentData['summary'] ?? []);
$pages = (array)($contentData['pages'] ?? []);
?>
<section class="eco-card">
  <h1>Landing Pages</h1>
  <p><strong>Aviso:</strong> panel administrativo en modo read-only.</p>
  <h2>Resumen</h2>
  <p>Total: <?= (int)($summary['total'] ?? 0) ?></p>
  <p>Por status:</p>
  <ul><?php foreach (($summary['by_status'] ?? []) as $row): ?><li><?= htmlspecialchars((string)$row['status']) ?>: <?= (int)$row['total'] ?></li><?php endforeach; ?></ul>
  <p>Por page_type:</p>
  <ul><?php foreach (($summary['by_page_type'] ?? []) as $row): ?><li><?= htmlspecialchars((string)$row['page_type']) ?>: <?= (int)$row['total'] ?></li><?php endforeach; ?></ul>

  <?php if ($pages === []): ?>
    <p>No hay landing pages para este tenant.</p>
  <?php else: ?>
    <table class="eco-table"><thead><tr><th>ID</th><th>Title</th><th>Slug</th><th>Status</th><th>Page type</th><th>Campaign</th><th>Template</th><th>Public URL present</th><th>Public URL exposed</th><th>Published at</th><th>Updated at</th><th>Detalle</th></tr></thead><tbody>
    <?php foreach ($pages as $page): ?>
      <tr>
        <td><?= (int)$page['id'] ?></td>
        <td><?= htmlspecialchars((string)$page['title']) ?></td>
        <td><?= htmlspecialchars((string)$page['slug']) ?></td>
        <td><?= htmlspecialchars((string)$page['status']) ?></td>
        <td><?= htmlspecialchars((string)$page['page_type']) ?></td>
        <td><?= htmlspecialchars((string)($page['campaign_name'] ?? '')) ?></td>
        <td><?= htmlspecialchars((string)($page['template_name'] ?? '')) ?></td>
        <td><?= !empty($page['public_url_present']) ? 'true' : 'false' ?></td>
        <td>false</td>
        <td><?= htmlspecialchars((string)($page['published_at'] ?? '')) ?></td>
        <td><?= htmlspecialchars((string)($page['updated_at'] ?? '')) ?></td>
        <td><a href="/landing/pages/<?= (int)$page['id'] ?>">Ver</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table>
  <?php endif; ?>
</section>
