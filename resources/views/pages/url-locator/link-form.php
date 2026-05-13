<?php $mode=(string)($contentData['mode'] ?? 'create'); $link=(array)($contentData['link'] ?? []); $errors=(array)($contentData['errors'] ?? []); $writeEnabled=(bool)($contentData['writeEnabled'] ?? false); ?>
<div class="eco-card">
<h1><?= $mode==='edit'?'Editar':'Nuevo' ?> short link</h1>
<?php if(!$writeEnabled): ?><div class="eco-alert eco-alert--warning">Write deshabilitado por flags.</div><?php endif; ?>
<?php foreach($errors as $e): ?><div class="eco-alert"><?= e((string)$e) ?></div><?php endforeach; ?>
<form method="post" action="<?= $mode==='edit'?'/url/locator/links/'.(int)($link['id']??0).'/edit':'/url/locator/links' ?>">
<input type="hidden" name="_csrf" value="<?= e((string)($csrfToken ?? '')) ?>" />
<?php foreach(['slug','target_url','title','description','status','smart_type','expires_at','max_clicks','campaign_id','landing_page_id','default_language_code','language_fallback_url','language_query_param','original_url_after_ads','utm_source','utm_medium','utm_campaign','utm_term','utm_content'] as $f): ?>
<label><?= e($f) ?><input name="<?= e($f) ?>" value="<?= e((string)($link[$f] ?? '')) ?>" <?= !$writeEnabled?'disabled':'' ?>></label><br>
<?php endforeach; ?>
<label>language_detection_enabled <input type="checkbox" name="language_detection_enabled" value="1" <?= !empty($link['language_detection_enabled'])?'checked':'' ?> <?= !$writeEnabled?'disabled':'' ?>></label><br>
<button class="eco-button btn" <?= !$writeEnabled?'disabled':'' ?>>Guardar</button>
</form>
<p><a href="/url/locator/links">Volver a listado</a></p>
</div>
