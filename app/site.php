<?php
declare(strict_types=1);

function route_site(string $path): void
{
    if ($path === '/contact') {
        // Só recebe o envio do formulário de contacto; não é uma página.
        ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' ? contact_submit() : redirect('/', 302);
        return;
    }
    switch ($path) {
        case '/':
            show_page('home');
            return;
        case '/home':
            redirect('/', 301);
        case '/robots.txt':
            header('Content-Type: text/plain; charset=utf-8');
            echo robots_txt();
            return;
        case '/sitemap.xml':
            header('Content-Type: application/xml; charset=utf-8');
            echo sitemap_xml();
            return;
        case '/llms.txt':
            header('Content-Type: text/plain; charset=utf-8');
            echo llms_txt();
            return;
        case '/og.png':
            serve_og_image();
            return;
        case '/favicon.svg':
        case '/favicon.ico':
            header('Content-Type: image/svg+xml');
            header('Cache-Control: public, max-age=604800');
            echo favicon_svg();
            return;
    }
    // Chave do IndexNow: o Bing confirma que o site é nosso pedindo /<chave>.txt.
    if (preg_match('#^/([a-f0-9]{32})\.txt$#', $path, $m) && hash_equals(indexnow_key(), $m[1])) {
        header('Content-Type: text/plain; charset=utf-8');
        echo $m[1];
        return;
    }
    if (preg_match('#^/([a-z0-9_-]+)$#', $path, $m)) {
        show_page($m[1]);
        return;
    }
    not_found();
}

function show_page(string $slug, array $form = []): void
{
    $page = find_page($slug);
    // Rascunhos só se veem com sessão iniciada no backoffice (pré-visualização).
    $preview = $page && !$page['published'] && current_user_id();
    if (!$page || (!$page['published'] && !$preview)) {
        not_found();
        return;
    }
    $isHome = $slug === 'home';
    if (!$preview) {
        track_view($isHome ? 'home' : 'page', (int) $page['id']);
    }
    $meta = [
        'og_title' => $page['title'],
        'title' => $isHome
            ? setting('site_name') . (setting('site_tagline') ? ' — ' . setting('site_tagline') : '')
            : $page['title'] . ' — ' . setting('site_name'),
        'description' => $page['meta_description'] ?: ($isHome ? setting('site_description') : '') ?: text_excerpt($page['content']),
        'path' => $isHome ? '/' : '/' . $slug,
        'image' => $page['cover_image'],
        'noindex' => (bool) $page['noindex'] || $preview,
    ];
    render($isHome ? 'home' : 'page', compact('page', 'meta', 'preview', 'form'));
}

function not_found(): void
{
    http_response_code(404);
    track_view('404');
    render('404', ['meta' => ['title' => 'Page not found — ' . setting('site_name'), 'noindex' => true]]);
}

/**
 * Divide o conteúdo da página inicial em blocos para o desenho da página:
 * uma linha horizontal separa blocos; dentro de cada bloco, duas ou mais secções "Título 2"
 * seguidas aparecem lado a lado como cartões. Devolve [['prose', html] | ['cards', [html, …]], …].
 */
function home_blocks(string $html): array
{
    $blocks = [];
    foreach (preg_split('#<hr\s*/?>#i', $html) ?: [] as $chunk) {
        $intro = '';
        $cards = [];
        foreach (preg_split('#(?=<h2[\s>])#i', trim($chunk)) ?: [] as $part) {
            if (trim($part) === '') {
                continue;
            }
            if (preg_match('#^<h2[\s>]#i', $part)) {
                $cards[] = $part;
            } else {
                $intro .= $part;
            }
        }
        if ($intro !== '') {
            $blocks[] = ['prose', $intro];
        }
        if (count($cards) >= 2) {
            $blocks[] = ['cards', $cards];
        } elseif ($cards) {
            $blocks[] = ['prose', $cards[0]];
        }
    }
    return $blocks;
}

