<?php
declare(strict_types=1);

const RESERVED_SLUGS = ['admin', 'home', 'index', 'sitemap', 'robots', 'assets', 'uploads', 'favicon', 'app', 'data', 'git', 'config', 'contact'];
const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_WINDOW = 900; // 15 minutos

/** Textos do topo da página inicial, guardados nas definições e editados no ecrã da página inicial. */
const HERO_FIELDS = ['hero_eyebrow', 'site_tagline', 'hero_sub', 'hero_cta_label', 'hero_cta_url'];

function route_admin(string $sub, string $method): void
{
    session_boot();
    header('X-Robots-Tag: noindex, nofollow');
    header('Cache-Control: no-store');

    if ($method === 'POST') {
        csrf_check();
    }
    if ($sub === '/setup') {
        admin_setup($method);
        return;
    }
    if (user_count() === 0) {
        redirect('/admin/setup');
    }
    if ($sub === '/login') {
        admin_login($method);
        return;
    }
    if (!current_user_id()) {
        redirect('/admin/login');
    }

    if ($sub === '/') {
        admin_dashboard();
    } elseif ($sub === '/logout' && $method === 'POST') {
        session_destroy();
        redirect('/admin/login');
    } elseif ($sub === '/home') {
        redirect('/admin/pages/' . (int) (find_page('home')['id'] ?? 0));
    } elseif ($sub === '/pages') {
        admin_list();
    } elseif (preg_match('#^/pages/(new|\d+)$#', $sub, $m)) {
        $id = $m[1] === 'new' ? null : (int) $m[1];
        $method === 'POST' ? admin_save($id) : admin_edit($id);
    } elseif (preg_match('#^/pages/(\d+)/delete$#', $sub, $m) && $method === 'POST') {
        // A página inicial não pode ser apagada.
        db()->prepare("DELETE FROM pages WHERE id = ? AND slug <> 'home'")->execute([(int) $m[1]]);
        flash('Página apagada.');
        redirect('/admin/pages');
    } elseif ($sub === '/media') {
        admin_render('media', ['title' => 'Imagens e ficheiros', 'items' => db()->query('SELECT * FROM media ORDER BY id DESC')->fetchAll()]);
    } elseif ($sub === '/media/upload' && $method === 'POST') {
        admin_media_upload();
    } elseif ($sub === '/media.json') {
        header('Content-Type: application/json');
        $rows = db()->query("SELECT path, original_name, alt FROM media WHERE mime LIKE 'image/%' ORDER BY id DESC LIMIT 200")->fetchAll();
        echo json_encode(array_map(fn($r) => ['title' => $r['alt'] ?: $r['original_name'], 'value' => $r['path']], $rows));
    } elseif (preg_match('#^/media/(\d+)$#', $sub, $m) && $method === 'POST') {
        db()->prepare('UPDATE media SET alt = ? WHERE id = ?')->execute([trim($_POST['alt'] ?? ''), (int) $m[1]]);
        flash('Texto alternativo guardado.');
        redirect('/admin/media');
    } elseif (preg_match('#^/media/(\d+)/delete$#', $sub, $m) && $method === 'POST') {
        delete_media((int) $m[1]);
        flash('Ficheiro apagado.');
        redirect('/admin/media');
    } elseif ($sub === '/contacts') {
        admin_render('contacts', ['title' => 'Contactos',
            'items' => db()->query('SELECT * FROM contacts ORDER BY handled, id DESC LIMIT 500')->fetchAll()]);
    } elseif (preg_match('#^/contacts/(\d+)/(handled|reopen|delete)$#', $sub, $m) && $method === 'POST') {
        match ($m[2]) {
            'handled' => db()->prepare('UPDATE contacts SET handled = 1 WHERE id = ?')->execute([(int) $m[1]]),
            'reopen' => db()->prepare('UPDATE contacts SET handled = 0 WHERE id = ?')->execute([(int) $m[1]]),
            'delete' => db()->prepare('DELETE FROM contacts WHERE id = ?')->execute([(int) $m[1]]),
        };
        flash($m[2] === 'delete' ? 'Contacto apagado.' : ($m[2] === 'handled' ? 'Marcado como tratado.' : 'Reaberto.'));
        redirect('/admin/contacts');
    } elseif ($sub === '/appearance') {
        admin_appearance($method);
    } elseif ($sub === '/settings') {
        admin_settings($method);
    } elseif ($sub === '/password') {
        admin_password($method);
    } elseif ($sub === '/update') {
        admin_update($method);
    } elseif ($sub === '/stats') {
        $days = (int) ($_GET['dias'] ?? 30);
        $days = in_array($days, [7, 30, 90, 365], true) ? $days : 30;
        admin_render('stats', ['title' => 'Estatísticas', 'r' => stats_report($days), 'geoip' => geoip_info()]);
    } elseif ($sub === '/stats/geoip' && $method === 'POST') {
        $error = geoip_update();
        $info = geoip_info();
        $error === null
            ? flash('Base de países atualizada (DB-IP ' . ($info['month'] ?? '') . '). As próximas visitas já mostram o país.')
            : flash($error, 'error');
        redirect('/admin/stats');
    } else {
        http_response_code(404);
        admin_render('message', ['title' => 'Não encontrado', 'message' => 'Esta página do backoffice não existe.']);
    }
}

