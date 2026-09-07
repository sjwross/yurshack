<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/mail.php';

if (session_status() !== PHP_SESSION_ACTIVE && (getenv('YURSHACK_STATIC') ?: '') !== '1') {
    session_start();
}

$errors = [];
$sent = false;
$old = [
    'name' => '',
    'email' => '',
    'subject' => '',
    'message' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Honeypot
    if (!empty($_POST['company_website'])) {
        flash_set('ok', 'Thanks — your message has been sent.');
        header('Location: contact.php');
        exit;
    }

    $old['name'] = trim((string) ($_POST['name'] ?? ''));
    $old['email'] = trim((string) ($_POST['email'] ?? ''));
    $old['subject'] = trim((string) ($_POST['subject'] ?? ''));
    $old['message'] = trim((string) ($_POST['message'] ?? ''));

    if ($old['name'] === '' || mb_strlen($old['name']) > 120) {
        $errors[] = 'Please enter your name.';
    }
    if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($old['message'] === '' || mb_strlen($old['message']) < 10) {
        $errors[] = 'Please include a short message (at least 10 characters).';
    }
    if (mb_strlen($old['subject']) > 200) {
        $errors[] = 'Subject is too long.';
    }

    if (!$errors) {
        $subjectLine = $old['subject'] !== ''
            ? 'Contact: ' . $old['subject']
            : 'Contact form message from ' . $old['name'];

        $text = "New contact message from yurshack.com\n\n"
            . "Name: {$old['name']}\n"
            . "Email: {$old['email']}\n"
            . "Subject: {$old['subject']}\n\n"
            . $old['message'] . "\n";

        $html = '<h2>New contact message</h2>'
            . '<p><strong>Name:</strong> ' . e($old['name']) . '<br>'
            . '<strong>Email:</strong> ' . e($old['email']) . '<br>'
            . '<strong>Subject:</strong> ' . e($old['subject']) . '</p>'
            . '<p>' . nl2br(e($old['message'])) . '</p>';

        $ok = Mailer::send($subjectLine, $html, $text, $old['email']);
        if ($ok) {
            flash_set('ok', 'Thanks — your message has been sent to support@yurshack.com.');
            header('Location: contact.php');
            exit;
        }
        $errors[] = 'We could not send your message just now. Please email support@yurshack.com directly.';
    }
}

$flash = flash_take();
render_header(
    'Contact',
    'Contact Yur Shack support about websites, hosting, domains, or an existing order.'
);
?>
  <section class="page-hero">
    <div class="site-wrap">
      <h1>Contact</h1>
      <p>Write to the team at support@yurshack.com — or use the form and we will reply by email.</p>
    </div>
  </section>

  <section class="section" style="padding-top:0">
    <div class="site-wrap form-shell">
      <?php if ($flash): ?>
        <div class="alert <?= $flash['type'] === 'ok' ? 'alert-ok' : 'alert-error' ?>"><?= h($flash['message']) ?></div>
      <?php endif; ?>
      <?php if ($errors): ?>
        <div class="alert alert-error">
          <?php foreach ($errors as $err): ?>
            <div><?= h($err) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form class="form-panel" method="post" action="<?= h(page_href('contact')) ?>" novalidate data-mail-to="support@yurshack.com">
        <div class="honeypot" aria-hidden="true">
          <label for="company_website">Company website</label>
          <input id="company_website" name="company_website" type="text" tabindex="-1" autocomplete="off">
        </div>
        <div class="field">
          <label for="name">Name</label>
          <input id="name" name="name" type="text" maxlength="120" required autocomplete="name" value="<?= h($old['name']) ?>">
        </div>
        <div class="field">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" maxlength="250" required autocomplete="email" value="<?= h($old['email']) ?>">
        </div>
        <div class="field">
          <label for="subject">Subject</label>
          <input id="subject" name="subject" type="text" maxlength="200" autocomplete="off" value="<?= h($old['subject']) ?>">
        </div>
        <div class="field">
          <label for="message">Message</label>
          <textarea id="message" name="message" required><?= h($old['message']) ?></textarea>
        </div>
        <button type="submit" class="btn btn-solid">Send message</button>
      </form>
    </div>
  </section>
<?php
render_footer();
