<?php $summary=(array)($contentData['summary']??[]); $submissions=(array)($contentData['submissions']??[]); $id=(int)($contentData['id']??0); ?>
<section class="eco-card"><h1>Submissions por landing page #<?= $id ?></h1><p><a href="/landing/pages/<?= $id ?>">Volver a landing page</a></p><p><strong>Aviso:</strong> read-only, sin descargas de adjuntos.</p>
<p>Total tenant: <?= (int)($summary['total']??0) ?></p>
<?php include __DIR__ . '/_submissions-table.php'; ?>
</section>