function admin_render(string $view, array $vars = []): void
{
    render("admin/$view", $vars, 'admin/layout');
}

/* ---------- Atualizar o site a partir do GitHub (mesmo código do git.php) ---------- */

function admin_update(string $method): void
{
    global $CONFIG;
    require_once ROOT . '/app/deploy.php';
    $config = (array) $CONFIG;
    $result = null;
    if ($method === 'POST') {
        $result = deploy_run($config, 'backoffice');
    }
    admin_render('update', [
        'title' => 'Atualizar site',
        'branch' => deploy_branch($config),
        'version' => deploy_current_version($config),
        'log' => deploy_recent_log(),
        'result' => $result,
    ]);
}

/* ---------- Autenticação ---------- */

/** true se este IP já falhou demasiadas vezes (login e setup). */
function too_many_attempts(): bool
{
    db()->prepare('DELETE FROM login_attempts WHERE attempted_at < ?')->execute([time() - LOGIN_WINDOW]);
    $st = db()->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ?');
    $st->execute([client_ip()]);
    return (int) $st->fetchColumn() >= LOGIN_MAX_ATTEMPTS;
}

function record_failed_attempt(): void
{
    db()->prepare('INSERT INTO login_attempts (ip, attempted_at) VALUES (?, ?)')->execute([client_ip(), time()]);
}

function admin_setup(string $method): void
{
    if (user_count() > 0) {
        redirect('/admin/login');
    }
    $key = (string) config('setup_key', '');
    $error = null;
    if ($key === '' || $key === 'mude-isto') {
        admin_render('message', ['title' => 'Configuração inicial', 'bare' => true,
            'message' => 'Defina um "setup_key" próprio em config.php (copie config.sample.php) antes de criar o administrador.']);
        return;
    }
    if ($method === 'POST') {
        $user = trim($_POST['username'] ?? '');
        $pass = (string) ($_POST['password'] ?? '');
        if (too_many_attempts()) {
            $error = 'Demasiadas tentativas. Tente de novo dentro de 15 minutos.';
        } elseif (!hash_equals($key, (string) ($_POST['setup_key'] ?? ''))) {
            record_failed_attempt();
            $error = 'Chave de configuração errada.';
        } elseif (!preg_match('/^[A-Za-z0-9_.@-]{3,64}$/', $user)) {
            $error = 'Nome de utilizador inválido (3–64 caracteres, sem espaços).';
        } elseif (mb_strlen($pass) < 10) {
            $error = 'A palavra-passe deve ter pelo menos 10 caracteres.';
        } else {
            db()->prepare('INSERT INTO users (username, password_hash, created_at) VALUES (?, ?, ?)')
                ->execute([$user, password_hash($pass, PASSWORD_DEFAULT), now()]);
            session_regenerate_id(true);
            $_SESSION['uid'] = (int) db()->lastInsertId();
            flash('Administrador criado. Bem-vindo!');
            redirect('/admin');
        }
    }
    admin_render('setup', ['title' => 'Configuração inicial', 'error' => $error, 'bare' => true]);
}

