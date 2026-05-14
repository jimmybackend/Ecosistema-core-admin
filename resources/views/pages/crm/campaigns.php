<?php
$summary = (array)($contentData['summary'] ?? []);
$campaigns = (array)($contentData['campaigns'] ?? []);
?>
<section class="stack">
    <h1>Campañas CRM</h1>
    <p>Total: <?= (int)($summary['total'] ?? 0) ?></p>
    <table>
        <thead><tr><th>ID</th><th>Nombre</th><th>Código</th><th>Status</th><th>Landing</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($campaigns as $item): ?>
            <tr>
                <td><?= (int)$item['id'] ?></td>
                <td><?= htmlspecialchars((string)$item['name']) ?></td>
                <td><?= htmlspecialchars((string)$item['code']) ?></td>
                <td><?= htmlspecialchars((string)$item['status']) ?></td>
                <td><?= htmlspecialchars((string)($item['landing_url_preview'] ?? '')) ?></td>
                <td><a href="/crm/campaigns/<?= (int)$item['id'] ?>">Detalle</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
