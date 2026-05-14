<?php
$capabilities = (array)($contentData['capabilities'] ?? []);
?>
<section class="stack">
    <h1>CRM · Campañas (read-only)</h1>
    <p>Módulo CRM en modo sólo lectura.</p>
    <ul>
        <?php foreach ($capabilities as $key => $value): ?>
            <li><strong><?= htmlspecialchars((string)$key) ?>:</strong> <?= $value ? 'true' : 'false' ?></li>
        <?php endforeach; ?>
    </ul>
    <p><a href="/crm/campaigns">Ver campañas</a></p>
    <p><a href="/crm/leads">Ver leads</a></p>
    <p><a href="/crm/followups">Ver followups</a></p>
</section>