function admin_login(string $method): void
{
    if (current_user_id()) {
        redirect('/admin');
    }
    $error = null;
    if ($method === 'POST') {
        if (too_many_attempts()) {
            $error = 'Demasiadas tentativas. Tente de novo dentro de 15 minutos.';
        } else {
            $st = db()->prepare('SELECT * FROM users WHERE username = ?');
            $st->execute([trim($_POST['username'] ?? '')]);
            $user = $st->fetch();
            // Verifica sempre um hash (mesmo sem utilizador) para o tempo de resposta não revelar se o nome existe.
            $hash = $user['password_hash'] ?? '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
            if (password_verify((string) ($_POST['password'] ?? ''), $hash) && $user) {
                db()->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([client_ip()]);
                if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                    db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
                        ->execute([password_hash((string) $_POST['password'], PASSWORD_DEFAULT), $user['id']]);
                }
                session_regenerate_id(true);
                $_SESSION['uid'] = (int) $user['id'];
                redirect('/admin');
            }
            record_failed_attempt();
            $error = 'Utilizador ou palavra-passe errados.';
        }
    }
    admin_render('login', ['title' => 'Entrar', 'error' => $error, 'bare' => true]);
}

function admin_password(string $method): void
{
    $error = null;
    if ($method === 'POST') {
        $st = db()->prepare('SELECT * FROM users WHERE id = ?');
        $st->execute([current_user_id()]);
        $user = $st->fetch();
        $new = (string) ($_POST['new'] ?? '');
        if (!password_verify((string) ($_POST['current'] ?? ''), $user['password_hash'])) {
            $error = 'A palavra-passe atual está errada.';
        } elseif (mb_strlen($new) < 10) {
            $error = 'A nova palavra-passe deve ter pelo menos 10 caracteres.';
        } elseif ($new !== ($_POST['confirm'] ?? '')) {
            $error = 'As palavras-passe não coincidem.';
        } else {
            db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            session_regenerate_id(true);
            flash('Palavra-passe alterada.');
            redirect('/admin');
        }
    }
    admin_render('password', ['title' => 'Palavra-passe', 'error' => $error]);
}

/* ---------- Painel ---------- */

function admin_dashboard(): void
{
    global $CONFIG;
    require_once ROOT . '/app/deploy.php';
    $today = date('Y-m-d');
    $st = db()->prepare('SELECT COUNT(DISTINCT day || visitor) FROM page_views WHERE day >= ?');
    $st->execute([date('Y-m-d', strtotime('-29 days'))]);
    $counts = [
        'pages' => (int) db()->query('SELECT COUNT(*) FROM pages')->fetchColumn(),
        'media' => (int) db()->query('SELECT COUNT(*) FROM media')->fetchColumn(),
        'contacts' => new_contacts_count(),
        'today' => stats_views_since($today),
        'views30' => stats_views_since(date('Y-m-d', strtotime('-29 days'))),
        'visitors30' => (int) $st->fetchColumn(),
    ];
    $recent = db()->query('SELECT id, slug, title, updated_at FROM pages ORDER BY updated_at DESC LIMIT 6')->fetchAll();
    $version = deploy_current_version((array) $CONFIG);
    $checks = [];
    if (!indexing_allowed()) {
        $checks[] = ['warn', 'A indexação está desativada: o robots.txt bloqueia o Google. Ative-a em Definições quando o site estiver pronto.'];
    }
    if (str_contains(base_url(), 'localhost') || str_contains(base_url(), '127.0.0.1')) {
        $checks[] = ['info', 'O URL do site é ' . base_url() . '. Em produção, defina base_url em config.php (https://qivolt.com).'];
    }
    if (strlen((string) config('deploy_secret', '')) < 20) {
        $checks[] = ['info', 'O deploy automático (webhook do GitHub em /git.php) está desligado: falta o deploy_secret em config.php. O botão "Atualizar" funciona na mesma.'];
    }
    admin_render('dashboard', ['title' => 'Painel'] + compact('counts', 'recent', 'version', 'checks'));
}

/* ---------- Páginas ---------- */

function admin_list(): void
{
    $items = db()->query("SELECT * FROM pages ORDER BY slug <> 'home', in_menu DESC, menu_order, title")->fetchAll();
    $views = stats_views_by_content('page');
    // A página inicial é registada como "home".
    foreach (stats_views_by_content('home') as $id => $count) {
        $views[$id] = ($views[$id] ?? 0) + $count;
    }
    admin_render('list', ['title' => 'Páginas'] + compact('items', 'views'));
}

