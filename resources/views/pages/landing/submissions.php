<?php $summary=(array)($contentData['summary']??[]); $submissions=(array)($contentData['submissions']??[]); ?>
<section class="eco-card"><h1>Landing Submissions</h1><p><strong>Aviso:</strong> modo read-only con protección de privacidad/PII y adjuntos internos ocultos.</p>
<p>Total: <?= (int)($summary['total']??0) ?> | spam scored: <?= (int)(($summary['spam_score']['scored']??0)) ?> | spam high: <?= (int)(($summary['spam_score']['high']??0)) ?></p>
<?php include __DIR__ . '/_submissions-table.php'; ?>
</section>
