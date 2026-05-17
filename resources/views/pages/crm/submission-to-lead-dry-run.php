<?php
$dryRun = $contentData['dryRun'] ?? null;
$id = (int)($contentData['id'] ?? 0);
$errorMessage = $contentData['errorMessage'] ?? null;

$maskPii = static function (string $field, mixed $value): string {
    $raw = trim((string) $value);
    if ($raw == '') { return '-'; }
    if ($field === 'email') {
        $parts = explode('@', $raw, 2);
        if (count($parts) !== 2) { return '***'; }
        $local = $parts[0]; $domain = $parts[1];
        $localMask = strlen($local) <= 2 ? substr($local, 0, 1) . '*' : substr($local, 0, 2) . str_repeat('*', max(1, strlen($local)-2));
        $domainParts = explode('.', $domain);
        $domainName = $domainParts[0] ?? '';
        $tld = $domainParts[1] ?? '';
        $domainMask = ($domainName === '') ? '***' : substr($domainName, 0, 1) . '***';
        return $domainMask . ($tld !== '' ? '.' . $tld : '') . ' (' . $localMask . '@...)';
    }
    if ($field === 'phone') {
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits === '') { return '***'; }
        return str_repeat('*', max(0, strlen($digits)-2)) . substr($digits, -2);
    }
    return mb_substr($raw, 0, 3) . '***';
};
?>
<section class="eco-card">
    <h1>CRM Submission to Lead Dry-Run</h1>
    <p><strong>Modo:</strong> simulación segura (sin INSERT/UPDATE).</p>
    <p><a href="/landing/submissions/<?= $id ?>">← Volver al submission detail</a></p>

    <?php if (is_string($errorMessage) && $errorMessage !== ''): ?>
        <p><?= htmlspecialchars($errorMessage) ?></p>
    <?php elseif (!is_array($dryRun)): ?>
        <p>No hay datos de simulación para este submission.</p>
    <?php else: ?>
        <ul>
            <li>mode: <?= htmlspecialchars((string)($dryRun['mode'] ?? '')) ?></li>
            <li>would_create_lead: <?= !empty($dryRun['would_create_lead']) ? 'true' : 'false' ?></li>
            <li>would_link_campaign: <?= !empty($dryRun['would_link_campaign']) ? 'true' : 'false' ?></li>
            <li>would_update_submission: false</li>
            <li>db_write: false</li>
            <li>duplicate_candidates_count: <?= (int)($dryRun['duplicate_candidates_count'] ?? 0) ?></li>
            <li>pii_preview_only: <?= !empty($dryRun['pii_preview_only']) ? 'true' : 'false' ?></li>
        </ul>

        <h2>Mapped fields (preview)</h2>
        <?php $mf = (array)($dryRun['mapped_fields'] ?? []); ?>
        <table class="eco-table"><tbody>
            <?php foreach (['contact_name','email','phone','company_name','interest','message'] as $field): ?>
                <tr><th><?= htmlspecialchars($field) ?></th><td><?= htmlspecialchars($maskPii($field, $mf[$field] ?? '')) ?></td></tr>
            <?php endforeach; ?>
        </tbody></table>

        <h2>Missing required fields</h2>
        <?php $missing = (array)($dryRun['missing_required_fields'] ?? []); ?>
        <p><?= $missing === [] ? 'Ninguno' : htmlspecialchars(implode(', ', $missing)) ?></p>

        <h2>Warnings</h2>
        <?php $warnings = (array)($dryRun['warnings'] ?? []); ?>
        <?php if ($warnings === []): ?><p>Sin warnings.</p>
        <?php else: ?><ul><?php foreach($warnings as $w): ?><li><?= htmlspecialchars((string)$w) ?></li><?php endforeach; ?></ul><?php endif; ?>


        <h2>Acción controlada</h2>
        <form method="post" action="/crm/submission-to-lead/<?= $id ?>">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? '')) ?>">
            <label><input type="checkbox" name="force_duplicate" value="1"> Permitir continuar si hay duplicados detectados</label><br>
            <button type="submit">Crear lead real (controlado)</button>
        </form>
    <?php endif; ?>
</section>