function empty_page(): array
{
    return [
        'id' => null, 'slug' => '', 'title' => '', 'menu_label' => '', 'meta_description' => '', 'content' => '',
        'cover_image' => '', 'published' => 1, 'noindex' => 0, 'in_menu' => 1, 'menu_order' => 0, 'contact_form' => 0, 'cards' => 0,
    ];
}

function admin_edit(?int $id, ?array $item = null, array $errors = []): void
{
    if ($item === null) {
        if ($id === null) {
            $item = empty_page();
        } else {
            $st = db()->prepare('SELECT * FROM pages WHERE id = ?');
            $st->execute([$id]);
            $item = $st->fetch();
            if (!$item) {
                flash('Página não encontrada.', 'error');
                redirect('/admin/pages');
            }
        }
    }
    $isHome = $item['slug'] === 'home' && $id !== null;
    $hero = [];
    foreach (HERO_FIELDS as $k) {
        $hero[$k] = (string) ($_POST[$k] ?? setting($k));
    }
    $title = $isHome ? 'Página inicial' : ($id ? 'Editar página' : 'Nova página');
    admin_render('edit', compact('item', 'errors', 'title', 'isHome', 'hero'));
}

function admin_save(?int $id): void
{
    $in = fn(string $k) => trim((string) ($_POST[$k] ?? ''));
    $current = null;
    if ($id) {
        $st = db()->prepare('SELECT * FROM pages WHERE id = ?');
        $st->execute([$id]);
        $current = $st->fetch();
        if (!$current) {
            flash('Página não encontrada.', 'error');
            redirect('/admin/pages');
        }
    }
    $isHome = $current && $current['slug'] === 'home';
    $item = [
        'title' => $in('title'),
        'slug' => $isHome ? 'home' : slugify($in('slug') ?: $in('title')),
        'menu_label' => mb_substr($in('menu_label'), 0, 40),
        'meta_description' => $in('meta_description'),
        'content' => sanitize_html((string) ($_POST['content'] ?? '')),
        'cover_image' => $in('cover_image'),
        // A página inicial está sempre publicada.
        'published' => $isHome || isset($_POST['published']) ? 1 : 0,
        'noindex' => isset($_POST['noindex']) ? 1 : 0,
        'in_menu' => $isHome || isset($_POST['in_menu']) ? 1 : 0,
        'menu_order' => (int) $in('menu_order'),
        'contact_form' => isset($_POST['contact_form']) ? 1 : 0,
        'cards' => $isHome || isset($_POST['cards']) ? 1 : 0,
    ];

    $errors = [];
    if ($item['title'] === '') {
        $errors[] = 'O título é obrigatório.';
    }
    if ($item['slug'] === '') {
        $errors[] = 'O endereço é obrigatório.';
    }
    if (!$isHome && in_array($item['slug'], RESERVED_SLUGS, true)) {
        $errors[] = "O endereço \"/{$item['slug']}\" está reservado. Escolha outro.";
    }
    $st = db()->prepare('SELECT id FROM pages WHERE slug = ? AND id IS NOT ?');
    $st->execute([$item['slug'], $id]);
    if ($st->fetchColumn()) {
        $errors[] = 'Já existe outra página com este endereço.';
    }
    if ($item['cover_image'] !== '' && !safe_url($item['cover_image'], false)) {
        $errors[] = 'A imagem de capa deve ser um URL (https://…) ou um caminho começado por /.';
    }
    if (!$errors && !empty($_FILES['cover_file']['name'])) {
        try {
            $item['cover_image'] = handle_upload($_FILES['cover_file'], $item['title'])['url'];
        } catch (RuntimeException $ex) {
            $errors[] = 'Capa: ' . $ex->getMessage();
        }
    }
    if ($errors) {
        admin_edit($id, ['id' => $id] + $item, $errors);
        return;
    }

    $item['updated_at'] = now();
    if ($id) {
        $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($item)));
        db()->prepare("UPDATE pages SET $sets WHERE id = :id")->execute($item + ['id' => $id]);
    } else {
        $item['created_at'] = $item['updated_at'];
        $cols = array_keys($item);
        db()->prepare('INSERT INTO pages (' . implode(', ', $cols) . ') VALUES (:' . implode(', :', $cols) . ')')->execute($item);
        $id = (int) db()->lastInsertId();
    }
    if ($isHome) {
        foreach (HERO_FIELDS as $k) {
            setting_set($k, mb_substr($in($k), 0, 300));
        }
    }
    flash('Guardado.');
    redirect("/admin/pages/$id");
}

