<section class="eco-card">
    <h1>Run #<?= e((string) $run['id']) ?></h1>
    <div class="eco-alert">Este PR sólo ejecuta pasos seguros/no-op/manual; no aprovisiona recursos externos.</div>
    <p>Estado: <span class="eco-badge"><?= e((string) $run['status']) ?></span></p>
    <form method="post" action="/onboarding/runs/<?= e((string) $run['id']) ?>/start">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <button class="eco-button" type="submit">Iniciar run</button>
    </form>
    <form method="post" action="/onboarding/runs/<?= e((string) $run['id']) ?>/next-step" style="margin-top:8px;">
        <input type="hidden" name="_csrf" value="<?= e($csrfToken) ?>">
        <button class="eco-button" type="submit">Ejecutar siguiente paso seguro</button>
    </form>
</section>
<section class="eco-card">
    <h3>Steps</h3>
    <table class="eco-table"><tbody>
    <?php foreach ($steps as $s): ?>
        <tr>
            <td><?= e((string) $s['name']) ?></td>
            <td><?= e((string) $s['action_type']) ?></td>
            <td><span class="eco-badge"><?= e((string) $s['status']) ?></span></td>
        </tr>
    <?php endforeach; ?>
    </tbody></table>
</section>
<section class="eco-card"><h3>Logs</h3><table class="eco-table"><tbody><?php foreach($logs as $l): ?><tr><td><?= e((string)$l['created_at']) ?></td><td><?= e((string)$l['level']) ?></td><td><?= e((string)$l['message']) ?></td></tr><?php endforeach; ?></tbody></table></section>
