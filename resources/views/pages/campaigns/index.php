<?php declare(strict_types=1); $summary=(array)($summary??[]); $campaigns=(array)($campaigns??[]); ?>
<h1>Campaigns</h1><p>Modo solo lectura.</p><p>Total: <?= (int)($summary['total']??0) ?></p>
<?php if($campaigns===[]): ?><p>No hay campañas para el tenant actual.</p><?php else: ?>
<table class="eco-table"><thead><tr><th>ID</th><th>Nombre</th><th>Código</th><th>Estado</th><th>Tipo</th><th>Ventana</th><th></th></tr></thead><tbody>
<?php foreach($campaigns as $row): ?><tr><td><?= (int)($row['id']??0) ?></td><td><?= htmlspecialchars((string)($row['name']??'')) ?></td><td><?= htmlspecialchars((string)($row['code']??'')) ?></td><td><?= htmlspecialchars((string)($row['status']??'')) ?></td><td><?= htmlspecialchars((string)($row['campaign_type']??'')) ?></td><td><?= htmlspecialchars((string)($row['starts_at']??'')) ?> → <?= htmlspecialchars((string)($row['ends_at']??'')) ?></td><td><a href="/campaigns/<?= (int)($row['id']??0) ?>/cockpit">Ver cockpit</a></td></tr><?php endforeach; ?>
</tbody></table><?php endif; ?>