function public_url(array $item): string
{
    return $item['slug'] === 'home' ? '/' : '/' . $item['slug'];
}

/* ---------- Média ---------- */

/** Usado pelo formulário da biblioteca e pelo editor (resposta JSON {location}). */
function admin_media_upload(): void
{
    $wantsJson = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
    $files = $_FILES['file'] ?? null;
    if (!$files) {
        $wantsJson ? json_error('Nenhum ficheiro.') : redirect('/admin/media');
    }
    // Normaliza envio múltiplo (name="file[]") e simples.
    $list = is_array($files['name'])
        ? array_map(fn($i) => array_combine(array_keys($files), array_column($files, $i)), array_keys($files['name']))
        : [$files];

    $done = 0;
    $errors = [];
    $last = null;
    foreach ($list as $f) {
        try {
            $last = handle_upload($f, trim($_POST['alt'] ?? ''));
            $done++;
        } catch (RuntimeException $ex) {
            $errors[] = ($f['name'] ?? '') . ': ' . $ex->getMessage();
        }
    }
    if ($wantsJson) {
        $last ? print(json_encode(['location' => $last['url']])) : json_error(implode(' ', $errors));
        return;
    }
    flash($errors ? implode(' ', $errors) : "$done ficheiro(s) enviado(s).", $errors ? 'error' : 'ok');
    redirect('/admin/media');
}

function json_error(string $msg): never
{
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => ['message' => $msg]]);
    exit;
}

/* ---------- Definições ---------- */

const SETTING_FIELDS = ['site_name', 'site_description', 'site_lang', 'default_image', 'footer_text', 'footer_links', 'google_site_verification', 'contact_email'];

function admin_settings(string $method): void
{
    if ($method === 'POST') {
        foreach (SETTING_FIELDS as $k) {
            $v = trim((string) ($_POST[$k] ?? ''));
            // Aceita a meta tag inteira colada do Search Console e extrai só o código.
            if ($k === 'google_site_verification' && preg_match('/content="([^"]+)"/', $v, $m)) {
                $v = $m[1];
            }
            if ($k === 'site_name' && $v === '') {
                continue;
            }
            setting_set($k, $v);
        }
        setting_set('allow_indexing', isset($_POST['allow_indexing']) ? '1' : '0');
        if (!empty($_FILES['default_image_file']['name'])) {
            try {
                setting_set('default_image', handle_upload($_FILES['default_image_file'], setting('site_name'))['url']);
            } catch (RuntimeException $ex) {
                flash($ex->getMessage(), 'error');
                redirect('/admin/settings');
            }
        }
        flash('Definições guardadas.');
        redirect('/admin/settings');
    }
    admin_render('settings', ['title' => 'Definições']);
}

/* ---------- Aspeto (tema do site público; ver app/theme.php) ---------- */

function admin_appearance(string $method): void
{
    if ($method === 'POST') {
        $theme = (string) ($_POST['theme'] ?? '');
        setting_set('theme', isset(THEMES[$theme]) ? $theme : 'dark');
        if (isset($_POST['reset_custom'])) {
            foreach (['bg', 'surface', 'fg', 'accent', 'font', 'radius', 'grid'] as $k) {
                setting_set("theme_$k", '');
            }
            flash('Tema personalizado reposto com os valores iniciais.');
            redirect('/admin/appearance');
        }
        foreach (array_keys(THEME_COLOR_FIELDS) as $k) {
            setting_set("theme_$k", hex_color((string) ($_POST["theme_$k"] ?? ''), THEMES['custom'][$k]));
        }
        $font = (string) ($_POST['theme_font'] ?? 'sans');
        setting_set('theme_font', isset(THEME_FONTS[$font]) ? $font : 'sans');
        setting_set('theme_radius', (string) max(0, min(28, (int) ($_POST['theme_radius'] ?? 14))));
        setting_set('theme_grid', isset($_POST['theme_grid']) ? '1' : '0');
        flash('Aspeto guardado: tema "' . THEMES[current_theme()]['name'] . '".');
        redirect('/admin/appearance');
    }
    admin_render('appearance', ['title' => 'Aspeto', 'theme' => current_theme(), 'custom' => theme_custom()]);
}
