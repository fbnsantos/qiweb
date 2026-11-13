<h1>Definições</h1>
<form class="card narrow" method="post" enctype="multipart/form-data" style="margin-top: 20px">
    <?= csrf_field() ?>
    <label>Nome do site
        <input name="site_name" value="<?= e(setting('site_name')) ?>" required maxlength="80">
    </label>
    <label>Descrição do site (Google e redes sociais)
        <textarea name="site_description" rows="3" maxlength="300" data-count="160"><?= e(setting('site_description')) ?></textarea>
    </label>
    <label>Idioma do site (código, ex.: en, pt-PT)
        <input name="site_lang" value="<?= e(setting('site_lang', 'en')) ?>" pattern="[A-Za-z]{2,3}(-[A-Za-z]{2})?" maxlength="6">
    </label>
    <p class="hint">Os textos do topo da página inicial (lema, etc.) editam-se em <a href="/admin/home">Página inicial</a>.</p>

    <h3>Rodapé</h3>
    <label>Texto do rodapé (vazio = “© ano nome do site”)
        <input name="footer_text" value="<?= e(setting('footer_text')) ?>" maxlength="200">
    </label>
    <label>Ligações do rodapé, uma por linha: <code>Texto | endereço</code>
        <textarea name="footer_links" rows="4" placeholder="Privacy | /pkmt-privacy"><?= e(setting('footer_links')) ?></textarea>
    </label>

    <h3>Google e partilha</h3>
    <label class="check"><input type="checkbox" name="allow_indexing" <?= setting('allow_indexing', '1') === '1' ? 'checked' : '' ?>>
        Permitir que o Google indexe o site</label>
    <label>Código de verificação do Google Search Console
        <input name="google_site_verification" value="<?= e(setting('google_site_verification')) ?>" placeholder="cole o código ou a meta tag inteira">
    </label>
    <?php if (setting('default_image')): ?><img class="thumb" src="<?= e(setting('default_image')) ?>" alt=""><?php endif; ?>
    <label>Imagem de partilha por omissão (URL)
        <input name="default_image" value="<?= e(setting('default_image')) ?>" placeholder="/uploads/…">
    </label>
    <label>…ou enviar nova <input type="file" name="default_image_file" accept="image/*"></label>

    <button class="btn primary">Guardar</button>
</form>
