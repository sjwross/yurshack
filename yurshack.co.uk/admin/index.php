<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function admin_h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function admin_logged_in(): bool
{
    return !empty($_SESSION['ys_admin']);
}

function require_admin_config(): array
{
    $user = AppConfig::get('ADMIN_USER', 'support') ?? 'support';
    $pass = AppConfig::get('ADMIN_PASSWORD');
    return [$user, $pass];
}

$loginError = null;
[$adminUser, $adminPass] = require_admin_config();

if (isset($_GET['logout'])) {
    unset($_SESSION['ys_admin']);
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $user = trim((string) ($_POST['username'] ?? ''));
    $pass = (string) ($_POST['password'] ?? '');
    if ($adminPass === null || $adminPass === '') {
        $loginError = 'Admin password is not configured. Set ADMIN_PASSWORD in .env.';
    } elseif (hash_equals($adminUser, $user) && hash_equals($adminPass, $pass)) {
        $_SESSION['ys_admin'] = $user;
        header('Location: index.php');
        exit;
    } else {
        $loginError = 'Invalid username or password.';
        usleep(300000);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status']) && admin_logged_in()) {
    $id = (int) ($_POST['order_id'] ?? 0);
    $status = (string) ($_POST['status'] ?? '');
    try {
        Database::updateOrderStatus($id, $status);
    } catch (Throwable $e) {
        error_log('Admin status update failed: ' . $e->getMessage());
    }
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    header('Location: index.php' . ($qs !== '' ? '?' . $qs : ''));
    exit;
}

$q = trim((string) ($_GET['q'] ?? ''));
$status = trim((string) ($_GET['status'] ?? 'all'));
$from = trim((string) ($_GET['from'] ?? ''));
$to = trim((string) ($_GET['to'] ?? ''));
$limit = (int) ($_GET['limit'] ?? 100);
if ($limit < 1) {
    $limit = 100;
}

$orders = [];
$dbError = null;
if (admin_logged_in()) {
    try {
        $orders = Database::queryOrders(
            $q !== '' ? $q : null,
            $status,
            $from !== '' ? $from : null,
            $to !== '' ? $to : null,
            $limit
        );
    } catch (Throwable $e) {
        $dbError = 'Could not query orders. Check PostgreSQL settings and that sql/schema.sql has been applied.';
        error_log('Admin query failed: ' . $e->getMessage());
    }
}

$statuses = ['all', 'new', 'reviewing', 'accepted', 'declined', 'completed', 'cancelled'];
$arrangementLabels = (require dirname(__DIR__) . '/includes/offerings.php')['domain_arrangements'];
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex,nofollow">
  <title>Support admin — Yur Shack</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../styles.css">
  <link rel="icon" href="../assets/mark.svg" type="image/svg+xml">
</head>
<body class="admin-body">
  <div class="admin-wrap">
    <div class="admin-bar">
      <div>
        <h1 style="font-family:Fraunces,Georgia,serif;font-size:1.8rem;margin:0">Support admin</h1>
        <p style="color:var(--muted);margin:0.25rem 0 0">Query website order requests</p>
      </div>
      <?php if (admin_logged_in()): ?>
        <a class="btn btn-outline" href="?logout=1">Log out</a>
      <?php endif; ?>
    </div>

    <?php if (!admin_logged_in()): ?>
      <?php if ($loginError): ?>
        <div class="alert alert-error"><?= admin_h($loginError) ?></div>
      <?php endif; ?>
      <form class="form-panel" method="post" style="max-width:24rem">
        <div class="field">
          <label for="username">Username</label>
          <input id="username" name="username" required autocomplete="username" value="<?= admin_h($adminUser) ?>">
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" required autocomplete="current-password">
        </div>
        <button class="btn btn-solid" type="submit" name="login" value="1">Sign in</button>
      </form>
    <?php else: ?>
      <?php if ($dbError): ?>
        <div class="alert alert-error"><?= admin_h($dbError) ?></div>
      <?php endif; ?>

      <form class="admin-filters" method="get">
        <div class="field" style="margin:0">
          <label for="q">Search</label>
          <input id="q" name="q" placeholder="Name, email, domain, id" value="<?= admin_h($q) ?>">
        </div>
        <div class="field" style="margin:0">
          <label for="status">Status</label>
          <select id="status" name="status">
            <?php foreach ($statuses as $s): ?>
              <option value="<?= admin_h($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= admin_h($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field" style="margin:0">
          <label for="from">From</label>
          <input id="from" name="from" type="date" value="<?= admin_h($from) ?>">
        </div>
        <div class="field" style="margin:0">
          <label for="to">To</label>
          <input id="to" name="to" type="date" value="<?= admin_h($to) ?>">
        </div>
        <div class="field" style="margin:0">
          <label for="limit">Limit</label>
          <input id="limit" name="limit" type="number" min="1" max="500" value="<?= (int) $limit ?>">
        </div>
        <div class="field" style="margin:0;display:flex;align-items:end">
          <button class="btn btn-solid" type="submit">Query</button>
        </div>
      </form>

      <p style="color:var(--muted);margin:0 0 0.75rem"><?= count($orders) ?> result<?= count($orders) === 1 ? '' : 's' ?></p>

      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>When</th>
              <th>Customer</th>
              <th>Domain</th>
              <th>Services</th>
              <th>Estimates</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$orders): ?>
              <tr><td colspan="7">No orders matched.</td></tr>
            <?php endif; ?>
            <?php foreach ($orders as $order): ?>
              <?php
                $services = $order['services'];
                if (is_string($services)) {
                    $services = json_decode($services, true) ?: [];
                }
                if (!is_array($services)) {
                    $services = [];
                }
                $arrKey = (string) $order['domain_arrangement'];
                $arrLabel = $arrangementLabels[$arrKey]['label'] ?? $arrKey;
              ?>
              <tr>
                <td>#<?= (int) $order['id'] ?></td>
                <td><?= admin_h(date('Y-m-d H:i', strtotime((string) $order['created_at']))) ?></td>
                <td>
                  <strong><?= admin_h((string) $order['customer_name']) ?></strong><br>
                  <a href="mailto:<?= admin_h((string) $order['customer_email']) ?>"><?= admin_h((string) $order['customer_email']) ?></a>
                  <?php if (!empty($order['business_name'])): ?>
                    <br><span style="color:var(--muted)"><?= admin_h((string) $order['business_name']) ?></span>
                  <?php endif; ?>
                  <?php if (!empty($order['special_scope'])): ?>
                    <details style="margin-top:0.35rem">
                      <summary>Scope</summary>
                      <div style="white-space:pre-wrap;max-width:18rem"><?= admin_h((string) $order['special_scope']) ?></div>
                    </details>
                  <?php endif; ?>
                </td>
                <td>
                  <?= admin_h((string) ($order['website_domain'] ?: '—')) ?><br>
                  <span style="color:var(--muted);font-size:0.85rem"><?= admin_h($arrLabel) ?></span>
                </td>
                <td>
                  <div class="service-chips">
                    <?php foreach ($services as $svc): ?>
                      <span><?= admin_h((string) ($svc['label'] ?? $svc['key'] ?? 'service')) ?></span>
                    <?php endforeach; ?>
                  </div>
                </td>
                <td style="white-space:nowrap">
                  <?php if ($order['estimated_setup_gbp'] !== null): ?>setup £<?= admin_h(number_format((float) $order['estimated_setup_gbp'], 2)) ?><br><?php endif; ?>
                  <?php if ($order['estimated_monthly_gbp'] !== null): ?>/mo £<?= admin_h(number_format((float) $order['estimated_monthly_gbp'], 2)) ?><br><?php endif; ?>
                  <?php if ($order['estimated_yearly_gbp'] !== null): ?>/yr £<?= admin_h(number_format((float) $order['estimated_yearly_gbp'], 2)) ?><?php endif; ?>
                </td>
                <td>
                  <span class="status-pill"><?= admin_h((string) $order['status']) ?></span>
                  <form method="post" style="margin-top:0.5rem">
                    <input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>">
                    <select name="status" style="font:inherit;margin-bottom:0.35rem;width:100%">
                      <?php foreach (array_slice($statuses, 1) as $s): ?>
                        <option value="<?= admin_h($s) ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= admin_h($s) ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button class="btn btn-outline" style="min-height:2rem;padding:0.35rem 0.6rem;font-size:0.85rem" type="submit" name="update_status" value="1">Update</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
