<?php
/** @var array $items pedidos recebidos pelo formulário do site (por tratar primeiro) */
$new = count(array_filter($items, fn($c) => !$c['handled']));
?>
<div class="head">
    <h1>Contactos</h1>
    <span class="muted"><?= $new ?> por tratar · <?= count($items) ?> no total</span>
</div>
<p class="muted">Pedidos enviados pelo formulário das páginas com “Mostrar formulário de contacto”.
    Para responder, clique no email. Apague os contactos de que já não precisa (RGPD).</p>

<?php if (!$items): ?>
    <div class="card"><p>Ainda não chegou nenhum contacto.</p></div>
<?php endif; ?>

<?php foreach ($items as $c):
    $subject = rawurlencode('Re: your enquiry to ' . setting('site_name')); ?>
    <article class="card contact-item<?= $c['handled'] ? ' handled' : '' ?>">
        <header class="contact-head">
            <div>
                <strong><?= e($c['name']) ?></strong>
                <?php if ($c['organization'] !== ''): ?><span class="muted">· <?= e($c['organization']) ?></span><?php endif; ?>
                <?php if (!$c['handled']): ?><span class="badge draft">Novo</span><?php else: ?><span class="badge">Tratado</span><?php endif; ?>
                <div><a href="mailto:<?= e($c['email']) ?>?subject=<?= $subject ?>"><?= e($c['email']) ?></a></div>
            </div>
            <div class="muted small contact-meta">
                <?= e(format_date($c['created_at'])) ?>, <?= e(date('H:i', strtotime($c['created_at']))) ?><br>
                <?= e(CONTACT_TOPICS[$c['topic']] ?? $c['topic']) ?> · /<?= e($c['page'] === 'home' ? '' : $c['page']) ?>
            </div>
        </header>
        <p class="contact-message"><?= nl2br(e($c['message'])) ?></p>
        <div class="actions">
            <form method="post" action="/admin/contacts/<?= (int) $c['id'] ?>/<?= $c['handled'] ? 'reopen' : 'handled' ?>">
                <?= csrf_field() ?>
                <button class="btn small<?= $c['handled'] ? '' : ' primary' ?>"><?= $c['handled'] ? 'Reabrir' : 'Marcar como tratado' ?></button>
            </form>
            <form method="post" action="/admin/contacts/<?= (int) $c['id'] ?>/delete">
                <?= csrf_field() ?>
                <button class="btn small" data-confirm="Apagar o contacto de &quot;<?= e($c['name']) ?>&quot;? Não pode ser desfeito.">Apagar</button>
            </form>
        </div>
    </article>
<?php endforeach; ?>
