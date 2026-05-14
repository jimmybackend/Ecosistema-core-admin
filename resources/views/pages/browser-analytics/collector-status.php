<section class="eco-card stack-md">
  <h1>Browser Analytics collector status</h1>
  <ul>
    <li>collector_write: <?= !empty($status['collector_write']) ? 'true' : 'false' ?></li>
    <li>collector_dry_run: <?= !empty($status['collector_dry_run']) ? 'true' : 'false' ?></li>
    <li>privacy_controls: <?= !empty($status['privacy_controls']) ? 'true' : 'false' ?></li>
  </ul>
</section>
