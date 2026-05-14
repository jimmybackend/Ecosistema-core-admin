<?php $result=$result??[]; $id=(int)($id??0); ?>
<section class="eco-card"><h1>Landing Form Submit Dry-run #<?= $id ?></h1><p><a href="/landing/forms/<?= $id ?>">Volver al formulario</a></p>
<p><strong>Modo:</strong> dry-run (sin INSERT/UPDATE/DELETE y sin leads CRM).</p>
<?php if (!empty($result['errors'])): ?><div class="eco-alert eco-alert--error"><ul><?php foreach($result['errors'] as $k=>$v): ?><li><?= htmlspecialchars((string)$k) ?>: <?= htmlspecialchars((string)$v) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if (!empty($result['fields'])): ?><h2>Campos</h2><ul><?php foreach($result['fields'] as $field): ?><li><?= htmlspecialchars((string)$field['field_key']) ?> (<?= htmlspecialchars((string)$field['field_type']) ?>) <?= !empty($field['is_required'])?'*':'' ?> max=<?= (int)$field['max_length'] ?></li><?php endforeach; ?></ul><?php endif; ?>
<form method="post" action="/landing/forms/<?= $id ?>/submit-dry-run">
<input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken??'')) ?>">
<?php foreach(($result['fields']??[]) as $field): ?><label><?= htmlspecialchars((string)$field['label']) ?> <input type="text" name="<?= htmlspecialchars((string)$field['field_key']) ?>"></label><br><?php endforeach; ?>
<button type="submit">Simular envío</button></form>
<?php if (array_key_exists('valid',$result)): ?><h2>Resultado</h2><p>Valid: <?= !empty($result['valid'])?'true':'false' ?> | spam: <?= !empty($result['spam']['is_spam'])?'true':'false' ?> (score <?= (int)($result['spam']['score']??0) ?>)</p><?php endif; ?>
<?php if (!empty($result['would_store'])): ?><h3>Qué guardaría (preview)</h3><ul><?php foreach($result['would_store'] as $key=>$row): ?><li><?= htmlspecialchars((string)$key) ?> → <?= htmlspecialchars((string)($row['value_preview']??'')) ?></li><?php endforeach; ?></ul><?php endif; ?>
</section>
