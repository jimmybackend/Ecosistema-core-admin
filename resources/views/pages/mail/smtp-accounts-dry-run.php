<?php $preview = is_array($preview ?? null) ? $preview : []; ?>
<section><h1>SMTP Test Dry-Run</h1><table class="eco-table"><tbody><?php foreach($preview as $k=>$v): ?><tr><th><?= e((string)$k) ?></th><td><?= e((string)$v) ?></td></tr><?php endforeach; ?></tbody></table><a class="eco-button btn" href="/mail/smtp-accounts">Volver</a></section>
