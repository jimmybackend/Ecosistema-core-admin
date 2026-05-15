<?php $events=(array)($events??[]); $filters=(array)($filters??[]); ?>
<section><h1>Auditoría unificada</h1>
<form method="get" action="/audit/events"><input name="module_code" placeholder="module_code" value="<?= htmlspecialchars((string)($filters['module_code']??'')) ?>"><input name="action" placeholder="action" value="<?= htmlspecialchars((string)($filters['action']??'')) ?>"><input type="date" name="from" value="<?= htmlspecialchars((string)($filters['from']??'')) ?>"><input type="date" name="to" value="<?= htmlspecialchars((string)($filters['to']??'')) ?>"><button type="submit">Filtrar</button></form>
<table><thead><tr><th>ID</th><th>Módulo</th><th>Acción</th><th>Entidad</th><th>IP</th><th>UA</th><th>Cambios</th><th>Links</th><th>Fecha</th></tr></thead><tbody>
<?php foreach($events as $e): ?><tr><td><a href="/audit/events/<?= (int)$e['id'] ?>"><?= (int)$e['id'] ?></a></td><td><?= htmlspecialchars((string)$e['module_code']) ?></td><td><?= htmlspecialchars((string)$e['action']) ?></td><td><?= htmlspecialchars((string)$e['entity_table']) ?>#<?= (int)($e['entity_id']??0) ?></td><td><?= htmlspecialchars((string)$e['ip_preview']) ?></td><td><?= htmlspecialchars((string)$e['user_agent_preview']) ?></td><td><?= (int)$e['change_count'] ?></td><td><?= (int)$e['link_count'] ?></td><td><?= htmlspecialchars((string)$e['created_at']) ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php if($events===[]): ?><p>Sin eventos para los filtros actuales.</p><?php endif; ?>
</section>
