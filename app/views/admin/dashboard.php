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
    <p class="muted">Gerado automaticamente a partir do conteúdo:</p>
    <ul class="links">
        <li><a href="/robots.txt" target="_blank">robots.txt</a></li>
        <li><a href="/sitemap.xml" target="_blank">sitemap.xml</a> (páginas e imagens)</li>
        <li><a href="/llms.txt" target="_blank">llms.txt</a> (resumo para ChatGPT, Perplexity e outros motores com IA)</li>
        <li>
            <?php if (og_available()): ?>
                <a href="<?= e(og_image_url(setting('site_name'))) ?>" target="_blank">Imagens de partilha 1200×630</a>
                geradas para cada página sem capa, com as cores do tema
            <?php else: ?>
                <span class="muted">Imagens de partilha: indisponíveis (falta a extensão GD do PHP neste servidor)</span>
            <?php endif; ?>
        </li>
        <li>
            <?php if (indexnow_active()): ?>IndexNow avisa o Bing ao guardar uma página publicada
            <?php else: ?><span class="muted">IndexNow: desligado (só funciona no domínio público e com a indexação permitida)</span><?php endif; ?>
        </li>
        <li><a href="https://search.google.com/test/rich-results?url=<?= e(rawurlencode(abs_url('/'))) ?>" target="_blank" rel="noopener">Testar dados estruturados no Google ↗</a></li>
        <li><a href="https://search.google.com/search-console" target="_blank" rel="noopener">Google Search Console ↗</a></li>
    </ul>
</section>
