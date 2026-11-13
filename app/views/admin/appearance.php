<?php
/** @var string $theme tema ativo  @var array $custom opções do tema personalizado */
$preview = function (array $vars): void { ?>
    <span class="theme-preview" style="<?= e(css_vars($vars)) ?>" aria-hidden="true">
        <span class="tp-eyebrow">ROBOTICS</span>
        <span class="tp-title">QIVOLT</span>
        <span class="tp-text">Precision. Autonomy. Purpose.</span>
        <span class="tp-card"><span class="tp-dash"></span>Navigate the unstructured</span>
    </span>
<?php };
$dark = ['--bg' => THEMES['dark']['bg'], '--surface' => THEMES['dark']['surface'], '--fg' => THEMES['dark']['fg'],
    '--volt' => THEMES['dark']['accent'], '--line' => '#232b29', '--muted' => '#93a09b', '--radius' => '14px'];
?>
<form method="post" id="appearance-form">
    <?= csrf_field() ?>
    <div class="head">
        <h1>Aspeto do site</h1>
        <div class="actions">
            <a class="btn" href="/" target="_blank">Ver site ↗</a>
            <button class="btn primary">Guardar</button>
        </div>
    </div>
    <p class="muted">Escolha o tema do site público. O conteúdo não muda, só as cores e a letra.</p>

    <div class="theme-grid">
        <?php foreach (THEMES as $key => $t): ?>
            <label class="card theme-option">
                <span class="theme-head">
                    <input type="radio" name="theme" value="<?= $key ?>" <?= $theme === $key ? 'checked' : '' ?>>
                    <strong><?= e($t['name']) ?></strong>
                    <?php if ($theme === $key): ?><span class="badge pub">Em uso</span><?php endif; ?>
                </span>
                <span class="muted small"><?= e($t['hint']) ?></span>
                <?php $preview($key === 'dark' ? $dark : theme_vars($key, $custom)); ?>
            </label>
        <?php endforeach; ?>
    </div>

    <section class="card" id="custom-options">
        <h2>Opções do tema personalizado</h2>
        <p class="hint">Só se aplicam quando o tema “Personalizado” está escolhido. A pré-visualização acima acompanha as alterações.</p>
        <div class="color-grid">
            <?php foreach (THEME_COLOR_FIELDS as $k => $label): ?>
                <label><?= e($label) ?>
                    <span class="color-field">
                        <input type="color" name="theme_<?= $k ?>" value="<?= e($custom[$k]) ?>" data-theme-color="<?= $k ?>">
                        <code><?= e($custom[$k]) ?></code>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
        <div class="color-grid">
            <label>Tipo de letra
                <select name="theme_font" data-theme-font>
                    <?php foreach (THEME_FONTS as $k => [$label, $stack]): ?>
                        <option value="<?= $k ?>" data-stack="<?= e($stack) ?>" <?= $custom['font'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Cantos dos cartões: <span data-radius-out><?= (int) $custom['radius'] ?></span> px
                <input type="range" name="theme_radius" min="0" max="28" value="<?= (int) $custom['radius'] ?>" data-theme-radius>
            </label>
        </div>
        <label class="check"><input type="checkbox" name="theme_grid" <?= $custom['grid'] ? 'checked' : '' ?>> Grelha de fundo no topo da página inicial</label>
        <p class="hint">Escolha um texto que contraste bem com o fundo. As linhas e o texto secundário são calculados a partir destas cores.</p>
        <button class="btn small" type="submit" name="reset_custom" value="1" data-confirm="Repor as cores e opções iniciais do tema personalizado?">Repor valores iniciais</button>
    </section>
</form>
