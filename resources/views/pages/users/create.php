<?php declare(strict_types=1);
$tenants = is_array($tenants ?? null) ? $tenants : [];
$errorMessage = is_string($errorMessage ?? null) ? $errorMessage : null;
$userTypes = ['human','system','student','teacher','agent','admin','service'];
$statuses = ['active','inactive','suspended','deleted'];
?>
<section><h1>Crear usuario</h1>
<?php if ($errorMessage !== null): ?><div class="eco-alert" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>
<article class="eco-card"><form method="post" action="/users">
<input type="hidden" name="_csrf" value="<?= e((string) ($csrfToken ?? '')) ?>">
<p><label>Tenant <select class="eco-form-control" name="tenant_id" required><?php foreach($tenants as $tenant): ?><option value="<?= e((string)$tenant['id']) ?>"><?= e((string)$tenant['name']) ?> (<?= e((string)$tenant['slug']) ?>)</option><?php endforeach; ?></select></label></p>
<p><label>Email <input class="eco-form-control" type="email" name="email" required></label></p>
<p><label>Username <input class="eco-form-control" name="username"></label></p>
<p><label>Password <input class="eco-form-control" type="password" name="password" minlength="8" required></label></p>
<p><label>Display name <input class="eco-form-control" name="display_name"></label></p>
<p><label>First name <input class="eco-form-control" name="first_name"></label></p>
<p><label>Last name <input class="eco-form-control" name="last_name"></label></p>
<p><label>Phone <input class="eco-form-control" name="phone"></label></p>
<p><label>User type <select class="eco-form-control" name="user_type" required><?php foreach($userTypes as $t): ?><option value="<?= e($t) ?>"><?= e($t) ?></option><?php endforeach; ?></select></label></p>
<p><label>Status <select class="eco-form-control" name="status" required><?php foreach($statuses as $s): ?><option value="<?= e($s) ?>" <?= $s==='active'?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?></select></label></p>
<p><button class="eco-button btn" type="submit">Guardar usuario</button> <a class="eco-button btn" href="/users">Cancelar</a></p>
</form></article></section>
