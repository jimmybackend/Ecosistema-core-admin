<?php $result = is_array($contentData['result'] ?? null) ? $contentData['result'] : null; $errorMessage = $contentData['errorMessage'] ?? null; $input = is_array($contentData['input'] ?? null) ? $contentData['input'] : []; ?>
<section class="card"><h1>Security Rate Limit Enforcement</h1><p>Controlado por flags. Tenant desde sesión. Sin exposición de PII completa.</p></section>
<section class="card">
<form method="post" action="/security/rate-limit/enforce">
<input type="hidden" name="_csrf" value="<?= htmlspecialchars((string)($csrfToken ?? ''), ENT_QUOTES, 'UTF-8') ?>">
<label>Path endpoint</label><input type="text" name="path" value="<?= htmlspecialchars((string)($input['path'] ?? '/api/example'), ENT_QUOTES, 'UTF-8') ?>" required>
<label>IP</label><input type="text" name="ip_address" value="<?= htmlspecialchars((string)($input['ip_address'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
<label>Ventana (minutos)</label><input type="number" min="1" max="120" name="window_minutes" value="<?= htmlspecialchars((string)($input['window_minutes'] ?? '15'), ENT_QUOTES, 'UTF-8') ?>">
<label>Umbral requests</label><input type="number" min="1" max="2000" name="max_requests" value="<?= htmlspecialchars((string)($input['max_requests'] ?? '120'), ENT_QUOTES, 'UTF-8') ?>">
<label>Umbral login failures</label><input type="number" min="1" max="2000" name="max_login_failures" value="<?= htmlspecialchars((string)($input['max_login_failures'] ?? '20'), ENT_QUOTES, 'UTF-8') ?>">
<label>Bloqueo (minutos)</label><input type="number" min="5" max="10080" name="block_minutes" value="<?= htmlspecialchars((string)($input['block_minutes'] ?? '60'), ENT_QUOTES, 'UTF-8') ?>">
<button type="submit">Enforce</button></form></section>
<section class="card"><?php if (is_string($errorMessage) && $errorMessage !== ''): ?><p><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p><?php elseif (!is_array($result)): ?><p>Sin ejecución todavía.</p><?php else: ?><ul><li>mode: <?= htmlspecialchars((string)($result['mode'] ?? ''), ENT_QUOTES, 'UTF-8') ?></li><li>enabled: <?= !empty($result['enabled']) ? 'true' : 'false' ?></li><li>write_blocks_enabled: <?= !empty($result['write_blocks_enabled']) ? 'true' : 'false' ?></li><li>request_blocked: <?= !empty($result['request_blocked']) ? 'true' : 'false' ?></li><li>db_write: <?= !empty($result['db_write']) ? 'true' : 'false' ?></li><li>would_block_reason: <?= htmlspecialchars((string)($result['would_block_reason'] ?? 'none'), ENT_QUOTES, 'UTF-8') ?></li></ul><?php endif; ?></section>
