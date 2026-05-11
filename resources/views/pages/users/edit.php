<?php declare(strict_types=1);
$user = is_array($user ?? null) ? $user : [];
$tenants = is_array($tenants ?? null) ? $tenants : [];
$errorMessage = is_string($errorMessage ?? null) ? $errorMessage : null;
$userTypes = ['human','system','student','teacher','agent','admin','service'];
$statuses = ['active','inactive','suspended','deleted'];
?>
<section><h1>Editar usuario #<?= e((string) ($user['id'] ?? '')) ?></h1>
<?php if ($errorMessage !== null): ?><div class="eco-alert" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>
<article class="eco-card"><form method="post" action="/users/<?= e((string) ($user['id'] ?? '0')) ?>">
<input type="hidden" name="_csrf" value="<?= e((string) ($csrfToken ?? '')) ?>">
<p><label>Tenant <select class="eco-form-control" name="tenant_id" required><?php foreach($tenants as $tenant): ?><option value="<?= e((string)$tenant['id']) ?>" <?= ((int)($user['tenant_id'] ?? 0)===(int)$tenant['id'])?'selected':'' ?>><?= e((string)$tenant['name']) ?> (<?= e((string)$tenant['slug']) ?>)</option><?php endforeach; ?></select></label></p>
<p><label>Email <input class="eco-form-control" type="email" name="email" value="<?= e((string)($user['email'] ?? '')) ?>" required></label></p>
<p><label>Username <input class="eco-form-control" name="username" value="<?= e((string)($user['username'] ?? '')) ?>"></label></p>
<p><label>Display name <input class="eco-form-control" name="display_name" value="<?= e((string)($user['display_name'] ?? '')) ?>"></label></p>
<p><label>First name <input class="eco-form-control" name="first_name" value="<?= e((string)($user['first_name'] ?? '')) ?>"></label></p>
<p><label>Last name <input class="eco-form-control" name="last_name" value="<?= e((string)($user['last_name'] ?? '')) ?>"></label></p>
<p><label>Phone <input class="eco-form-control" name="phone" value="<?= e((string)($user['phone'] ?? '')) ?>"></label></p>
<p><label>User type <select class="eco-form-control" name="user_type" required><?php foreach($userTypes as $t): ?><option value="<?= e($t) ?>" <?= (($user['user_type'] ?? '')===$t)?'selected':'' ?>><?= e($t) ?></option><?php endforeach; ?></select></label></p>
<p><label>Status <select class="eco-form-control" name="status" required><?php foreach($statuses as $s): ?><option value="<?= e($s) ?>" <?= (($user['status'] ?? '')===$s)?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?></select></label></p>
<p><button class="eco-button btn" type="submit">Actualizar usuario</button> <a class="eco-button btn" href="/users">Volver</a></p>
</form></article>
<article class="eco-card" style="margin-top:1rem;"><h2>Cambiar contraseña</h2>
<form method="post" action="/users/<?= e((string) ($user['id'] ?? '0')) ?>/password">
<input type="hidden" name="_csrf" value="<?= e((string) ($csrfToken ?? '')) ?>">
<p><label>Nueva contraseña <input class="eco-form-control" type="password" name="password" minlength="8" required></label></p>
<p><button class="eco-button btn" type="submit">Actualizar contraseña</button></p>
</form></article></section>
