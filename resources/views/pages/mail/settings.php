<?php
$smtp = is_array($smtp ?? null) ? $smtp : [];
$validationErrors = is_array($smtp['validation_errors'] ?? null) ? $smtp['validation_errors'] : [];
$isValid = (bool) ($smtp['is_valid'] ?? false);
$sendEnabled = (bool) ($smtp['send_enabled'] ?? false);
?>
<section>
  <h1>Configuración SMTP</h1>
  <p>Estado administrativo de configuración local de Core Admin/Mail.</p>

  <div class="eco-alert" role="alert">El envío real está deshabilitado en este PR.</div>

  <article class="eco-card">
    <table class="eco-table" style="width:100%">
      <tbody>
        <tr><th>Estado configuración</th><td><span class="eco-badge"><?= $isValid ? 'Válida' : 'Incompleta' ?></span></td></tr>
        <tr><th>MAIL_SEND_ENABLED</th><td><span class="eco-badge"><?= $sendEnabled ? 'true' : 'false' ?></span></td></tr>
        <tr><th>Mailer</th><td><?= e((string) ($smtp['mailer'] ?? '')) ?></td></tr>
        <tr><th>Host</th><td><?= e((string) ($smtp['host'] ?? '')) ?></td></tr>
        <tr><th>Port</th><td><?= e((string) ($smtp['port'] ?? '')) ?></td></tr>
        <tr><th>Encryption</th><td><?= e((string) ($smtp['encryption'] ?? '')) ?></td></tr>
        <tr><th>Username (enmascarado)</th><td><?= e((string) ($smtp['username_masked'] ?? '')) ?></td></tr>
        <tr><th>From address</th><td><?= e((string) ($smtp['from_address'] ?? '')) ?></td></tr>
        <tr><th>From name</th><td><?= e((string) ($smtp['from_name'] ?? '')) ?></td></tr>
      </tbody>
    </table>

    <?php if ($validationErrors !== []): ?>
      <div class="eco-alert" role="alert" style="margin-top:1rem;">
        <strong>Validación local:</strong>
        <ul>
          <?php foreach ($validationErrors as $error): ?>
            <li><?= e((string) $error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div style="margin-top:1rem;">
      <a class="eco-button btn" href="/mail">Volver a Mail</a>
    </div>
  </article>
</section>
