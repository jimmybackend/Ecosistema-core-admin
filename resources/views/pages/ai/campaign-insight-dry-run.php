<?php
declare(strict_types=1);
/** @var array<string,mixed>|null $result */
/** @var int $id */
/** @var string|null $errorMessage */
?>
<section class="stack">
    <h1>AI Campaign Insight Dry-Run</h1>
    <p>Preparación segura de contexto/métricas de campaña sin llamadas IA externas y sin escrituras en DB.</p>
    <p><strong>Campaign ID:</strong> <?= (int) ($id ?? 0) ?></p>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars((string) $errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (is_array($result)): ?>
        <ul>
            <li><strong>ok:</strong> <?= !empty($result['ok']) ? 'true' : 'false' ?></li>
            <li><strong>allowed:</strong> <?= !empty($result['allowed']) ? 'true' : 'false' ?></li>
            <li><strong>blocked_reason:</strong> <?= htmlspecialchars((string) ($result['blocked_reason'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></li>
            <li><strong>external_ai_called:</strong> <?= !empty($result['external_ai_called']) ? 'true' : 'false' ?></li>
            <li><strong>proposal_persisted:</strong> <?= !empty($result['proposal_persisted']) ? 'true' : 'false' ?></li>
        </ul>

        <?php $context = is_array($result['context'] ?? null) ? $result['context'] : []; ?>
        <?php $campaign = is_array($context['campaign'] ?? null) ? $context['campaign'] : []; ?>
        <?php $metrics = is_array($context['metrics'] ?? null) ? $context['metrics'] : []; ?>
        <h2>Campaña sanitizada</h2>
        <ul>
            <li>ID: <?= (int) ($campaign['id'] ?? 0) ?></li>
            <li>Nombre (preview): <?= htmlspecialchars((string) ($campaign['name_preview'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></li>
            <li>Código (preview): <?= htmlspecialchars((string) ($campaign['code_preview'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></li>
            <li>Status: <?= htmlspecialchars((string) ($campaign['status'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></li>
            <li>Tiene presupuesto: <?= !empty($campaign['has_budget']) ? 'sí' : 'no' ?></li>
        </ul>

        <h2>Métricas agregadas</h2>
        <ul>
            <li>Leads asociados: <?= (int) ($metrics['lead_count'] ?? 0) ?></li>
            <li>Eventos de servicio: <?= (int) ($metrics['service_event_count'] ?? 0) ?></li>
            <li>Días de rollup: <?= (int) ($metrics['rollup_days_count'] ?? 0) ?></li>
        </ul>
    <?php else: ?>
        <p>Ejecuta la ruta con sesión y permisos para ver el contexto dry-run.</p>
    <?php endif; ?>
</section>
