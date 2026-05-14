<?php $preview = $contentData['preview'] ?? null; $errorMessage = $contentData['errorMessage'] ?? null; $id = (int)($contentData['id'] ?? 0); ?>
<section class="eco-card">
    <h1>Message Preview Dry-Run</h1>
    <p><strong>Modo:</strong> simulación segura, sin envío SMTP y sin escrituras en base de datos.</p>
    <p><a href="/mail-notifications">← Volver a Mail Notifications</a></p>
    <?php if (is_string($errorMessage) && $errorMessage !== ''): ?>
        <p><?= htmlspecialchars($errorMessage) ?></p>
    <?php elseif (!is_array($preview)): ?>
        <p>No hay información disponible para generar preview.</p>
    <?php else: ?>
        <ul>
            <li>mode: <?= htmlspecialchars((string)($preview['mode'] ?? '')) ?></li>
            <li>preview_generated: <?= !empty($preview['preview_generated']) ? 'true' : 'false' ?></li>
            <li>send_executed: false</li>
            <li>queue_created: false</li>
            <li>smtp_connection: false</li>
        </ul>
        <h2>Subject preview</h2>
        <p><?= htmlspecialchars((string)($preview['subject_preview'] ?? '')) ?></p>
        <h2>Body preview</h2>
        <pre><?= htmlspecialchars((string)($preview['body_preview'] ?? '')) ?></pre>
        <h2>Variables permitidas</h2>
        <?php $vars = (array)($preview['variables_used'] ?? []); ?>
        <?php if ($vars === []): ?><p>Sin variables aplicadas.</p>
        <?php else: ?><ul><?php foreach($vars as $k => $v): ?><li><?= htmlspecialchars((string)$k) ?> = <?= htmlspecialchars((string)$v) ?></li><?php endforeach; ?></ul><?php endif; ?>
        <h2>Warnings</h2>
        <?php $warnings = (array)($preview['warnings'] ?? []); ?>
        <?php if ($warnings === []): ?><p>Sin warnings.</p>
        <?php else: ?><ul><?php foreach($warnings as $w): ?><li><?= htmlspecialchars((string)$w) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php endif; ?>

    <h2>Simular variables</h2>
    <form method="post">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">
        <label>variables[nombre]</label><br>
        <input type="text" name="variables[nombre]" value=""><br>
        <label>variables[empresa]</label><br>
        <input type="text" name="variables[empresa]" value=""><br><br>
        <button type="submit">Generar preview dry-run</button>
    </form>
</section>
