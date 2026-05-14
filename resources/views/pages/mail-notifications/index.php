<?php
/** @var array<string,mixed> $contentData */
$capabilities = (array) ($contentData['capabilities'] ?? []);
?>
<section class="eco-card">
  <h1>Mail Notifications</h1>
  <p><strong>Modo:</strong> read-only. No se realizan envíos ni escrituras en base de datos.</p>
  <ul>
    <li>notification_templates_read: <?= !empty($capabilities['notification_templates_read']) ? 'true' : 'false' ?></li>
    <li>url_message_templates_read: <?= !empty($capabilities['url_message_templates_read']) ? 'true' : 'false' ?></li>
    <li>queue_read: <?= !empty($capabilities['queue_read']) ? 'true' : 'false' ?></li>
    <li>db_writes: <?= !empty($capabilities['db_writes']) ? 'true' : 'false' ?></li>
  </ul>
  <p><a href="/mail-notifications/templates">Ver plantillas de notificación</a></p>
  <p><a href="/mail-notifications/url-message-templates">Ver URL message templates</a></p>
  <p><a href="/mail-notifications/queue">Ver cola de notificaciones</a></p>
  <p><a href="/mail-notifications/send-dry-run">Simular envío de notificación (dry-run)</a></p>
</section>
