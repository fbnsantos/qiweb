<?php
/*
 * Ajudas automáticas para motores de pesquisa e assistentes de IA (além do robots.txt e do sitemap.xml em site.php):
 *   /og.png      imagem de partilha 1200×630 com o título da página, para páginas sem imagem de capa
 *   /llms.txt    resumo do site em Markdown para ChatGPT, Perplexity e outros motores com IA
 *   IndexNow     avisa o Bing, Yandex, Seznam… quando uma página é publicada ou alterada
 */
declare(strict_types=1);

const OG_W = 1200;
const OG_H = 630;
const OG_FONT = ROOT . '/app/fonts/Inter-Medium.ttf';

/* ---------- Imagem de partilha (Open Graph) ---------- */

/** Precisa da extensão GD com FreeType; sem ela o site funciona na mesma, só não gera imagens. */
function og_available(): bool
{
    return function_exists('imagettftext') && is_file(OG_FONT);
}

/** Segredo gerado na primeira utilização e guardado nas definições. */
function generated_secret(string $name): string
{
    $secret = setting($name);
    if ($secret === '') {
        $secret = bin2hex(random_bytes(16));
        setting_set($name, $secret);
    }
    return $secret;
}

/** Cores do tema ativo (backoffice → Aspeto): ['bg' => '#…', 'surface', 'fg', 'accent']. */
function theme_palette(): array
{
    $theme = current_theme();
    $p = $theme === 'custom' ? theme_custom() : THEMES[$theme];
    return ['bg' => $p['bg'], 'surface' => $p['surface'], 'fg' => $p['fg'], 'accent' => $p['accent']];
}

/**
 * O URL é assinado para ninguém gerar imagens com texto arbitrário no domínio.
 * A assinatura inclui nome, lema e cores, para a imagem mudar quando estes mudam.
 */
function og_signature(string $title): string
{
    $state = implode('|', [$title, setting('site_name'), setting('site_tagline'), base_url(), ...array_values(theme_palette())]);
    return substr(hash_hmac('sha256', $state, generated_secret('og_secret')), 0, 20);
}

function og_image_url(string $title): string
{
    $title = mb_substr(trim($title), 0, 140);
    return '/og.png?' . http_build_query(['t' => $title, 's' => og_signature($title)]);
}

function serve_og_image(): void
{
    $title = is_string($_GET['t'] ?? null) ? $_GET['t'] : '';
    if (!og_available() || !hash_equals(og_signature($title), (string) ($_GET['s'] ?? ''))) {
        http_response_code(404);
        return;
    }
    $dir = ROOT . '/data/og';
    $file = $dir . '/' . og_signature($title) . '.png';
    if (!is_file($file)) {
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        imagepng(render_og_image($title), $file, 6);
    }
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=604800');
    header('Content-Length: ' . filesize($file));
    readfile($file);
}

function render_og_image(string $title): GdImage
{
    $p = theme_palette();
    $im = imagecreatetruecolor(OG_W, OG_H);
    imageantialias($im, true);
    $rgb = fn(string $h) => array_map('hexdec', str_split(substr($h, 1), 2));
    $color = fn(array $c) => imagecolorallocate($im, (int) $c[0], (int) $c[1], (int) $c[2]);
    // Mistura de duas cores (0 = só a primeira, 1 = só a segunda), para linhas e texto secundário.
    $mix = fn(string $a, string $b, float $t) => array_map(fn($x, $y) => (int) round($x + ($y - $x) * $t), $rgb($a), $rgb($b));

    $bg = $color($rgb($p['bg']));
    $fg = $color($rgb($p['fg']));
    $accent = $color($rgb($p['accent']));
    $line = $color($mix($p['bg'], $p['fg'], 0.09));
    $muted = $color($mix($p['bg'], $p['fg'], 0.6));

    imagefilledrectangle($im, 0, 0, OG_W, OG_H, $bg);
    // Grelha discreta, como no topo da página inicial.
    for ($x = 0; $x <= OG_W; $x += 60) {
        imageline($im, $x, 0, $x, OG_H, $line);
    }
    for ($y = 30; $y <= OG_H; $y += 60) {
        imageline($im, 0, $y, OG_W, $y, $line);
    }
    imagefilledrectangle($im, 0, OG_H - 14, OG_W, OG_H, $accent);

    // Raio do logótipo e nome do site no topo.
    $bolt = [14, 1, 4, 14, 10.5, 14, 9, 23, 20, 9, 13, 9];
    $points = [];
    foreach ($bolt as $i => $v) {
        $points[] = (int) round(($i % 2 === 0 ? 72 : 64) + $v * 2.2);
    }
    imagefilledpolygon($im, $points, $accent);
    imagettftext($im, 26, 0, 136, 102, $fg, OG_FONT, mb_strtoupper(setting('site_name')));

    // Título com quebras de linha (até 4 linhas).
    $size = mb_strlen($title) > 60 ? 52 : (mb_strlen($title) > 24 ? 66 : 92);
    $lines = wrap_ttf($title, OG_FONT, $size, OG_W - 160);
    if (count($lines) > 4) {
        $lines = array_slice($lines, 0, 4);
        $lines[3] = rtrim($lines[3], ' .,;:') . '…';
    }
    $lineH = (int) ($size * 1.25);
    $y = (int) (OG_H / 2 - (count($lines) * $lineH) / 2 + $size * 0.95);
    foreach ($lines as $l) {
        imagettftext($im, $size, 0, 80, $y, $fg, OG_FONT, $l);
        $y += $lineH;
    }

    // Lema e domínio em baixo.
    $tagline = setting('site_tagline');
    if ($tagline !== '' && $tagline !== $title) {
        foreach (array_slice(wrap_ttf($tagline, OG_FONT, 24, OG_W - 160), 0, 1) as $l) {
            imagettftext($im, 24, 0, 80, OG_H - 96, $accent, OG_FONT, $l);
        }
    }
    imagettftext($im, 20, 0, 80, OG_H - 52, $muted, OG_FONT, (string) parse_url(base_url(), PHP_URL_HOST));
    return $im;
}

