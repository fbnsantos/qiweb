<h1>Definições</h1>
<form class="card narrow" method="post" style="margin-top: 20px">
    <?= csrf_field() ?>
    <label>Nome do site
        <input name="site_name" value="<?= e(setting('site_name')) ?>" required maxlength="80">
    </label>
    <label>Idioma do site (código, ex.: en, pt-PT)
        <input name="site_lang" value="<?= e(setting('site_lang', 'en')) ?>" pattern="[A-Za-z]{2,3}(-[A-Za-z]{2})?" maxlength="6">
    </label>
    <p class="hint">Os textos do topo da página inicial editam-se em <a href="/admin/home">Página inicial</a>; a descrição do site,
        a indexação no Google e a imagem de partilha em <a href="/admin/seo">SEO</a>.</p>

    <h3>Rodapé</h3>
    <label>Texto do rodapé (vazio = “© ano nome do site”)
        <input name="footer_text" value="<?= e(setting('footer_text')) ?>" maxlength="200">
    </label>
    <label>Ligações do rodapé, uma por linha: <code>Texto | endereço</code>
        <textarea name="footer_links" rows="4" placeholder="Privacy | /pkmt-privacy"><?= e(setting('footer_links')) ?></textarea>
    </label>

    <h3>Contactos</h3>
    <label>Email para receber aviso de cada novo contacto (opcional)
        <input type="email" name="contact_email" value="<?= e(setting('contact_email')) ?>" placeholder="nome@exemplo.com">
    </label>
    <p class="hint">Os pedidos ficam sempre guardados em <a href="/admin/contacts">Contactos</a>. O aviso por email depende do
        servidor conseguir enviar correio; se não chegar, confirme na lista.</p>

    <button class="btn primary">Guardar</button>
</form>
