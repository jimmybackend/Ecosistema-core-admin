<?php $result = is_array($result ?? null) ? $result : null; ?>
<section>
    <h1>Reports Export Controlado</h1>
    <p>Flujo seguro: validación por tenant de sesión, formato e inclusión de PII por flags.</p>

    <form method="post" action="/reports/exports">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <label>Tipo de reporte
            <select name="report_type">
                <option value="dashboard_inventory">dashboard_inventory</option>
            </select>
        </label><br>
        <label>Source ID <input type="number" min="1" name="source_id" required></label><br>
        <label>Formato
            <select name="format">
                <option value="csv">csv</option>
                <option value="xlsx">xlsx</option>
            </select>
        </label><br>
        <label><input type="checkbox" name="confirm_pii" value="1"> Confirmo exportar con PII (si la política lo permite)</label><br>
        <button type="submit">Solicitar export</button>
    </form>

    <?php if ($result !== null): ?>
        <h2>Resultado</h2>
        <ul>
            <li>ok: <?= !empty($result['ok']) ? 'true' : 'false' ?></li>
            <li>allowed: <?= !empty($result['allowed']) ? 'true' : 'false' ?></li>
            <li>status: <?= htmlspecialchars((string) ($result['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
            <li>blocked_reason: <?= htmlspecialchars((string) ($result['blocked_reason'] ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
            <li>export_id: <?= htmlspecialchars((string) ($result['export_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
            <li>pii_included: <?= !empty($result['pii_included']) ? 'true' : 'false' ?></li>
        </ul>
    <?php endif; ?>
</section>
