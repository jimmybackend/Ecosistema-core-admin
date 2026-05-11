<?php declare(strict_types=1); $tenant = is_array($tenant ?? null)?$tenant:[]; $errorMessage = is_string($errorMessage ?? null) ? $errorMessage : null; ?>
<section><h1>Editar tenant #<?= e((string) ($tenant['id'] ?? '')) ?></h1>
<?php if ($errorMessage !== null): ?><div class="eco-alert" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>
<article class="eco-card"><form method="post" action="/tenants/<?= e((string) ($tenant['id'] ?? '0')) ?>">
<input type="hidden" name="_csrf" value="<?= e((string) ($csrfToken ?? '')) ?>">
<p><label>Name <input class="eco-form-control" name="name" value="<?= e((string) ($tenant['name'] ?? '')) ?>" required></label></p>
<p><label>Slug <input class="eco-form-control" name="slug" value="<?= e((string) ($tenant['slug'] ?? '')) ?>" pattern="[a-z0-9-]+" required></label></p>
<p><label>Legal name <input class="eco-form-control" name="legal_name" value="<?= e((string) ($tenant['legal_name'] ?? '')) ?>"></label></p>
<p><label>Domain <input class="eco-form-control" name="domain" value="<?= e((string) ($tenant['domain'] ?? '')) ?>"></label></p>
<p><label>Plan code <input class="eco-form-control" name="plan_code" value="<?= e((string) ($tenant['plan_code'] ?? '')) ?>"></label></p>
<p><label>Status <select class="eco-form-control" name="status" required><?php foreach (['active','trial','suspended','canceled','deleted'] as $status): ?><option value="<?= e($status) ?>" <?= (($tenant['status'] ?? '')===$status)?'selected':'' ?>><?= e($status) ?></option><?php endforeach; ?></select></label></p>
<p><label>Timezone <input class="eco-form-control" name="timezone" value="<?= e((string) ($tenant['timezone'] ?? 'America/Mexico_City')) ?>" required></label></p>
<p><label>Locale <input class="eco-form-control" name="locale" value="<?= e((string) ($tenant['locale'] ?? 'es_MX')) ?>" required></label></p>
<p><button class="eco-button btn" type="submit">Actualizar tenant</button> <a class="eco-button btn" href="/tenants">Volver</a></p>
</form></article></section>
