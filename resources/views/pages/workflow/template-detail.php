<?php $template = (array) ($contentData['template']['template'] ?? []); ?>
<section class="eco-card">
  <h1>Workflow Template · <?= e((string) ($template['name'] ?? '')) ?></h1>
  <p><a href="/workflow/templates">← Volver a plantillas</a></p>
  <p>Modo <strong>read-only</strong>. Esta vista no escribe en base de datos.</p>
  <ul>
    <li><strong>Key:</strong> <?= e((string) ($template['key'] ?? '')) ?></li>
    <li><strong>Trigger:</strong> <?= e((string) ($template['trigger_module'] ?? '')) ?> / <?= e((string) ($template['trigger_event'] ?? '')) ?></li>
    <li><strong>Acciones sugeridas:</strong> <?= e(implode(', ', (array) ($template['actions'] ?? []))) ?></li>
    <li><strong>Descripción:</strong> <?= e((string) ($template['description_preview'] ?? '')) ?></li>
    <li><strong>JSON sensible expuesto:</strong> No</li>
  </ul>
  <p><a href="/workflow/templates/<?= rawurlencode((string) ($template['key'] ?? '')) ?>/install-dry-run">Simular instalación (dry-run)</a></p>
</section>
