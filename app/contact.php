<?php
/*
 * Formulário de contacto do site público (páginas com "Mostrar formulário de contacto").
 * Os pedidos ficam na tabela contacts e veem-se no backoffice → Contactos.
 *
 * Contra spam, sem cookies nem serviços externos: um campo-armadilha invisível, um selo temporal assinado
 * (rejeita envios em menos de 3 segundos) e um limite de envios por hora. O IP não é guardado: fica só
 * um hash diário, usado para esse limite.
 */
declare(strict_types=1);

const CONTACT_TOPICS = [
    'consulting' => 'Technology consulting',
    'eu-proposal' => 'European project proposal',
    'engineering' => 'Engineering / robotics project',
    'expert-opinion' => 'Expert opinion / technical review',
    'other' => 'Something else',
];
const CONTACT_MAX_PER_HOUR = 5;
const CONTACT_MIN_SECONDS = 3;

/** Selo "hora.assinatura" posto no formulário quando a página é mostrada. */
function contact_token(): string
{
    $ts = (string) time();
    return $ts . '.' . hash_hmac('sha256', 'contact|' . $ts, (string) config('setup_key', ''));
}

/** Idade do selo em segundos, ou null se for inválido ou tiver mais de um dia. */
function contact_token_age(string $token): ?int
{
    [$ts, $sig] = explode('.', $token, 2) + [1 => ''];
    if (!ctype_digit($ts) || !hash_equals(hash_hmac('sha256', 'contact|' . $ts, (string) config('setup_key', '')), $sig)) {
        return null;
    }
    $age = time() - (int) $ts;
    return $age >= 0 && $age <= 86400 ? $age : null;
}

function contact_ip_hash(): string
{
    return substr(hash('sha256', date('Y-m-d') . '|contact|' . config('setup_key', '') . '|' . client_ip()), 0, 16);
}

/** Trata o envio do formulário (POST /contact). */
function contact_submit(): void
{
    $slug = (string) ($_POST['page'] ?? '');
    $page = preg_match('/^[a-z0-9_-]{1,80}$/', $slug) ? find_page($slug) : null;
    if (!$page || !$page['published'] || !$page['contact_form']) {
        not_found();
        return;
    }
    $back = ($slug === 'home' ? '/' : '/' . $slug) . '?sent=1#contact';
    $in = fn(string $k, int $max) => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);
    $old = [
        'name' => $in('name', 120),
        'email' => $in('email', 200),
        'organization' => $in('organization', 160),
        'topic' => $in('topic', 40),
        'message' => $in('message', 5000),
    ];

    // Campo-armadilha preenchido: é um robô. Responde como se tivesse corrido bem, sem guardar nada.
    if (trim((string) ($_POST['website'] ?? '')) !== '') {
        redirect($back, 303);
    }

    $errors = [];
    if ($old['name'] === '') {
        $errors[] = 'Please tell us your name.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!isset(CONTACT_TOPICS[$old['topic']])) {
        $old['topic'] = 'other';
    }
    if (mb_strlen($old['message']) < 10) {
        $errors[] = 'Please describe your project or question in a few words.';
    }
    if (empty($_POST['consent'])) {
        $errors[] = 'Please confirm that we may use your details to reply.';
    }
    $age = contact_token_age((string) ($_POST['ts'] ?? ''));
    if (!$errors && ($age === null || $age < CONTACT_MIN_SECONDS)) {
        $errors[] = 'The form expired. Please send it again.';
    }
    if (!$errors) {
        $st = db()->prepare('SELECT COUNT(*) FROM contacts WHERE ip_hash = ? AND created_at >= ?');
        $st->execute([contact_ip_hash(), date('Y-m-d H:i:s', time() - 3600)]);
        if ((int) $st->fetchColumn() >= CONTACT_MAX_PER_HOUR) {
            $errors[] = 'Too many messages in a short time. Please try again later.';
        }
    }
    if ($errors) {
        http_response_code(422);
        show_page($slug, ['errors' => $errors, 'old' => $old]);
        return;
    }

    db()->prepare('INSERT INTO contacts (created_at, name, email, organization, topic, message, page, ip_hash)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([now(), $old['name'], $old['email'], $old['organization'], $old['topic'], $old['message'], $slug, contact_ip_hash()]);
    contact_notify($old);
    redirect($back, 303);
}

/** Aviso por email de um novo contacto (Definições → email de avisos). Falhar aqui nunca estraga o envio. */
function contact_notify(array $c): void
{
    $to = setting('contact_email');
    if (!filter_var($to, FILTER_VALIDATE_EMAIL) || !function_exists('mail')) {
        return;
    }
    $clean = fn(string $s) => trim(preg_replace('/[\r\n]+/', ' ', $s) ?? '');
    $host = preg_replace('/^www\./', '', (string) (parse_url(base_url(), PHP_URL_HOST) ?: 'localhost'));
    $body = "New enquiry from the website\n\n"
        . 'Name: ' . $clean($c['name']) . "\n"
        . 'Email: ' . $clean($c['email']) . "\n"
        . 'Organisation: ' . $clean($c['organization']) . "\n"
        . 'Topic: ' . (CONTACT_TOPICS[$c['topic']] ?? $c['topic']) . "\n\n"
        . $c['message'] . "\n\n"
        . abs_url('/admin/contacts') . "\n";
    $headers = "From: " . $clean(setting('site_name', 'Website')) . " <no-reply@$host>\r\n"
        . 'Reply-To: ' . $clean($c['email']) . "\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n";
    try {
        @mail($to, '=?UTF-8?B?' . base64_encode('[' . setting('site_name') . '] ' . $clean($c['name'])) . '?=', $body, $headers);
    } catch (Throwable $e) {
        error_log('Aviso de contacto: ' . $e->getMessage());
    }
}

function new_contacts_count(): int
{
    return (int) db()->query('SELECT COUNT(*) FROM contacts WHERE handled = 0')->fetchColumn();
}