/* ---------- SEO ---------- */

/** Tags do <head>: título, descrição, canonical, robots, Open Graph. */
function seo_head(array $m): string
{
    $title = $m['title'] ?? setting('site_name');
    $desc = $m['description'] ?? '';
    $noindex = !empty($m['noindex']) || !indexing_allowed();
    $out = '<title>' . e($title) . "</title>\n";
    if ($desc !== '') {
        $out .= '    <meta name="description" content="' . e($desc) . "\">\n";
    }
    $out .= '    <meta name="robots" content="' . ($noindex ? 'noindex, nofollow' : 'index, follow, max-image-preview:large') . "\">\n";
    if (isset($m['path'])) {
        $url = abs_url($m['path']);
        $out .= '    <link rel="canonical" href="' . e($url) . "\">\n";
        $out .= '    <meta property="og:url" content="' . e($url) . "\">\n";
    }
    $out .= '    <meta property="og:type" content="website">' . "\n";
    $out .= '    <meta property="og:site_name" content="' . e(setting('site_name')) . "\">\n";
    $out .= '    <meta property="og:title" content="' . e($title) . "\">\n";
    if ($desc !== '') {
        $out .= '    <meta property="og:description" content="' . e($desc) . "\">\n";
    }
    $image = ($m['image'] ?? '') ?: setting('default_image');
    if ($image === '' && og_available()) {
        // Sem capa: imagem 1200×630 gerada com o título da página.
        $image = og_image_url($m['og_title'] ?? $title);
        $out .= '    <meta property="og:image:width" content="' . OG_W . "\">\n";
        $out .= '    <meta property="og:image:height" content="' . OG_H . "\">\n";
    }
    if ($image !== '') {
        $out .= '    <meta property="og:image" content="' . e(abs_url($image)) . "\">\n";
        $out .= '    <meta name="twitter:card" content="summary_large_image">' . "\n";
    }
    if (setting('google_site_verification')) {
        $out .= '    <meta name="google-site-verification" content="' . e(setting('google_site_verification')) . "\">\n";
    }
    if (($m['path'] ?? '') === '/') {
        $schema = ['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => setting('site_name'),
            'url' => abs_url('/'), 'description' => setting('site_description')];
        $out .= '    <script type="application/ld+json">' . json_encode(array_filter($schema), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) . "</script>\n";
    }
    return $out . '    <link rel="icon" href="/favicon.svg" type="image/svg+xml">' . "\n";
}

function robots_txt(): string
{
    if (!indexing_allowed()) {
        return "User-agent: *\nDisallow: /\n";
    }
    return "User-agent: *\nDisallow: /admin\n\nSitemap: " . abs_url('/sitemap.xml') . "\n";
}

function sitemap_xml(): string
{
    $out = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
        . ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
    if (indexing_allowed()) {
        foreach (db()->query("SELECT * FROM pages WHERE published = 1 AND noindex = 0 ORDER BY slug <> 'home', slug") as $p) {
            $out .= '  <url><loc>' . xml(abs_url($p['slug'] === 'home' ? '/' : '/' . $p['slug'])) . '</loc><lastmod>'
                . date('Y-m-d', strtotime($p['updated_at'])) . '</lastmod>';
            // Imagem de capa e imagens do texto, para o Google Imagens.
            $images = array_filter(array_merge([$p['cover_image']], content_images($p['content'])));
            foreach (array_slice(array_unique($images), 0, 20) as $src) {
                $out .= '<image:image><image:loc>' . xml(abs_url($src)) . '</image:loc></image:image>';
            }
            $out .= "</url>\n";
        }
    }
    return $out . "</urlset>\n";
}

function favicon_svg(): string
{
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
        . '<rect width="64" height="64" rx="14" fill="#0a0d0c"/>'
        . '<path d="M36 8 16 36h13l-3 20 22-30H34z" fill="#c6f432"/></svg>';
}
