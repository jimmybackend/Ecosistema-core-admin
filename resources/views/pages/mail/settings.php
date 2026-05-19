<?php
$smtp = is_array($smtp ?? null) ? $smtp : [];
$effectiveSmtp = is_array($effectiveSmtp ?? null) ? $effectiveSmtp : [];
$validationErrors = is_array($smtp['validation_errors'] ?? null) ? $smtp['validation_errors'] : [];
$isValid = (bool) ($smtp['is_valid'] ?? false);
$sendEnabled = (bool) ($smtp['send_enabled'] ?? false);
$yn = static fn ($v): string => ((string)$v === '1' || $v === 1 || $v === true || $v === 'yes') ? 'yes' : 'no';
?>
<section>
  <h1>Configuración SMTP</h1>
  <p>Estado administrativo de configuración local de Core Admin/Mail.</p>

  <div class="eco-alert" role="alert">El envío real está deshabilitado en este PR.</div>
  <div class="eco-alert" role="alert">La contraseña SMTP es independiente de la contraseña de acceso al panel.</div>

  <article class="eco-card" style="margin-top:1rem;">
    <h2>SMTP efectivo actual (dry-run)</h2>
    <table class="eco-table" style="width:100%"><tbody>
      <tr><th>source</th><td><?= e((string)($effectiveSmtp['source'] ?? 'global_env')) ?></td></tr>
      <tr><th>status</th><td><?= e((string)($effectiveSmtp['status'] ?? 'unknown')) ?></td></tr>
      <tr><th>mailbox_full_address</th><td><?= e((string)($effectiveSmtp['mailbox_full_address'] ?? '')) ?></td></tr>
      <tr><th>account_name</th><td><?= e((string)($effectiveSmtp['account_name'] ?? '')) ?></td></tr>
      <tr><th>email_address</th><td><?= e((string)($effectiveSmtp['email_address'] ?? '')) ?></td></tr>
      <tr><th>host_in</th><td><?= e((string)($effectiveSmtp['host_in'] ?? '')) ?></td></tr>
      <tr><th>port_in</th><td><?= e((string)($effectiveSmtp['port_in'] ?? '')) ?></td></tr>
      <tr><th>ssl_in</th><td><?= e((string)($effectiveSmtp['ssl_in'] ?? '')) ?></td></tr>
      <tr><th>host_out</th><td><?= e((string)($effectiveSmtp['host_out'] ?? $effectiveSmtp['host'] ?? $smtp['host'] ?? '')) ?></td></tr>
      <tr><th>port_out</th><td><?= e((string)($effectiveSmtp['port_out'] ?? $effectiveSmtp['port'] ?? $smtp['port'] ?? '')) ?></td></tr>
      <tr><th>ssl_out</th><td><?= e((string)($effectiveSmtp['ssl_out'] ?? $effectiveSmtp['encryption'] ?? $smtp['encryption'] ?? '')) ?></td></tr>
      <tr><th>username_masked</th><td><?= e((string)($effectiveSmtp['username_masked'] ?? $smtp['username_masked'] ?? '')) ?></td></tr>
      <tr><th>max_daily_email</th><td><?= e((string)($effectiveSmtp['max_daily_email'] ?? '')) ?></td></tr>
      <tr><th>enable_limit</th><td><?= e($yn($effectiveSmtp['enable_limit'] ?? 0)) ?></td></tr>
      <tr><th>available_to_everyone</th><td><?= e($yn($effectiveSmtp['available_to_everyone'] ?? 0)) ?></td></tr>
      <tr><th>password_encrypted_present</th><td><?= e($yn($effectiveSmtp['password_encrypted_present'] ?? 'no')) ?></td></tr>
      <tr><th>last_error</th><td><?= e((string)($effectiveSmtp['last_error'] ?? '')) ?></td></tr>
    </tbody></table>
  </article>

  <article class="eco-card" style="margin-top:1rem;">
    <h2>SMTP global fallback (.env)</h2>
    <table class="eco-table" style="width:100%"><tbody>
      <tr><th>Estado configuración</th><td><span class="eco-badge"><?= $isValid ? 'Válida' : 'Incompleta' ?></span></td></tr>
      <tr><th>MAIL_SEND_ENABLED</th><td><span class="eco-badge"><?= $sendEnabled ? 'true' : 'false' ?></span></td></tr>
      <tr><th>MAIL_ALLOW_TEST_SEND</th><td><span class="eco-badge"><?= (bool)($smtp['allow_test_send'] ?? false) ? 'true' : 'false' ?></span></td></tr>
      <tr><th>mailer</th><td><?= e((string) ($smtp['mailer'] ?? '')) ?></td></tr>
      <tr><th>host</th><td><?= e((string) ($smtp['host'] ?? '')) ?></td></tr>
      <tr><th>port</th><td><?= e((string) ($smtp['port'] ?? '')) ?></td></tr>
      <tr><th>encryption</th><td><?= e((string) ($smtp['encryption'] ?? '')) ?></td></tr>
      <tr><th>username_masked</th><td><?= e((string) ($smtp['username_masked'] ?? '')) ?></td></tr>
      <tr><th>from_address</th><td><?= e((string) ($smtp['from_address'] ?? '')) ?></td></tr>
      <tr><th>from_name</th><td><?= e((string) ($smtp['from_name'] ?? '')) ?></td></tr>
    </tbody></table>
  </article>

  <article class="eco-card" style="margin-top:1rem;">
    <h2>Acciones</h2>
    <?php if ($validationErrors !== []): ?>
      <div class="eco-alert" role="alert" style="margin-top:1rem;">
        <strong>Validación local:</strong>
        <ul><?php foreach ($validationErrors as $error): ?><li><?= e((string) $error) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>
    <div style="margin-top:1rem;display:flex;gap:.5rem;">
      <a class="eco-button btn" href="/mail/smtp-accounts">Gestionar cuentas SMTP</a>
      <a class="eco-button btn" href="/mail/smtp-accounts/create">Crear SMTP propio</a>
      <a class="eco-button btn" href="/mail">Volver a Mail</a>
    </div>
  </article>
</section>
