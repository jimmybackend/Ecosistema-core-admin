<?php
$summary = (array)($contentData['summary'] ?? []);
$leads = (array)($contentData['leads'] ?? []);
?>
<section class="stack">
    <h1>Leads CRM (read-only)</h1>
    <p>Total: <?= (int)($summary['total'] ?? 0) ?></p>
    <table>
        <thead><tr><th>ID</th><th>Empresa</th><th>Contacto</th><th>Email</th><th>Teléfono</th><th>Interés</th><th>Status</th><th>Notas</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($leads as $item): ?>
            <tr>
                <td><?= (int)$item['id'] ?></td>
                <td><?= htmlspecialchars((string)($item['company_name_preview'] ?? '')) ?></td>
                <td><?= !empty($item['contact_name_present']) ? htmlspecialchars((string)($item['contact_name_preview'] ?? '')) : '—' ?></td>
                <td><?= !empty($item['email_present']) ? htmlspecialchars((string)($item['email_preview'] ?? '')) : '—' ?></td>
                <td><?= !empty($item['phone_present']) ? htmlspecialchars((string)($item['phone_preview'] ?? '')) : '—' ?></td>
                <td><?= htmlspecialchars((string)($item['interest_preview'] ?? '')) ?></td>
                <td><?= htmlspecialchars((string)($item['status'] ?? '')) ?></td>
                <td><?= !empty($item['notes_present']) ? htmlspecialchars((string)($item['notes_preview'] ?? '')) : '—' ?></td>
                <td><a href="/crm/leads/<?= (int)$item['id'] ?>">Ver detalle</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
