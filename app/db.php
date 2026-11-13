<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }
    $path = (string) config('db_path', ROOT . '/data/site.sqlite');
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }
    $fresh = !is_file($path);
    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA journal_mode = WAL; PRAGMA foreign_keys = ON; PRAGMA busy_timeout = 5000;');
    migrate($pdo);
    if ($fresh) {
        seed($pdo);
    }
    return $pdo;
}

function migrate(PDO $pdo): void
{
    $pdo->exec(<<<SQL
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY,
        username TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        created_at TEXT NOT NULL
    );
    CREATE TABLE IF NOT EXISTS settings (
        name TEXT PRIMARY KEY,
        value TEXT NOT NULL DEFAULT ''
    );
    CREATE TABLE IF NOT EXISTS pages (
        id INTEGER PRIMARY KEY,
        slug TEXT NOT NULL UNIQUE,
        title TEXT NOT NULL,
        menu_label TEXT NOT NULL DEFAULT '',
        meta_description TEXT NOT NULL DEFAULT '',
        content TEXT NOT NULL DEFAULT '',
        cover_image TEXT NOT NULL DEFAULT '',
        published INTEGER NOT NULL DEFAULT 1,
        noindex INTEGER NOT NULL DEFAULT 0,
        in_menu INTEGER NOT NULL DEFAULT 1,
        menu_order INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    );
    CREATE TABLE IF NOT EXISTS media (
        id INTEGER PRIMARY KEY,
        path TEXT NOT NULL UNIQUE,
        original_name TEXT NOT NULL,
        mime TEXT NOT NULL,
        size INTEGER NOT NULL,
        width INTEGER,
        height INTEGER,
        alt TEXT NOT NULL DEFAULT '',
        created_at TEXT NOT NULL
    );
    CREATE TABLE IF NOT EXISTS login_attempts (
        ip TEXT NOT NULL,
        attempted_at INTEGER NOT NULL
    );
    CREATE TABLE IF NOT EXISTS page_views (
        id INTEGER PRIMARY KEY,
        created_at TEXT NOT NULL,
        day TEXT NOT NULL,
        path TEXT NOT NULL,
        content_type TEXT NOT NULL DEFAULT '',
        content_id INTEGER,
        visitor TEXT NOT NULL,
        referrer_host TEXT NOT NULL DEFAULT '',
        source TEXT NOT NULL DEFAULT '',
        channel TEXT NOT NULL DEFAULT '',
        country TEXT NOT NULL DEFAULT '',
        language TEXT NOT NULL DEFAULT '',
        device TEXT NOT NULL DEFAULT '',
        os TEXT NOT NULL DEFAULT '',
        browser TEXT NOT NULL DEFAULT ''
    );
    CREATE INDEX IF NOT EXISTS page_views_day ON page_views (day);
    CREATE INDEX IF NOT EXISTS page_views_content ON page_views (content_type, content_id);
    CREATE TABLE IF NOT EXISTS bot_hits (
        day TEXT NOT NULL,
        bot TEXT NOT NULL,
        hits INTEGER NOT NULL DEFAULT 0,
        PRIMARY KEY (day, bot)
    );
    SQL);
}

/** Conteúdo inicial (o que estava em qivolt.com); depois edita-se tudo no backoffice. */
function seed(PDO $pdo): void
{
    $data = require __DIR__ . '/seed.php';
    $now = now();

    $st = $pdo->prepare('INSERT OR IGNORE INTO settings (name, value) VALUES (?, ?)');
    foreach ($data['settings'] as $k => $v) {
        $st->execute([$k, $v]);
    }
    $st = $pdo->prepare('INSERT INTO pages (slug, title, menu_label, meta_description, content, in_menu, menu_order, noindex, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($data['pages'] as $p) {
        $st->execute([$p['slug'], $p['title'], $p['menu_label'] ?? '', $p['description'] ?? '', sanitize_html($p['content']),
            $p['in_menu'] ?? 0, $p['menu_order'] ?? 0, $p['noindex'] ?? 0, $now, $now]);
    }
}

function user_count(): int
{
    return (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
}

function menu_pages(): array
{
    return db()->query("SELECT slug, title, menu_label FROM pages
        WHERE published = 1 AND in_menu = 1 AND slug <> 'home'
        ORDER BY menu_order, title")->fetchAll();
}

function find_page(string $slug): ?array
{
    $st = db()->prepare('SELECT * FROM pages WHERE slug = ?');
    $st->execute([$slug]);
    return $st->fetch() ?: null;
}
