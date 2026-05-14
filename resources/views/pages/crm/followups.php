<?php
$followups = (array)($contentData['followups'] ?? []);
$tasks = (array)($followups['tasks'] ?? []);
$customerFollowups = (array)($followups['followups'] ?? []);
$events = (array)($followups['events'] ?? []);
?>
<section class="stack">
    <h1>CRM Followups (read-only)</h1>
    <p>Tareas: <?= count($tasks) ?> · Followups: <?= count($customerFollowups) ?> · Eventos: <?= count($events) ?></p>
</section>
