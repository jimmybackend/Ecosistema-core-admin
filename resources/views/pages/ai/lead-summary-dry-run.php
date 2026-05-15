<?php
declare(strict_types=1);
/** @var array<string,mixed>|null $result */
/** @var int $id */
/** @var string|null $errorMessage */
?>
<section class="stack">
    <h1>AI Lead Summary Dry-Run</h1>
    <p>Ruta segura para preparar contexto sanitizado de lead sin llamar proveedor IA y sin escrituras DB.</p>
    <p><strong>Lead ID:</strong> <?= (int) ($id ?? 0) ?></p>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars((string) $errorMessage, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (is_array($result)): ?>
        <div class="card">
            <h2>Resultado</h2>
            <ul>
                <li><strong>ok:</strong> <?= !empty($result['ok']) ? 'true' : 'false' ?></li>
                <li><strong>allowed:</strong> <?= !empty($result['allowed']) ? 'true' : 'false' ?></li>
                <li><strong>blocked_reason:</strong> <?= htmlspecialchars((string) ($result['blocked_reason'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></li>
                <li><strong>mode:</strong> <?= htmlspecialchars((string) ($result['mode'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></li>
                <li><strong>external_ai_called:</strong> <?= !empty($result['external_ai_called']) ? 'true' : 'false' ?></li>
                <li><strong>proposal_persisted:</strong> <?= !empty($result['proposal_persisted']) ? 'true' : 'false' ?></li>
            </ul>
        </div>

        <?php $context = is_array($result['context'] ?? null) ? $result['context'] : []; ?>
        <?php $lead = is_array($context['lead'] ?? null) ? $context['lead'] : []; ?>
        <div class="card">
            <h2>Lead sanitizado</h2>
            <ul>
                <li>ID: <?= (int) ($lead['id'] ?? 0) ?></li>
                <li>Empresa (preview): <?= htmlspecialchars((string) ($lead['company_name_preview'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></li>
                <li>Contacto presente: <?= !empty($lead['contact_name_present']) ? 'sí' : 'no' ?></li>
                <li>Email preview: <?= htmlspecialchars((string) ($lead['email_preview'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></li>
                <li>Phone preview: <?= htmlspecialchars((string) ($lead['phone_preview'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></li>
                <li>Interés (preview): <?= htmlspecialchars((string) ($lead['interest_preview'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></li>
                <li>Status: <?= htmlspecialchars((string) ($lead['status'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></li>
            </ul>
        </div>
    <?php else: ?>
        <p>Ejecuta la ruta con sesión y permisos para ver contexto dry-run.</p>
    <?php endif; ?>
</section>
