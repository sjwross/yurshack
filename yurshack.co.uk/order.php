<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/mail.php';
require_once __DIR__ . '/includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$errors = [];
$old = [
    'customer_name' => '',
    'customer_email' => '',
    'customer_phone' => '',
    'business_name' => '',
    'website_domain' => '',
    'domain_arrangement' => 'platform_subdomain',
    'initial_term' => 'monthly',
    'payment_method' => 'invoice',
    'special_scope' => '',
    'notes' => '',
    'services' => [],
];

$serviceKeys = array_keys($offerings['services']);
$arrangementKeys = array_keys($offerings['domain_arrangements']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['company_website'])) {
        flash_set('ok', 'Thanks — your order request has been received.');
        header('Location: order.php');
        exit;
    }

    foreach ($old as $key => $_) {
        if ($key === 'services') {
            continue;
        }
        $old[$key] = trim((string) ($_POST[$key] ?? $old[$key]));
    }

    $selected = [];
    $postedServices = $_POST['services'] ?? [];
    if (!is_array($postedServices)) {
        $postedServices = [];
    }
    foreach ($serviceKeys as $key) {
        if (!empty($postedServices[$key])) {
            $selected[] = $key;
        }
    }
    $old['services'] = $selected;

    if ($old['customer_name'] === '' || mb_strlen($old['customer_name']) > 120) {
        $errors[] = 'Please enter your name.';
    }
    if ($old['customer_email'] === '' || !filter_var($old['customer_email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!in_array($old['domain_arrangement'], $arrangementKeys, true)) {
        $errors[] = 'Please choose a domain arrangement.';
    }
    if ($selected === []) {
        $errors[] = 'Select at least one service.';
    }
    $allowedTerms = ['monthly', '12_months', 'other'];
    if (!in_array($old['initial_term'], $allowedTerms, true)) {
        $errors[] = 'Please choose an initial term.';
    }
    $allowedPay = ['card', 'direct_debit', 'invoice'];
    if (!in_array($old['payment_method'], $allowedPay, true)) {
        $errors[] = 'Please choose a payment method.';
    }

    if (!$errors) {
        $serviceRows = [];
        $setup = 0.0;
        $monthly = 0.0;
        $yearly = 0.0;

        foreach ($selected as $key) {
            $svc = $offerings['services'][$key];
            $price = (float) $svc['standard_price'];
            $serviceRows[] = [
                'key' => $key,
                'label' => $svc['label'],
                'billing' => $svc['billing'],
                'standard_price' => $price,
                'standard_display' => $svc['standard_display'],
            ];
            if ($svc['billing'] === 'one_off') {
                $setup += $price;
            } elseif ($svc['billing'] === 'monthly') {
                $monthly += $price;
            } elseif ($svc['billing'] === 'yearly') {
                $yearly += $price;
            }
            // hourly excluded from fixed totals
        }

        $orderPayload = [
            'customer_name' => $old['customer_name'],
            'customer_email' => $old['customer_email'],
            'customer_phone' => $old['customer_phone'] !== '' ? $old['customer_phone'] : null,
            'business_name' => $old['business_name'] !== '' ? $old['business_name'] : null,
            'website_domain' => $old['website_domain'] !== '' ? $old['website_domain'] : null,
            'domain_arrangement' => $old['domain_arrangement'],
            'initial_term' => $old['initial_term'],
            'payment_method' => $old['payment_method'],
            'special_scope' => $old['special_scope'] !== '' ? $old['special_scope'] : null,
            'services' => $serviceRows,
            'estimated_setup_gbp' => $setup > 0 ? $setup : null,
            'estimated_monthly_gbp' => $monthly > 0 ? $monthly : null,
            'estimated_yearly_gbp' => $yearly > 0 ? $yearly : null,
            'notes' => $old['notes'] !== '' ? $old['notes'] : null,
            'ip_address' => client_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 400) : null,
        ];

        $orderId = null;
        $dbOk = false;
        try {
            $orderId = Database::insertOrder($orderPayload);
            $dbOk = true;
        } catch (Throwable $e) {
            error_log('Yur Shack order DB error: ' . $e->getMessage());
        }

        $arrangementLabel = $offerings['domain_arrangements'][$old['domain_arrangement']]['label'];
        $termLabels = [
            'monthly' => 'Monthly',
            '12_months' => '12 months',
            'other' => 'Other / discuss',
        ];
        $payLabels = [
            'card' => 'Card',
            'direct_debit' => 'Direct debit',
            'invoice' => 'Invoice',
        ];

        $serviceText = '';
        $serviceHtml = '<ul>';
        foreach ($serviceRows as $row) {
            $serviceText .= "- {$row['label']} ({$row['standard_display']})\n";
            $serviceHtml .= '<li>' . e($row['label']) . ' — ' . e($row['standard_display']) . '</li>';
        }
        $serviceHtml .= '</ul>';

        $subjectLine = 'Order request'
            . ($orderId ? ' #' . $orderId : '')
            . ' from ' . $old['customer_name'];

        $text = "New order request from yurshack.com\n"
            . ($orderId ? "Order ID: {$orderId}\n" : "Order ID: (not stored — DB unavailable)\n")
            . "DB stored: " . ($dbOk ? 'yes' : 'no') . "\n\n"
            . "Name: {$old['customer_name']}\n"
            . "Email: {$old['customer_email']}\n"
            . "Phone: {$old['customer_phone']}\n"
            . "Business: {$old['business_name']}\n"
            . "Website/domain: {$old['website_domain']}\n"
            . "Domain arrangement: {$arrangementLabel}\n"
            . "Initial term: {$termLabels[$old['initial_term']]}\n"
            . "Payment method: {$payLabels[$old['payment_method']]}\n\n"
            . "Services:\n{$serviceText}\n"
            . "Estimated setup: £" . number_format($setup, 2) . "\n"
            . "Estimated monthly: £" . number_format($monthly, 2) . "\n"
            . "Estimated yearly: £" . number_format($yearly, 2) . "\n\n"
            . "Special scope:\n{$old['special_scope']}\n\n"
            . "Notes:\n{$old['notes']}\n";

        $html = '<h2>New order request'
            . ($orderId ? ' #' . e((string) $orderId) : '')
            . '</h2>'
            . '<p><strong>Name:</strong> ' . e($old['customer_name']) . '<br>'
            . '<strong>Email:</strong> ' . e($old['customer_email']) . '<br>'
            . '<strong>Phone:</strong> ' . e($old['customer_phone']) . '<br>'
            . '<strong>Business:</strong> ' . e($old['business_name']) . '<br>'
            . '<strong>Website/domain:</strong> ' . e($old['website_domain']) . '<br>'
            . '<strong>Domain arrangement:</strong> ' . e($arrangementLabel) . '<br>'
            . '<strong>Initial term:</strong> ' . e($termLabels[$old['initial_term']]) . '<br>'
            . '<strong>Payment method:</strong> ' . e($payLabels[$old['payment_method']]) . '</p>'
            . '<h3>Services</h3>' . $serviceHtml
            . '<p><strong>Estimated setup:</strong> £' . e(number_format($setup, 2))
            . '<br><strong>Estimated monthly:</strong> £' . e(number_format($monthly, 2))
            . '<br><strong>Estimated yearly:</strong> £' . e(number_format($yearly, 2)) . '</p>'
            . '<p><strong>Special scope</strong><br>' . nl2br(e($old['special_scope'])) . '</p>'
            . '<p><strong>Notes</strong><br>' . nl2br(e($old['notes'])) . '</p>'
            . '<p><em>Standard prices shown. Offered prices to be confirmed in writing.</em></p>';

        $mailOk = Mailer::send($subjectLine, $html, $text, $old['customer_email']);

        if ($mailOk || $dbOk) {
            $msg = 'Thanks — your order request has been received';
            if ($orderId) {
                $msg .= ' (reference #' . $orderId . ')';
            }
            $msg .= '. We will follow up from support@yurshack.com.';
            if (!$mailOk) {
                $msg .= ' Email delivery is delayed; your request is still saved.';
            }
            if (!$dbOk) {
                $msg .= ' (Note: order email was sent; database save needs attention.)';
            }
            flash_set('ok', $msg);
            header('Location: order.php');
            exit;
        }

        $errors[] = 'We could not submit your order just now. Please email support@yurshack.com or try again shortly.';
    }
}

