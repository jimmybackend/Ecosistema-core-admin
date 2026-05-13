<?php $summary=(array)($contentData['summary']??[]); $submissions=(array)($contentData['submissions']??[]); $id=(int)($contentData['id']??0); ?>
<section class="eco-card"><h1>Submissions por formulario #<?= $id ?></h1><p><a href="/landing/forms/<?= $id ?>">Volver al formulario</a></p><p><strong>Aviso:</strong> read-only, sin creación de leads CRM ni procesamiento público.</p>
<p>Total tenant: <?= (int)($summary['total']??0) ?></p>
<?php include __DIR__ . '/_submissions-table.php'; ?>
</section>
