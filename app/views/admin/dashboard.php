<?php $n = fn(int $v) => number_format($v, 0, ',', ' '); ?>
<div class="head">
    <h1>Painel</h1>
    <div class="actions">
        <a class="btn primary" href="/admin/home">Editar página inicial</a>
        <a class="btn" href="/admin/pages/new">+ Nova página</a>
    </div>
</div>

<div class="stats">
    <a class="stat" href="/admin/stats?dias=7"><b><?= $n($counts['today']) ?></b><span>visualizações hoje</span></a>
    <a class="stat" href="/admin/stats"><b><?= $n($counts['views30']) ?></b><span>visualizações (30 dias)</span></a>
    <a class="stat" href="/admin/stats"><b><?= $n($counts['visitors30']) ?></b><span>visitantes (30 dias)</span></a>
    <a class="stat" href="/admin/contacts"><b><?= $n($counts['contacts']) ?></b><span>contactos por tratar</span></a>
</div>

<section class="card">
    <h2>Versão do site</h2>
    <p><?= $version !== '' ? e($version) : '<span class="muted">Desconhecida (git indisponível nesta pasta ou neste servidor).</span>' ?></p>
    <a class="btn small" href="/admin/update">Atualizar a partir do GitHub</a>
</section>

<?php if ($checks): ?>
    <section class="card">
        <h2>Avisos</h2>
        <ul class="checks">
            <?php foreach ($checks as [$level, $msg]): ?><li class="<?= $level ?>"><?= e($msg) ?></li><?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<section class="card">
    <h2>Editado recentemente</h2>
    <table class="table">
        <?php foreach ($recent as $r): ?>
            <tr>
                <td><a href="/admin/pages/<?= (int) $r['id'] ?>"><?= e($r['title']) ?></a></td>
                <td class="muted"><?= $r['slug'] === 'home' ? 'Página inicial' : '/' . e($r['slug']) ?></td>
                <td class="muted nowrap"><?= e(format_date($r['updated_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>

<section class="card">
    <h2>SEO e Google</h2>
    <p class="muted">robots.txt, sitemap.xml, llms.txt, imagens de partilha e IndexNow são gerados automaticamente.</p>
    <a class="btn small" href="/admin/seo">Ver SEO e avisos por página</a>
</section>
