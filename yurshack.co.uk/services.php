<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

render_header(
    'Services & pricing',
    'Standard Yur Shack website, hosting, domain, and development pricing.'
);
?>
  <section class="page-hero">
    <div class="site-wrap">
      <h1>Services &amp; pricing</h1>
      <p>These are our standard published rates. Your order form can include offered prices that become binding only when accepted in writing.</p>
    </div>
  </section>

  <section class="section" style="padding-top:0">
    <div class="site-wrap">
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

      <div class="section-head reveal" style="margin-top:3.5rem">
        <h2>Domain arrangements</h2>
        <p>Only one arrangement applies per site. Custom-domain setup and annual domain fees depend on the option you choose.</p>
      </div>
      <div class="domain-options reveal">
        <?php foreach ($offerings['domain_arrangements'] as $item): ?>
          <article class="domain-option">
            <h3><?= h($item['label']) ?></h3>
            <p><?= h($item['summary']) ?></p>
          </article>
        <?php endforeach; ?>
      </div>

      <div class="section-head reveal" style="margin-top:3.5rem">
        <h2>Good to know</h2>
        <p>A few limits from our core terms that most customers ask about first.</p>
      </div>
      <ul class="terms-list reveal">
        <?php foreach ($offerings['terms_highlights'] as $line): ?>
          <li><?= h($line) ?></li>
        <?php endforeach; ?>
      </ul>

      <div class="split-cta reveal" style="margin-top:3rem">
        <div>
          <h2>Start with an order</h2>
          <p>Select the services you need and we will follow up from support@yurshack.com.</p>
        </div>
        <div class="cta-row">
          <a class="btn btn-solid" href="<?= h(page_href('order')) ?>">Order</a>
          <a class="btn btn-outline" href="<?= h(page_href('terms')) ?>">Terms overview</a>
        </div>
      </div>
    </div>
  </section>
<?php
render_footer();
