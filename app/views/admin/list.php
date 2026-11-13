<div class="head">
    <h1>Páginas</h1>
    <a class="btn primary" href="/admin/pages/new">+ Nova página</a>
</div>

<table class="table card">
    <thead>
    <tr>
        <th>Título</th>
        <th>Endereço</th>
        <th>Estado</th>
        <th>Menu</th>
        <th class="num"><a href="/admin/stats" class="muted">Visualizações</a></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $it): $url = public_url($it); ?>
        <tr>
            <td><a href="/admin/pages/<?= (int) $it['id'] ?>"><strong><?= e($it['title']) ?></strong></a></td>
            <td><a class="muted" href="<?= e($url) ?>" target="_blank"><?= e($url) ?></a></td>
            <td>
                <?php if (!$it['published']): ?><span class="badge draft">Rascunho</span>
                <?php else: ?><span class="badge pub">Publicado</span><?php endif; ?>
                <?php if ($it['noindex']): ?><span class="badge">noindex</span><?php endif; ?>
            </td>
            <td class="muted nowrap">
                <?= $it['slug'] === 'home' ? 'Página inicial' : ($it['in_menu'] ? 'Sim (' . (int) $it['menu_order'] . ')' : '—') ?>
            </td>
            <td class="num muted"><?= number_format($views[$it['id']] ?? 0, 0, ',', ' ') ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
