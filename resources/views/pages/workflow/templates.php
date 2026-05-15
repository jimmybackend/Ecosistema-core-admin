<?php $templates = (array) (($contentData['templates']['items'] ?? [])); ?>
<section class="eco-card">
  <h1>Workflow Templates</h1>
  <p>Catálogo sugerido en modo <strong>read-only</strong>. No crea reglas ni acciones.</p>
  <?php if ($templates === []): ?>
    <p>No hay plantillas sugeridas disponibles.</p>
  <?php else: ?>
    <table><thead><tr><th>Key</th><th>Nombre</th><th>Trigger</th><th>Acciones</th><th>Detalle</th></tr></thead><tbody>
      <?php foreach ($templates as $item): ?>
      <tr>
        <td><?= e((string) ($item['key'] ?? '')) ?></td>
        <td><?= e((string) ($item['name'] ?? '')) ?></td>
        <td><?= e((string) ($item['trigger_module'] ?? '')) ?> / <?= e((string) ($item['trigger_event'] ?? '')) ?></td>
        <td><?= e((string) ($item['actions_count'] ?? 0)) ?></td>
        <td><a href="/workflow/templates/<?= rawurlencode((string) ($item['key'] ?? '')) ?>">Abrir</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody></table>
  <?php endif; ?>
</section>
