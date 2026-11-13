<form id="editor-form" method="post" enctype="multipart/form-data"
      action="/admin/pages/<?= $item['id'] ?: 'new' ?>">
    <?= csrf_field() ?>
    <div class="head">
        <h1><?= e($title) ?></h1>
        <div class="actions">
            <?php if ($item['id']): ?>
                <a class="btn" href="<?= e(public_url($item)) ?>" target="_blank">Ver no site ↗</a>
            <?php endif; ?>
            <button class="btn primary">Guardar</button>
        </div>
    </div>

    <?php if ($errors): ?>
        <div class="flash error"><?php foreach ($errors as $err): ?><div><?= e($err) ?></div><?php endforeach; ?></div>
    <?php endif; ?>

    <div class="edit-grid">
        <div class="edit-main">
            <input class="title-input" name="title" value="<?= e($item['title']) ?>" required
                   placeholder="<?= $isHome ? 'Título grande do topo' : 'Título' ?>"
                   data-slug-source <?= $item['id'] ? '' : 'autofocus' ?>>

            <?php if ($isHome): ?>
                <section class="card">
                    <h3>Topo da página inicial</h3>
                    <p class="hint">O título acima aparece em letras grandes; por baixo ficam estes três textos.</p>
                    <label>Linha pequena por cima do título
                        <input name="hero_eyebrow" value="<?= e($hero['hero_eyebrow']) ?>" maxlength="300" placeholder="Robotics · Intelligent sensing">
                    </label>
                    <label>Lema
                        <input name="site_tagline" value="<?= e($hero['site_tagline']) ?>" maxlength="300">
                    </label>
                    <label>Frase por baixo do lema
                        <input name="hero_sub" value="<?= e($hero['hero_sub']) ?>" maxlength="300">
                    </label>
                </section>
            <?php endif; ?>

            <textarea id="content" name="content"><?= e($item['content']) ?></textarea>
            <p class="hint">
                <?php if ($isHome): ?>
                    <strong>Como a página inicial é desenhada:</strong> duas ou mais secções seguidas com “Título 2” aparecem lado a lado
                    como cartões · uma linha horizontal separa blocos · o último bloco fica centrado, com a citação em destaque.<br>
                <?php endif; ?>
                <strong>Dicas:</strong> arraste imagens para o editor para as enviar ·
                para um vídeo do YouTube cole o link sozinho numa linha ·
                Ctrl+S guarda.
            </p>
        </div>

        <aside class="edit-side">
            <?php if (!$isHome): ?>
                <section class="card">
                    <h3>Publicação</h3>
                    <label class="check"><input type="checkbox" name="published" <?= $item['published'] ? 'checked' : '' ?>> Publicado</label>
                    <label class="check"><input type="checkbox" name="in_menu" <?= $item['in_menu'] ? 'checked' : '' ?>> Mostrar no menu</label>
                    <label>Nome no menu <span class="muted small">(vazio = título)</span>
                        <input name="menu_label" value="<?= e($item['menu_label']) ?>" maxlength="40">
                    </label>
                    <label>Ordem no menu <input type="number" name="menu_order" value="<?= (int) $item['menu_order'] ?>"></label>
                </section>
            <?php endif; ?>

            <section class="card">
                <h3>Google</h3>
                <label>Endereço
                    <span class="slug-field"><span class="muted">/</span>
                        <?php if ($isHome): ?>
                            <input name="slug" value="" readonly placeholder="(página inicial)" title="A página inicial está sempre em /">
                        <?php else: ?>
                            <input name="slug" value="<?= e($item['slug']) ?>" pattern="[a-z0-9_\-]*" data-slug placeholder="gerado-do-titulo">
                        <?php endif; ?>
                    </span>
                </label>
                <label>Descrição para o Google
                    <textarea name="meta_description" rows="3" maxlength="300" data-count="160"
                              placeholder="1–2 frases que resumem a página (≈150 caracteres)."><?= e($item['meta_description']) ?></textarea>
                </label>
                <label class="check"><input type="checkbox" name="noindex" <?= $item['noindex'] ? 'checked' : '' ?>> Esconder do Google (noindex)</label>
            </section>

            <section class="card">
                <h3>Imagem de capa</h3>
                <p class="hint">Aparece no topo da página e ao partilhar nas redes sociais (ideal 1200×630).</p>
                <?php if ($item['cover_image']): ?><img class="thumb" src="<?= e($item['cover_image']) ?>" alt=""><?php endif; ?>
                <label>URL da imagem <input name="cover_image" value="<?= e($item['cover_image']) ?>" placeholder="/uploads/…"></label>
                <label>…ou enviar nova <input type="file" name="cover_file" accept="image/*"></label>
            </section>

            <?php if ($item['id'] && !$isHome): ?>
                <button class="btn danger" form="delete-form"
                        data-confirm="Apagar &quot;<?= e($item['title']) ?>&quot;? Esta ação não pode ser desfeita.">Apagar página</button>
            <?php endif; ?>
        </aside>
    </div>
</form>

<?php if ($item['id'] && !$isHome): ?>
    <form id="delete-form" method="post" action="/admin/pages/<?= (int) $item['id'] ?>/delete"><?= csrf_field() ?></form>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/tinymce@7.9.2/tinymce.min.js" integrity="sha384-C7mWIB9Vj1ePj/U7oKCJWgLbv1ttjXrW3JnNpeNjOJfwUWGBsnLtJ5X3alR5V1WU" crossorigin="anonymous" referrerpolicy="origin"></script>
<script>
    window.EDITOR_CSS = '/assets/site.css?v=<?= filemtime(ROOT . '/assets/site.css') ?>';
</script>
