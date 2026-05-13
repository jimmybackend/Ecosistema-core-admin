<?php
$link = (array)($contentData['link'] ?? []);
$errorMessage = $contentData['errorMessage'] ?? null;
?>
<div class="eco-card">
  <h1>Detalle de short link</h1>
  <div class="eco-alert eco-alert--warning">Modo read-only. Sin escrituras, sin redirección y sin tracking.</div>
  <?php if (is_string($errorMessage) && $errorMessage !== ''): ?><div class="eco-alert"><?= e($errorMessage) ?></div><?php endif; ?>
  <p><a class="eco-button btn" href="/url/locator/links">Volver al listado</a></p>
  <h2>Metadata segura</h2>
  <ul>
    <li>ID: <?= e((string)($link['id'] ?? '')) ?></li><li>Slug: <?= e((string)($link['slug'] ?? '')) ?></li><li>Title: <?= e((string)($link['title'] ?? '')) ?></li><li>Status: <?= e((string)($link['status'] ?? '')) ?></li><li>Smart type: <?= e((string)($link['smart_type'] ?? '')) ?></li><li>Campaign: <?= e((string)($link['campaign_name'] ?? '')) ?></li><li>Landing page: <?= e((string)($link['landing_page_title'] ?? '')) ?></li><li>Created by: <?= e((string)($link['created_by_label'] ?? '')) ?></li><li>target_url_present: <?= !empty($link['target_url_present'])?'true':'false' ?></li><li>target_url_preview: <?= e((string)($link['target_url_preview'] ?? '')) ?></li><li>target_url_exposed: false</li><li>original_url_after_ads_present: <?= !empty($link['original_url_after_ads_present'])?'true':'false' ?></li><li>original_url_after_ads_preview: <?= e((string)($link['original_url_after_ads_preview'] ?? '')) ?></li><li>original_url_after_ads_exposed: false</li><li>language_fallback_url_present: <?= !empty($link['language_fallback_url_present'])?'true':'false' ?></li><li>language_fallback_url_exposed: false</li><li>access_token_hash_present: <?= !empty($link['access_token_hash_present'])?'true':'false' ?></li><li>access_token_hash_exposed: false</li><li>utm_present: <?= !empty($link['utm_present'])?'true':'false' ?></li><li>utm_preview_safe: <?= e((string)($link['utm_preview_safe'] ?? 'none')) ?></li>
  </ul>

  <h2>Idiomas</h2>
  <table class="eco-table"><thead><tr><th>language_code</th><th>target_url_present</th><th>target_url_exposed</th><th>priority</th><th>is_default_for_language</th><th>is_active</th><th>click_count</th></tr></thead><tbody><?php foreach ((array)($link['languages'] ?? []) as $row): ?><tr><td><?= e((string)($row['language_code'] ?? '')) ?></td><td><?= !empty($row['target_url_present'])?'true':'false' ?></td><td>false</td><td><?= e((string)($row['priority'] ?? '')) ?></td><td><?= !empty($row['is_default_for_language'])?'true':'false' ?></td><td><?= !empty($row['is_active'])?'true':'false' ?></td><td><?= e((string)($row['click_count'] ?? '0')) ?></td></tr><?php endforeach; ?></tbody></table>

  <h2>Smart settings</h2>
  <?php $smart=(array)($link['smart_settings'] ?? []); ?>
  <ul><li>smart_type: <?= e((string)($smart['smart_type'] ?? '')) ?></li><li>show_access_counter: <?= !empty($smart['show_access_counter'])?'true':'false' ?></li><li>track_location: <?= !empty($smart['track_location'])?'true':'false' ?></li><li>track_attachments: <?= !empty($smart['track_attachments'])?'true':'false' ?></li><li>track_final_click: <?= !empty($smart['track_final_click'])?'true':'false' ?></li><li>allow_indexing: <?= !empty($smart['allow_indexing'])?'true':'false' ?></li><li>require_consent: <?= !empty($smart['require_consent'])?'true':'false' ?></li><li>custom_css_present: <?= !empty($smart['custom_css_present'])?'true':'false' ?></li><li>custom_css_exposed: false</li><li>custom_js_present: <?= !empty($smart['custom_js_present'])?'true':'false' ?></li><li>custom_js_exposed: false</li></ul>

  <h2>Message templates (resumen)</h2>
  <table class="eco-table"><thead><tr><th>id</th><th>template_name</th><th>language_code</th><th>status</th><th>view_count</th><th>body_html_present</th><th>body_html_exposed</th></tr></thead><tbody><?php foreach ((array)($link['message_templates_summary'] ?? []) as $row): ?><tr><td><?= e((string)($row['id'] ?? '')) ?></td><td><?= e((string)($row['template_name'] ?? '')) ?></td><td><?= e((string)($row['language_code'] ?? '')) ?></td><td><?= e((string)($row['status'] ?? '')) ?></td><td><?= e((string)($row['view_count'] ?? '0')) ?></td><td><?= !empty($row['body_html_present'])?'true':'false' ?></td><td>false</td></tr><?php endforeach; ?></tbody></table>

  <h2>Ad interstitials (resumen)</h2>
  <table class="eco-table"><thead><tr><th>id</th><th>title</th><th>ad_type</th><th>status</th><th>impression_count</th><th>click_count</th><th>media_s3_key_present</th><th>media_s3_key_exposed</th><th>ad_html_present</th><th>ad_html_exposed</th></tr></thead><tbody><?php foreach ((array)($link['ad_interstitials_summary'] ?? []) as $row): ?><tr><td><?= e((string)($row['id'] ?? '')) ?></td><td><?= e((string)($row['title'] ?? '')) ?></td><td><?= e((string)($row['ad_type'] ?? '')) ?></td><td><?= e((string)($row['status'] ?? '')) ?></td><td><?= e((string)($row['impression_count'] ?? '0')) ?></td><td><?= e((string)($row['click_count'] ?? '0')) ?></td><td><?= !empty($row['media_s3_key_present'])?'true':'false' ?></td><td>false</td><td><?= !empty($row['ad_html_present'])?'true':'false' ?></td><td>false</td></tr><?php endforeach; ?></tbody></table>
</div>
