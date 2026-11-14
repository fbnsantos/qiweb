<?php
/** @var array $pages linhas de seo_audit()  @var int $problems páginas com avisos */
$host = preg_replace('#^https?://#', '', base_url());
?>
<div class="head">
    <h1>SEO</h1>
    <div class="actions">
        <a class="btn" href="https://search.google.com/search-console" target="_blank" rel="noopener">Google Search Console ↗</a>
    </div>
</div>
<p class="muted">Como o site aparece no Google, no Bing, nos assistentes de IA e quando é partilhado nas redes sociais.</p>

<div class="stats">
    <div class="stat stat-tile">
        <b><?= indexing_allowed() ? 'Sim' : 'Não' ?></b><span>indexação permitida</span>
        <span class="muted"><?= indexing_allowed() ? 'o Google pode mostrar o site' : 'o robots.txt bloqueia tudo' ?></span>
    </div>
    <div class="stat stat-tile">
        <b><?= count(array_filter($pages, fn($p) => $p['indexable'])) ?></b><span>páginas no sitemap</span>
        <span class="muted">de <?= count($pages) ?> páginas</span>
    </div>
    <div class="stat stat-tile">
        <b><?= $problems ?></b><span>páginas com avisos</span>
        <span class="muted"><?= $problems ? 'veja a tabela abaixo' : 'tudo em ordem' ?></span>
    </div>
</div>

<section class="card">
    <h2>Páginas</h2>
    <table class="table">
        <thead>
        <tr><th>Página</th><th>No Google</th><th>Descrição</th><th>Imagem de partilha</th><th>Avisos</th></tr>
        </thead>
        <tbody>
        <?php foreach ($pages as $p): ?>
            <tr>
                <td>
                    <a href="/admin/pages/<?= (int) $p['id'] ?>"><strong><?= e($p['title']) ?></strong></a>
                    <div class="muted small"><?= e($host . $p['url']) ?></div>
                </td>
                <td>
                    <?php if (!$p['published']): ?><span class="badge draft">Rascunho</span>
                    <?php elseif (!$p['indexable']): ?><span class="badge">Escondida</span>
                    <?php else: ?><span class="badge pub">Indexável</span><?php endif; ?>
                </td>
                <td class="muted small"><?= $p['desc_len'] ? $p['desc_len'] . ' caracteres' : 'automática' ?></td>
                <td class="muted small">
                    <a href="<?= e($p['image']) ?>" target="_blank"><?= $p['image_kind'] ?></a>
                </td>
                <td class="small">
                    <?php if (!$p['warnings']): ?><span class="ok-text">OK</span><?php endif; ?>
                    <?php foreach ($p['warnings'] as $w): ?><div><?= e($w) ?></div><?php endforeach; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="hint">O título, o endereço e a descrição de cada página editam-se na própria página (caixa “Google”).
        Descrição ideal: 70–160 caracteres. Páginas escondidas ou em rascunho não contam para os avisos.</p>
</section>

<form class="card" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <h2>Google e motores de pesquisa</h2>
    <label class="check"><input type="checkbox" name="allow_indexing" <?= indexing_allowed() ? 'checked' : '' ?>>
        Permitir que o Google e outros motores indexem o site</label>
    <p class="hint">Desligado, o <code>robots.txt</code> bloqueia tudo e todas as páginas levam <code>noindex</code>. Útil enquanto o site está em construção.</p>
    <label>Descrição do site (usada na página inicial e quando uma página não tem descrição própria)
        <textarea name="site_description" rows="3" maxlength="300" data-count="160"><?= e(setting('site_description')) ?></textarea>
    </label>
    <label>Código de verificação do Google Search Console
        <input name="google_site_verification" value="<?= e(setting('google_site_verification')) ?>" placeholder="cole o código ou a meta tag inteira">
    </label>
    <label>Código de verificação do Bing Webmaster Tools
        <input name="bing_site_verification" value="<?= e(setting('bing_site_verification')) ?>" placeholder="cole o código ou a meta tag inteira">
    </label>
    <p class="hint">Depois de verificar o site, submeta <code><?= e(abs_url('/sitemap.xml')) ?></code> em cada uma das ferramentas.</p>

    <h3>Imagem de partilha</h3>
    <p class="hint">Mostrada quando alguém partilha uma ligação do site (LinkedIn, WhatsApp, X…). Ordem de escolha: a imagem de capa
        da página, depois esta imagem por omissão, depois uma imagem gerada com o título da página.</p>
    <?php if (setting('default_image')): ?><img class="thumb" src="<?= e(setting('default_image')) ?>" alt=""><?php endif; ?>
    <label>Imagem por omissão (URL; vazio = gerar automaticamente)
        <input name="default_image" value="<?= e(setting('default_image')) ?>" placeholder="/uploads/…">
    </label>
    <label>…ou enviar nova <input type="file" name="default_image_file" accept="image/*"></label>
    <button class="btn primary">Guardar</button>
</form>

<section class="card">
    <h2>Gerado automaticamente</h2>
    <table class="table">
        <tr>
            <td><a href="/robots.txt" target="_blank">robots.txt</a></td>
            <td class="muted">Diz aos motores o que podem ler; bloqueia o backoffice e aponta para o sitemap.</td>
        </tr>
        <tr>
            <td><a href="/sitemap.xml" target="_blank">sitemap.xml</a></td>
            <td class="muted">Lista das páginas indexáveis e das suas imagens, com a data da última alteração.</td>
        </tr>
        <tr>
            <td><a href="/llms.txt" target="_blank">llms.txt</a></td>
            <td class="muted">Resumo do site e dos serviços em Markdown, para ChatGPT, Perplexity e outros motores com IA.</td>
        </tr>
        <tr>
            <td><?php if (og_available()): ?><a href="<?= e(og_image_url(setting('site_name'))) ?>" target="_blank">Imagens de partilha</a><?php else: ?>Imagens de partilha<?php endif; ?></td>
            <td class="muted"><?= og_available()
                ? '1200×630, com o título da página e as cores do tema, para páginas sem capa.'
                : 'Indisponíveis: falta a extensão GD (com FreeType) do PHP neste servidor.' ?></td>
        </tr>
        <tr>
            <td>IndexNow</td>
            <td class="muted"><?= indexnow_active()
                ? 'Ativo: ao guardar uma página publicada, o Bing, o Yandex e o Seznam são avisados (o Google usa o sitemap).'
                : 'Desligado: só funciona no domínio público e com a indexação permitida.' ?></td>
        </tr>
        <tr>
            <td>Dados estruturados</td>
            <td class="muted">JSON-LD: organização, logótipo e site na página inicial; caminho “Home › Página” nas outras ·
                <a href="https://search.google.com/test/rich-results?url=<?= e(rawurlencode(abs_url('/'))) ?>" target="_blank" rel="noopener">testar no Google ↗</a></td>
        </tr>
    </table>
</section>
