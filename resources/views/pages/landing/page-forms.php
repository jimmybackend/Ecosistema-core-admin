<?php $summary=(array)($contentData['summary']??[]); $forms=(array)($contentData['forms']??[]); $id=(int)($contentData['id']??0); ?>
<section class="eco-card"><h1>Landing Page Forms</h1><p><a href="/landing/pages/<?= $id ?>">Volver al detalle de landing</a></p><p><strong>Aviso:</strong> modo read-only.</p>
<p>Total tenant: <?= (int)($summary['total']??0) ?> | Formularios en página: <?= count($forms) ?></p>
<ul><?php foreach($forms as $form): ?><li>#<?= (int)$form['id'] ?> <?= htmlspecialchars((string)$form['name']) ?> — campos=<?= (int)($form['fields_count']??0) ?> — <a href="/landing/forms/<?= (int)$form['id'] ?>">Ver detalle</a></li><?php endforeach; ?></ul>
</section>
