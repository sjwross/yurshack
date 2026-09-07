<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

render_header(
    AppConfig::siteName(),
    'Yur Shack builds and hosts websites for small businesses — design, managed hosting, domains, and ongoing care.'
);
?>
  <section class="hero" aria-label="Yur Shack introduction">
    <div class="hero-media" aria-hidden="true">
      <img src="assets/hero-shack.svg" alt="">
    </div>
    <div class="hero-shade" aria-hidden="true"></div>
    <div class="hero-content">
      <p class="hero-brand">Yur Shack</p>
      <h2>Websites &amp; hosting, built to keep.</h2>
      <p class="hero-lead">We design, host, and look after small-business sites — from first build to quiet monthly care.</p>
      <div class="cta-row">
        <a class="btn btn-primary" href="order.php">Start an order</a>
        <a class="btn btn-ghost" href="services.php">View services</a>
      </div>
    </div>
  </section>

  <section class="section">
    <div class="site-wrap">
      <div class="section-head reveal">
        <h2>What we offer</h2>
        <p>Standard published rates from our service agreement. Offered prices for your business are confirmed on the order form.</p>
      </div>
      <div class="offer-list reveal">
        <?php foreach ($offerings['services'] as $service): ?>
          <article class="offer-row">
            <div>
              <h3><?= h($service['label']) ?></h3>
              <p><?= h($service['summary']) ?></p>
            </div>
            <div class="offer-price"><?= h($service['standard_display']) ?></div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section" style="padding-top:0">
    <div class="site-wrap">
      <div class="section-head reveal">
        <h2>How your domain works</h2>
        <p>Choose a platform subdomain, bring your own domain, or let us arrange a standard .co.uk for you.</p>
      </div>
      <div class="domain-options reveal">
        <?php foreach ($offerings['domain_arrangements'] as $item): ?>
          <article class="domain-option">
            <h3><?= h($item['label']) ?></h3>
            <p><?= h($item['summary']) ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section" style="padding-top:0">
    <div class="site-wrap">
      <div class="split-cta reveal">
        <div>
          <h2>Ready when you are</h2>
          <p>Send an order request and we will confirm scope, offered pricing, and next steps. Questions first? Write to support.</p>
        </div>
        <div class="cta-row">
          <a class="btn btn-solid" href="order.php">Order form</a>
          <a class="btn btn-outline" href="contact.php">Contact</a>
        </div>
      </div>
    </div>
  </section>
<?php
render_footer();
