<section class="hero">
    <div class="wrap-wide">
        <?php if ($preview): ?><p class="notice">Preview — not published.</p><?php endif; ?>
        <?php if (setting('hero_eyebrow')): ?><p class="eyebrow"><?= e(setting('hero_eyebrow')) ?></p><?php endif; ?>
        <h1><?= e($page['title']) ?></h1>
        <?php if (setting('site_tagline')): ?><p class="hero-tagline"><?= e(setting('site_tagline')) ?></p><?php endif; ?>
        <?php if (setting('hero_sub')): ?><p class="hero-sub"><?= e(setting('hero_sub')) ?></p><?php endif; ?>
        <?php if (setting('hero_cta_label') && safe_url(setting('hero_cta_url'), true)): ?>
            <p class="hero-cta"><a class="button" href="<?= e(setting('hero_cta_url')) ?>"><?= e(setting('hero_cta_label')) ?> →</a></p>
        <?php endif; ?>
    </div>
</section>

<?php if ($page['cover_image']): ?>
    <div class="wrap-wide"><img class="cover" src="<?= e($page['cover_image']) ?>" alt="<?= e($page['title']) ?>" fetchpriority="high"></div>
<?php endif; ?>

<?php foreach (home_blocks($page['content']) as $i => [$kind, $body]): ?>
    <?php if ($kind === 'cards'): ?>
        <section class="wrap-wide cards">
            <?php foreach ($body as $card): ?>
                <article class="card prose"><?= $card ?></article>
            <?php endforeach; ?>
        </section>
    <?php else: ?>
        <section class="wrap prose home-prose<?= $i === 0 ? ' lead' : ' closing' ?>"><?= $body ?></section>
    <?php endif; ?>
<?php endforeach; ?>

<?php if ($page['contact_form']) {
    require __DIR__ . '/partials/contact-form.php';
    echo '<div class="page-end"></div>';
} ?>