function wrap_ttf(string $text, string $font, int $size, int $maxWidth): array
{
    $lines = [];
    $line = '';
    foreach (preg_split('/\s+/u', trim($text)) ?: [] as $word) {
        $try = $line === '' ? $word : "$line $word";
        $box = imagettfbbox($size, 0, $font, $try);
        if ($line !== '' && $box[2] - $box[0] > $maxWidth) {
            $lines[] = $line;
            $line = $word;
        } else {
            $line = $try;
        }
    }
    if ($line !== '') {
        $lines[] = $line;
    }
    return $lines;
}

/* ---------- IndexNow: avisa Bing, Yandex, Seznam… quando um URL muda ---------- */

function indexnow_key(): string
{
    return generated_secret('indexnow_key');
}

/** O IndexNow só faz sentido num domínio público e com a indexação permitida. */
function indexnow_active(): bool
{
    $host = (string) parse_url(base_url(), PHP_URL_HOST);
    return indexing_allowed() && $host !== '' && $host !== 'localhost' && !filter_var($host, FILTER_VALIDATE_IP);
}

/** Envia os URLs alterados ao IndexNow. Falhas são ignoradas (o sitemap continua a servir). */
function indexnow_ping(array $paths): void
{
    if (!indexnow_active()) {
        return;
    }
    $key = indexnow_key();
    $body = json_encode([
        'host' => (string) parse_url(base_url(), PHP_URL_HOST),
        'key' => $key,
        'keyLocation' => abs_url("/$key.txt"),
        'urlList' => array_values(array_unique(array_map('abs_url', $paths))),
    ]);
    @file_get_contents('https://api.indexnow.org/indexnow', false, stream_context_create(['http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json; charset=utf-8\r\n",
        'content' => $body,
        'timeout' => 4,
        'ignore_errors' => true,
    ]]));
}

/* ---------- llms.txt: resumo do site em Markdown para assistentes e motores de IA ---------- */

function llms_txt(): string
{
    $md = ['# ' . setting('site_name'), ''];
    if (setting('site_description') !== '') {
        $md[] = '> ' . setting('site_description');
        $md[] = '';
    }
    $home = find_page('home');
    if ($home) {
        // O primeiro parágrafo da página inicial dá o contexto que a descrição curta não dá.
        $intro = preg_match('#<p[^>]*>(.*?)</p>#si', $home['content'], $m) ? text_excerpt($m[1], 600) : '';
        if ($intro !== '') {
            $md[] = $intro;
            $md[] = '';
        }
    }
    $md[] = '## Pages';
    $md[] = '';
    $md[] = '- [Home](' . abs_url('/') . '): ' . ($home['meta_description'] ?? '' ?: setting('site_description'));
    $pages = db()->query("SELECT * FROM pages WHERE published = 1 AND noindex = 0 AND slug <> 'home'
        ORDER BY in_menu DESC, menu_order, title")->fetchAll();
    foreach ($pages as $p) {
        $md[] = '- [' . $p['title'] . '](' . abs_url('/' . $p['slug']) . '): ' . ($p['meta_description'] ?: text_excerpt($p['content'], 140));
    }
    // Páginas de serviços (as que têm formulário de contacto): lista o que se pode contratar.
    foreach ($pages as $p) {
        if (!$p['contact_form'] || !preg_match_all('#<h2[^>]*>(.*?)</h2>\s*(?:<p[^>]*>(.*?)</p>)?#si', $p['content'], $m, PREG_SET_ORDER)) {
            continue;
        }
        $md[] = '';
        $md[] = '## ' . $p['title'];
        $md[] = '';
        foreach ($m as $section) {
            // Tira emojis e símbolos do início do título.
            $name = trim(preg_replace('/^[^\p{L}\p{N}]+/u', '', text_excerpt($section[1], 120)) ?? '');
            $about = isset($section[2]) ? text_excerpt($section[2], 200) : '';
            // Secções sem frase de apresentação (ex.: "How it works") não são serviços.
            if ($name !== '' && $about !== '') {
                $md[] = "- **$name**: $about";
            }
        }
        $md[] = '';
        $md[] = 'Contact: ' . abs_url('/' . $p['slug']) . '#contact';
    }
    return implode("\n", $md) . "\n";
}
