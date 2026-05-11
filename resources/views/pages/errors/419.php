<?php

declare(strict_types=1);

$statusCode = (int) ($statusCode ?? 500);
$message = (string) ($message ?? 'Ocurrió un error interno.');
?>
<section class="eco-card" style="max-width: 680px; margin: 1rem auto;">
  <h1 style="margin-bottom:.5rem;"><?= e((string) $statusCode) ?></h1>
  <p style="margin:0;"><?= e($message) ?></p>
</section>
