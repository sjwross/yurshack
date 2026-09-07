<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/** @var array{domain_arrangements: array, services: array, terms_highlights: list<string>} $offerings */
$offerings = require __DIR__ . '/offerings.php';

function is_static_export(): bool
{
    return (getenv('YURSHACK_STATIC') ?: '') === '1';
}

function page_href(string $name): string
{
    $name = preg_replace('/\.(php|html)$/', '', $name) ?? $name;
    return $name . (is_static_export() ? '.html' : '.php');
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function current_page(): string
{
    $script = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    return $script === '' ? 'index.php' : $script;
}

function nav_active(string $file): string
{
    $want = preg_replace('/\.(php|html)$/', '', $file) ?? $file;
    $have = preg_replace('/\.(php|html)$/', '', current_page()) ?? current_page();
    return $want === $have ? ' aria-current="page" class="is-current"' : '';
}

/**
 * @param array<string, mixed> $vars
 */
function render_header(string $title, string $description = '', array $vars = []): void
{
    $site = AppConfig::siteName();
    $fullTitle = $title === $site ? $title . ' — Websites & Hosting' : $title . ' — ' . $site;
    $desc = $description !== ''
        ? $description
        : 'Yur Shack builds and hosts websites for small businesses — design, hosting, domains, and ongoing care.';
    $bodyClass = trim((string) ($vars['body_class'] ?? '') . (is_static_export() ? ' is-static' : ''));
    ?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($fullTitle) ?></title>
  <meta name="description" content="<?= h($desc) ?>">
  <meta name="theme-color" content="#1e3d2f">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Source+Sans+3:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <link rel="icon" href="assets/mark.svg" type="image/svg+xml">
</head>
<body class="<?= h($bodyClass) ?>"<?= is_static_export() ? ' data-static="1"' : '' ?>>
  <a class="skip-link" href="#main">Skip to content</a>
  <header class="site-header">
    <div class="site-wrap header-inner">
      <a class="brand" href="<?= h(page_href('index')) ?>" aria-label="Yur Shack home">
        <img src="assets/mark.svg" width="40" height="40" alt="" class="brand-mark">
        <span class="brand-name">Yur Shack</span>
      </a>
      <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>
        <span class="sr-only">Menu</span>
        <span></span><span></span>
      </button>
      <nav id="site-nav" class="site-nav" aria-label="Primary">
        <a href="<?= h(page_href('index')) ?>"<?= nav_active('index') ?>>Home</a>
        <a href="<?= h(page_href('services')) ?>"<?= nav_active('services') ?>>Services</a>
        <a href="<?= h(page_href('order')) ?>"<?= nav_active('order') ?>>Order</a>
        <a href="<?= h(page_href('contact')) ?>"<?= nav_active('contact') ?>>Contact</a>
      </nav>
    </div>
  </header>
  <main id="main">
<?php
}

function render_footer(): void
{
    $year = (int) date('Y');
    $email = AppConfig::supportEmail();
    ?>
  </main>
  <footer class="site-footer">
    <div class="site-wrap footer-inner">
      <div class="footer-brand">
        <strong>Yur Shack</strong>
        <p>Websites and hosting for small businesses.</p>
      </div>
      <div class="footer-links">
        <a href="<?= h(page_href('services')) ?>">Services &amp; pricing</a>
        <a href="<?= h(page_href('order')) ?>">Order</a>
        <a href="<?= h(page_href('contact')) ?>">Contact</a>
        <a href="<?= h(page_href('terms')) ?>">Terms</a>
        <a href="mailto:<?= h($email) ?>"><?= h($email) ?></a>
      </div>
      <p class="footer-copy">&copy; <?= $year ?> Yur Shack</p>
    </div>
  </footer>
  <script src="site.js" defer></script>
</body>
</html>
<?php
}

function flash_set(string $type, string $message): void
{
    if (is_static_export()) {
        return;
    }
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_take(): ?array
{
    if (is_static_export()) {
        return null;
    }
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function client_ip(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return is_string($ip) ? $ip : '';
}
