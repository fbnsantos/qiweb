<?php
declare(strict_types=1);

function config(string $key, mixed $default = null): mixed
{
    global $CONFIG;
    return $CONFIG[$key] ?? $default;
}

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function xml(string $s): string
{
    return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

function base_url(): string
{
    $url = getenv('SITE_URL') ?: (string) config('base_url', '');
    if ($url === '') {
        $url = (is_https() ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
    return rtrim($url, '/');
}

/** Converte um caminho relativo (/pagina) num URL absoluto. */
function abs_url(string $path): string
{
    if ($path === '') {
        return '';
    }
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return base_url() . '/' . ltrim($path, '/');
}

function redirect(string $to, int $code = 302): never
{
    header('Location: ' . $to, true, $code);
    exit;
}

function settings(bool $reload = false): array
{
    static $cache = null;
    if ($cache === null || $reload) {
        $cache = [];
        foreach (db()->query('SELECT name, value FROM settings') as $row) {
            $cache[$row['name']] = (string) $row['value'];
        }
    }
    return $cache;
}

function setting(string $name, string $default = ''): string
{
    $v = settings()[$name] ?? '';
    return $v !== '' ? $v : $default;
}

function setting_set(string $name, string $value): void
{
    db()->prepare('INSERT INTO settings (name, value) VALUES (?, ?)
        ON CONFLICT(name) DO UPDATE SET value = excluded.value')->execute([$name, $value]);
    settings(true);
}

/** O Google pode indexar o site? (Definições) */
function indexing_allowed(): bool
{
    return setting('allow_indexing', '1') === '1';
}

/** Linhas "Texto | URL" (ex.: ligações do rodapé) => [texto => url]. */
function link_lines(string $text): array
{
    $out = [];
    foreach (preg_split('/\R/', $text) ?: [] as $line) {
        $parts = array_map('trim', explode('|', $line, 2));
        if (count($parts) === 2 && $parts[0] !== '' && safe_url($parts[1], true)) {
            $out[$parts[0]] = $parts[1];
        }
    }
    return $out;
}

function render(string $view, array $vars = [], string $layout = 'layout'): void
{
    extract($vars);
    ob_start();
    require ROOT . "/app/views/$view.php";
    $content = ob_get_clean();
    require ROOT . "/app/views/$layout.php";
}

/** Endereço de página: minúsculas, algarismos, "-" e "_" (o "_" mantém endereços antigos como pkmt_manual). */
function slugify(string $s): string
{
    $map = [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'ñ' => 'n', 'ß' => 'ss', 'æ' => 'ae', 'œ' => 'oe',
    ];
    $s = strtr(mb_strtolower(trim($s), 'UTF-8'), $map);
    $s = preg_replace('/[^a-z0-9_]+/', '-', $s) ?? '';
    return substr(trim($s, '-'), 0, 80);
}

/** Texto simples a partir de HTML, cortado numa fronteira de palavra. */
function text_excerpt(string $html, int $len = 160): string
{
    $text = html_entity_decode(strip_tags(str_replace('<', ' <', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    $text = preg_replace('/ ([,.;:!?])/u', '$1', $text) ?? $text; // espaço deixado por tags antes da pontuação
    if (mb_strlen($text) <= $len) {
        return $text;
    }
    $cut = mb_substr($text, 0, $len - 1);
    $space = mb_strrpos($cut, ' ');
    if ($space !== false && $space > $len * 0.6) {
        $cut = mb_substr($cut, 0, $space);
    }
    return rtrim($cut, " ,.;:-") . '…';
}

/** Data por extenso em português (usada só no backoffice). */
function format_date(?string $datetime): string
{
    if (!$datetime) {
        return '';
    }
    $t = strtotime($datetime);
    $months = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho',
        'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
    return date('j', $t) . ' de ' . $months[(int) date('n', $t) - 1] . ' de ' . date('Y', $t);
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

/* ---------- Sessão, autenticação e CSRF (só usados no backoffice) ---------- */

const SESSION_NAME = 'qiweb_admin';

function session_boot(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Utilizador autenticado, sem abrir sessão para visitantes anónimos. */
function current_user_id(): ?int
{
    if (session_status() !== PHP_SESSION_ACTIVE && !isset($_COOKIE[SESSION_NAME])) {
        return null;
    }
    session_boot();
    return isset($_SESSION['uid']) ? (int) $_SESSION['uid'] : null;
}

function csrf_token(): string
{
    session_boot();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('Pedido inválido (CSRF). Volte atrás e recarregue a página.');
    }
}

function flash(?string $msg = null, string $type = 'ok'): ?array
{
    session_boot();
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
