<?php
$leadId = (int)($contentData['leadId'] ?? 0);
$followups = (array)($contentData['followups'] ?? []);
$tasks = (array)($followups['tasks'] ?? []);
$customerFollowups = (array)($followups['followups'] ?? []);
$events = (array)($followups['events'] ?? []);
$errorMessage = $contentData['errorMessage'] ?? null;
?>
<section class="stack">
    <h1>Lead #<?= $leadId ?> · Followups (read-only)</h1>
    <?php if (is_string($errorMessage) && $errorMessage !== ''): ?><p><?= htmlspecialchars($errorMessage) ?></p><?php endif; ?>
    <p>Tareas: <?= count($tasks) ?> · Followups: <?= count($customerFollowups) ?> · Eventos: <?= count($events) ?></p>
</section>
