<header class="page-head">
    <div class="wrap">
        <?php if ($preview): ?><p class="notice">Preview — not published.</p><?php endif; ?>
        <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a> / <span><?= e($page['menu_label'] ?: $page['title']) ?></span></nav>
        <h1><?= e($page['title']) ?></h1>
    </div>
</header>
<?php if ($page['cover_image']): ?>
    <div class="wrap"><img class="cover" src="<?= e($page['cover_image']) ?>" alt="<?= e($page['title']) ?>" fetchpriority="high"></div>
<?php endif; ?>

<?php if ($page['cards']): /* secções "Título 2" seguidas lado a lado, como na página inicial */ ?>
    <?php foreach (home_blocks($page['content']) as [$kind, $body]): ?>
        <?php if ($kind === 'cards'): ?>
            <section class="wrap-wide cards cards-wide">
                <?php foreach ($body as $card): ?>
                    <article class="card prose"><?= $card ?></article>
                <?php endforeach; ?>
            </section>
        <?php else: ?>
            <section class="wrap prose page-block"><?= $body ?></section>
        <?php endif; ?>
    <?php endforeach; ?>
<?php else: ?>
    <article class="wrap">
        <div class="prose"><?= $page['content'] ?></div>
    </article>
<?php endif; ?>

<?php if ($page['contact_form']) {
    require __DIR__ . '/partials/contact-form.php';
} ?>
<div class="page-end"></div>