$flash = flash_take();
render_header(
    'Order',
    'Request Yur Shack website build, hosting, domain, or development services.'
);
?>
  <section class="page-hero">
    <div class="site-wrap">
      <h1>Order form</h1>
      <p>Select the services you need. Standard prices are shown; we will confirm any offered price in writing before work starts.</p>
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

      <form class="form-panel" method="post" action="order.php" novalidate>
        <div class="honeypot" aria-hidden="true">
          <label for="company_website">Company website</label>
          <input id="company_website" name="company_website" type="text" tabindex="-1" autocomplete="off">
        </div>

        <div class="field">
          <label for="customer_name">Your name</label>
          <input id="customer_name" name="customer_name" required maxlength="120" autocomplete="name" value="<?= h($old['customer_name']) ?>">
        </div>
        <div class="field">
          <label for="customer_email">Email</label>
          <input id="customer_email" name="customer_email" type="email" required maxlength="250" autocomplete="email" value="<?= h($old['customer_email']) ?>">
        </div>
        <div class="field">
          <label for="customer_phone">Phone <span class="hint">(optional)</span></label>
          <input id="customer_phone" name="customer_phone" type="tel" maxlength="40" autocomplete="tel" value="<?= h($old['customer_phone']) ?>">
        </div>
        <div class="field">
          <label for="business_name">Business name <span class="hint">(optional)</span></label>
          <input id="business_name" name="business_name" maxlength="160" value="<?= h($old['business_name']) ?>">
        </div>
        <div class="field">
          <label for="website_domain">Website / domain <span class="hint">(optional)</span></label>
          <input id="website_domain" name="website_domain" maxlength="200" placeholder="example.co.uk" value="<?= h($old['website_domain']) ?>">
        </div>

        <fieldset class="field" style="border:0;padding:0;margin:0 0 1.25rem">
          <legend style="font-weight:600;margin-bottom:0.5rem">Domain arrangement</legend>
          <div class="radio-list">
            <?php foreach ($offerings['domain_arrangements'] as $key => $item): ?>
              <label class="radio-item">
                <input type="radio" name="domain_arrangement" value="<?= h($key) ?>" <?= $old['domain_arrangement'] === $key ? 'checked' : '' ?> required>
                <span>
                  <strong><?= h($item['label']) ?></strong><br>
                  <span class="hint"><?= h($item['summary']) ?></span>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
        </fieldset>

        <fieldset class="field" style="border:0;padding:0;margin:0 0 1.25rem">
          <legend style="font-weight:600;margin-bottom:0.5rem">Services</legend>
          <div class="checkbox-list">
            <?php foreach ($offerings['services'] as $key => $service): ?>
              <label class="checkbox-item">
                <input type="checkbox" name="services[<?= h($key) ?>]" value="1" <?= in_array($key, $old['services'], true) ? 'checked' : '' ?>>
                <span>
                  <strong><?= h($service['label']) ?></strong><br>
                  <span class="hint"><?= h($service['summary']) ?></span>
                </span>
                <span class="price"><?= h($service['standard_display']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </fieldset>

        <div class="field">
          <label for="initial_term">Initial term</label>
          <select id="initial_term" name="initial_term" required>
            <option value="monthly" <?= $old['initial_term'] === 'monthly' ? 'selected' : '' ?>>Monthly</option>
            <option value="12_months" <?= $old['initial_term'] === '12_months' ? 'selected' : '' ?>>12 months</option>
            <option value="other" <?= $old['initial_term'] === 'other' ? 'selected' : '' ?>>Other / discuss</option>
          </select>
        </div>
        <div class="field">
          <label for="payment_method">Preferred payment method</label>
          <select id="payment_method" name="payment_method" required>
            <option value="invoice" <?= $old['payment_method'] === 'invoice' ? 'selected' : '' ?>>Invoice</option>
            <option value="card" <?= $old['payment_method'] === 'card' ? 'selected' : '' ?>>Card</option>
            <option value="direct_debit" <?= $old['payment_method'] === 'direct_debit' ? 'selected' : '' ?>>Direct debit</option>
          </select>
        </div>
        <div class="field">
          <label for="special_scope">Special scope <span class="hint">(pages, features, timeline, exclusions)</span></label>
          <textarea id="special_scope" name="special_scope"><?= h($old['special_scope']) ?></textarea>
        </div>
        <div class="field">
          <label for="notes">Anything else?</label>
          <textarea id="notes" name="notes"><?= h($old['notes']) ?></textarea>
        </div>

        <p class="hint" style="margin-bottom:1rem;color:var(--muted)">Submitting this form requests a quote/order based on standard prices. Offered prices and the final agreement are confirmed in writing.</p>
        <button type="submit" class="btn btn-solid">Submit order request</button>
      </form>
    </div>
  </section>
<?php
render_footer();
