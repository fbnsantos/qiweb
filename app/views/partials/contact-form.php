<?php
/** @var array $page  @var array $form ['errors' => [...], 'old' => [...]] quando o envio falhou */
$old = ($form['old'] ?? []) + ['name' => '', 'email' => '', 'organization' => '', 'topic' => '', 'message' => ''];
$errors = $form['errors'] ?? [];
$sent = isset($_GET['sent']) && !$errors;
?>
<section class="wrap contact" id="contact">
    <?php if ($sent): ?>
        <div class="contact-done" role="status">
            <h2>Thank you — message received.</h2>
            <p>We will reply by email, usually within two working days.</p>
        </div>
    <?php else: ?>
        <h2>Tell us about your project</h2>
        <p class="muted">A few lines are enough. We reply by email, usually within two working days.</p>
        <?php if ($errors): ?>
            <div class="contact-errors" role="alert">
                <?php foreach ($errors as $err): ?><p><?= e($err) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="post" action="/contact#contact" class="contact-form">
            <input type="hidden" name="page" value="<?= e($page['slug']) ?>">
            <input type="hidden" name="ts" value="<?= e(contact_token()) ?>">
            <p class="contact-trap" aria-hidden="true">
                <label>Leave this field empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </p>
            <div class="contact-row">
                <label>Name
                    <input type="text" name="name" value="<?= e($old['name']) ?>" autocomplete="name" maxlength="120" required>
                </label>
                <label>Email
                    <input type="email" name="email" value="<?= e($old['email']) ?>" autocomplete="email" maxlength="200" required>
                </label>
            </div>
            <div class="contact-row">
                <label><span>Organisation <span class="muted">(optional)</span></span>
                    <input type="text" name="organization" value="<?= e($old['organization']) ?>" autocomplete="organization" maxlength="160">
                </label>
                <label>What do you need?
                    <select name="topic">
                        <?php foreach (CONTACT_TOPICS as $key => $label): ?>
                            <option value="<?= $key ?>"<?= $old['topic'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <label>Your project or question
                <textarea name="message" rows="6" maxlength="5000" required><?= e($old['message']) ?></textarea>
            </label>
            <label class="contact-consent">
                <input type="checkbox" name="consent" value="1" required>
                <span>I agree that <?= e(setting('site_name')) ?> stores these details to reply to my enquiry. They are not shared
                    with anyone and are deleted on request.</span>
            </label>
            <button class="button" type="submit">Send message</button>
        </form>
    <?php endif; ?>
</section>
