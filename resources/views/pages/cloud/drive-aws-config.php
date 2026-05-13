<?php
/** @var array<string,mixed> $awsConfig */
$awsConfig = is_array($awsConfig ?? null) ? $awsConfig : [];
?>
<section class="space-y-4">
    <h1 class="text-xl font-semibold">Configuración AWS/S3 preparada pero apagada</h1>
    <p>Modo actual: <strong><?= htmlspecialchars((string)($awsConfig['mode'] ?? 'contract'), ENT_QUOTES, 'UTF-8'); ?></strong></p>
    <ul>
        <li>aws_connection: <strong><?= !empty($awsConfig['aws_connection']) ? 'true' : 'false'; ?></strong></li>
        <li>remote_calls: <strong><?= !empty($awsConfig['allow_remote_calls']) ? 'true' : 'false'; ?></strong></li>
        <li>signed_urls: <strong><?= !empty($awsConfig['allow_signed_urls']) ? 'true' : 'false'; ?></strong></li>
        <li>signed_url_dry_run: <strong>true</strong></li>
        <li>remote_downloads: <strong><?= !empty($awsConfig['allow_remote_downloads']) ? 'true' : 'false'; ?></strong></li>
        <li>remote_uploads: <strong><?= !empty($awsConfig['allow_remote_uploads']) ? 'true' : 'false'; ?></strong></li>
    </ul>

    <h2 class="text-lg font-semibold">Indicadores seguros</h2>
    <ul>
        <li>region_configured: <?= !empty($awsConfig['region_configured']) ? 'true' : 'false'; ?></li>
        <li>bucket_configured: <?= !empty($awsConfig['bucket_configured']) ? 'true' : 'false'; ?></li>
        <li>credentials_configured: <?= !empty($awsConfig['credentials_configured']) ? 'true' : 'false'; ?></li>
        <li>endpoint_configured: <?= !empty($awsConfig['endpoint_configured']) ? 'true' : 'false'; ?></li>
    </ul>

    <h2 class="text-lg font-semibold">Operaciones bloqueadas</h2>
    <ul>
        <?php foreach ((array)($awsConfig['blocked_operations'] ?? []) as $item): ?>
            <li><?= htmlspecialchars((string)$item, ENT_QUOTES, 'UTF-8'); ?></li>
        <?php endforeach; ?>
    </ul>

    <h2 class="text-lg font-semibold">Advertencias</h2>
    <ul>
        <?php foreach ((array)($awsConfig['warnings'] ?? []) as $warning): ?>
            <li><?= htmlspecialchars((string)$warning, ENT_QUOTES, 'UTF-8'); ?></li>
        <?php endforeach; ?>
    </ul>

    <p>
        <a href="/cloud/drive/download-contract">Contrato de descarga</a> |
        <a href="/cloud/drive/files">Validación s3_key</a> |
        <a href="/cloud/drive/summary">Resumen Drive</a>
    </p>
</section>
