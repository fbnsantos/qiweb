<?php
$current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$footerLinks = link_lines(setting('footer_links'));
?><!doctype html>
<html lang="<?= e(setting('site_lang', 'en')) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= seo_head($meta ?? []) ?>
    <link rel="stylesheet" href="/assets/site.css?v=<?= filemtime(ROOT . '/assets/site.css') ?>">
<?php if ($themeCss = theme_css()): ?>    <style><?= $themeCss ?></style>
<?php endif; ?>
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<header class="site-header">
    <div class="wrap-wide">
        <a class="brand" href="/">
            <svg class="brand-mark" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 1 4 14h6.5L9 23l11-14h-7z"/></svg>
            <?= e(setting('site_name')) ?>
        </a>
        <nav aria-label="Main">
            <ul>
                <li><a href="/"<?= $current === '/' ? ' aria-current="page"' : '' ?>>Home</a></li>
                <?php foreach (menu_pages() as $item): $href = '/' . $item['slug']; ?>
                    <li><a href="<?= e($href) ?>"<?= $current === $href ? ' aria-current="page"' : '' ?>><?= e($item['menu_label'] ?: $item['title']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</header>

<main id="main">
<?= $content ?>
</main>

<footer class="site-footer">
    <div class="wrap-wide">
        <div>
            <p class="footer-name"><?= e(setting('site_name')) ?></p>
            <?php if (setting('site_tagline')): ?><p class="muted"><?= e(setting('site_tagline')) ?></p><?php endif; ?>
        </div>
        <?php if ($footerLinks): ?>
            <ul class="footer-links">
                <?php foreach ($footerLinks as $label => $url): $external = (bool) preg_match('#^https?://#i', $url); ?>
                    <li><a href="<?= e($url) ?>"<?= $external ? ' target="_blank" rel="noopener"' : '' ?>><?= e($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <div class="wrap-wide footer-bottom">
        <span><?= setting('footer_text') !== '' ? e(setting('footer_text')) : '© ' . date('Y') . ' ' . e(setting('site_name')) ?></span>
        <a class="footer-admin" href="/admin" rel="nofollow">Admin</a>
    </div>
</footer>
</body>
</html>
