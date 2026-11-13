<?php
/*
 * Aspeto do site público (backoffice → Aspeto).
 * O assets/site.css desenha tudo com variáveis CSS; o tema escolhido só muda os valores dessas variáveis:
 *   dark   — o original (escuro, com verde elétrico); são os valores por omissão do site.css
 *   light  — brancos e cinzentos
 *   custom — cores, letra e cantos escolhidos no backoffice
 */
declare(strict_types=1);

const THEMES = [
    'dark' => ['name' => 'Escuro', 'hint' => 'O original: fundo quase preto com verde elétrico.',
        'bg' => '#0a0d0c', 'surface' => '#111615', 'fg' => '#e9eeec', 'accent' => '#c6f432'],
    'light' => ['name' => 'Claro', 'hint' => 'Brancos e cinzentos, sóbrio.',
        'bg' => '#ffffff', 'surface' => '#f4f5f6', 'fg' => '#1f2328', 'accent' => '#1f2328'],
    'custom' => ['name' => 'Personalizado', 'hint' => 'Escolha as cores, a letra e os cantos.',
        'bg' => '#f7f8fa', 'surface' => '#ffffff', 'fg' => '#1b2430', 'accent' => '#0b6bcb'],
];

const THEME_FONTS = [
    'sans' => ['Sem serifa (moderna)', 'ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif'],
    'serif' => ['Com serifa (clássica)', 'Georgia, "Iowan Old Style", "Times New Roman", serif'],
    'mono' => ['Monoespaçada (técnica)', 'ui-monospace, "SF Mono", "Cascadia Mono", Menlo, Consolas, monospace'],
];

const THEME_COLOR_FIELDS = [
    'bg' => 'Fundo da página',
    'surface' => 'Fundo dos cartões',
    'fg' => 'Texto',
    'accent' => 'Cor de destaque (ligações, marcadores, botões)',
];

function current_theme(): string
{
    $t = setting('theme', 'dark');
    return isset(THEMES[$t]) ? $t : 'dark';
}

/** Cor #rrggbb válida, ou o valor por omissão. */
function hex_color(string $value, string $default): string
{
    $value = strtolower(trim($value));
    return preg_match('/^#[0-9a-f]{6}$/', $value) ? $value : $default;
}

/** Luminância relativa (WCAG) acima de ~0,4: sobre esta cor lê-se melhor texto escuro. */
function color_is_light(string $hex): bool
{
    $lin = function (int $c): float {
        $c /= 255;
        return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
    };
    [$r, $g, $b] = array_map('hexdec', str_split(substr($hex, 1), 2));
    return 0.2126 * $lin((int) $r) + 0.7152 * $lin((int) $g) + 0.0722 * $lin((int) $b) > 0.4;
}

/** Opções do tema personalizado, já validadas. */
function theme_custom(): array
{
    $d = THEMES['custom'];
    $font = setting('theme_font', 'sans');
    return [
        'bg' => hex_color(setting('theme_bg'), $d['bg']),
        'surface' => hex_color(setting('theme_surface'), $d['surface']),
        'fg' => hex_color(setting('theme_fg'), $d['fg']),
        'accent' => hex_color(setting('theme_accent'), $d['accent']),
        'font' => isset(THEME_FONTS[$font]) ? $font : 'sans',
        'radius' => max(0, min(28, (int) setting('theme_radius', '14'))),
        'grid' => setting('theme_grid', '1') === '1',
    ];
}

/** Variáveis CSS de um tema: ['--bg' => '#fff', …]. As do tema escuro são as do site.css (lista vazia). */
function theme_vars(string $theme, ?array $custom = null): array
{
    if ($theme === 'light') {
        return [
            'color-scheme' => 'light',
            '--bg' => '#ffffff', '--surface' => '#f4f5f6', '--line' => '#e2e5e8',
            '--fg' => '#1f2328', '--muted' => '#69717a', '--strong' => '#000000',
            '--volt' => '#1f2328', '--volt-ink' => '#ffffff', '--grid-opacity' => '0.9',
        ];
    }
    if ($theme === 'custom') {
        $c = $custom ?? theme_custom();
        return [
            'color-scheme' => color_is_light($c['bg']) ? 'light' : 'dark',
            '--bg' => $c['bg'], '--surface' => $c['surface'], '--fg' => $c['fg'], '--strong' => $c['fg'],
            // Linhas e texto secundário derivam do texto e do fundo, para combinarem sempre.
            '--line' => "color-mix(in srgb, {$c['fg']} 15%, {$c['bg']})",
            '--muted' => "color-mix(in srgb, {$c['fg']} 64%, {$c['bg']})",
            '--volt' => $c['accent'],
            '--volt-ink' => color_is_light($c['accent']) ? '#0a0d0c' : '#ffffff',
            '--radius' => $c['radius'] . 'px',
            '--sans' => THEME_FONTS[$c['font']][1],
            '--grid-opacity' => $c['grid'] ? '0.7' : '0',
        ];
    }
    return [];
}

function css_vars(array $vars): string
{
    $out = '';
    foreach ($vars as $name => $value) {
        $out .= "$name:$value;";
    }
    return $out;
}

/** CSS a juntar ao site.css para o tema ativo ('' no tema escuro). Só contém valores validados. */
function theme_css(): string
{
    $vars = theme_vars(current_theme());
    return $vars ? ':root{' . css_vars($vars) . '}' : '';
}
