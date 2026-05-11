<?php $mailboxes = is_array($mailboxes ?? null) ? $mailboxes : []; $statusMessage = is_string($statusMessage ?? null)?$statusMessage:null; $errorMessage = is_string($errorMessage ?? null)?$errorMessage:null; ?>
<section><h1>Crear borrador</h1>
<?php if ($statusMessage): ?><div class="eco-alert" role="status"><?= e($statusMessage) ?></div><?php endif; ?>
<?php if ($errorMessage): ?><div class="eco-alert" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>
<?php if ($mailboxes === []): ?><div class="eco-alert">No hay mailbox activo disponible para crear borradores.</div>
<?php else: ?><article class="eco-card"><form method="post" action="/mail/drafts">
<input type="hidden" name="_csrf" value="<?= e((string)$csrfToken) ?>">
<label>Mailbox</label><select class="eco-form-control" name="mailbox_id" required><?php foreach($mailboxes as $mb): ?><option value="<?= e((string)$mb['id']) ?>"><?= e((string)$mb['full_address']) ?></option><?php endforeach; ?></select>
<label>To</label><input class="eco-form-control" name="to_addresses" required>
<label>CC</label><input class="eco-form-control" name="cc_addresses">
<label>BCC</label><input class="eco-form-control" name="bcc_addresses">
<label>Subject</label><input class="eco-form-control" name="subject">
<label>Body text</label><textarea class="eco-form-control" name="body_text"></textarea>
<button class="eco-button btn" type="submit">Guardar borrador</button></form></article><?php endif; ?></section>
