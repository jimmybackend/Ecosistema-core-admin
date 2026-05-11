<?php declare(strict_types=1); $errorMessage = is_string($errorMessage ?? null) ? $errorMessage : null; ?>
<section>
  <h1>Crear tenant</h1>
  <?php if ($errorMessage !== null): ?><div class="eco-alert" role="alert"><?= e($errorMessage) ?></div><?php endif; ?>
  <article class="eco-card"><form method="post" action="/tenants">
    <input type="hidden" name="_csrf" value="<?= e((string) ($csrfToken ?? '')) ?>">
    <p><label>Name <input class="eco-form-control" name="name" required></label></p>
    <p><label>Slug <input class="eco-form-control" name="slug" pattern="[a-z0-9-]+" required></label></p>
    <p><label>Legal name <input class="eco-form-control" name="legal_name"></label></p>
    <p><label>Domain <input class="eco-form-control" name="domain"></label></p>
    <p><label>Plan code <input class="eco-form-control" name="plan_code"></label></p>
    <p><label>Status <select class="eco-form-control" name="status" required><?php foreach (['active','trial','suspended','canceled','deleted'] as $status): ?><option value="<?= e($status) ?>" <?= $status==='trial'?'selected':'' ?>><?= e($status) ?></option><?php endforeach; ?></select></label></p>
    <p><label>Timezone <input class="eco-form-control" name="timezone" value="America/Mexico_City" required></label></p>
    <p><label>Locale <input class="eco-form-control" name="locale" value="es_MX" required></label></p>
    <p><button class="eco-button btn" type="submit">Guardar tenant</button> <a class="eco-button btn" href="/tenants">Cancelar</a></p>
  </form></article>
</section>
