<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

render_header(
    'Terms overview',
    'Summary of Yur Shack website service terms for customers.'
);
?>
  <section class="page-hero">
    <div class="site-wrap">
      <h1>Terms overview</h1>
      <p>A plain-language summary of our Master Website Service Agreement. Your signed order form and full core terms govern the engagement.</p>
    </div>
  </section>

  <section class="section" style="padding-top:0">
    <div class="site-wrap" style="max-width:44rem">
      <article class="reveal" style="margin-bottom:2rem">
        <h2 style="font-size:1.4rem;margin-bottom:0.5rem">Scope &amp; care</h2>
        <p style="color:var(--muted)">We provide the selected services with reasonable skill and care. Work outside the stated scope needs written agreement and may be charged separately.</p>
      </article>
      <article class="reveal" style="margin-bottom:2rem">
        <h2 style="font-size:1.4rem;margin-bottom:0.5rem">Payment</h2>
        <p style="color:var(--muted)">Setup fees are payable before work starts. Recurring fees are monthly in advance unless your order says otherwise. Fees are exclusive of VAT unless stated. Recurring fees may change on 30 days’ written notice.</p>
      </article>
      <article class="reveal" style="margin-bottom:2rem">
        <h2 style="font-size:1.4rem;margin-bottom:0.5rem">Support &amp; changes</h2>
        <p style="color:var(--muted)">Managed hosting includes reasonable email support for faults and up to 30 minutes per calendar month of basic changes (text, images, opening hours, prices, contact details). Unused time does not roll over. Larger work is chargeable.</p>
      </article>
      <article class="reveal" style="margin-bottom:2rem">
        <h2 style="font-size:1.4rem;margin-bottom:0.5rem">Cancellation</h2>
        <p style="color:var(--muted)">Monthly services may be cancelled on 30 days’ written notice, subject to accrued fees and committed third-party costs.</p>
      </article>
      <article class="reveal" style="margin-bottom:2rem">
        <h2 style="font-size:1.4rem;margin-bottom:0.5rem">Intellectual property</h2>
        <p style="color:var(--muted)">Subject to full payment, you own your content and bespoke website content created specifically for you. We retain tools, templates, reusable components, and know-how, licensed for use with the selected service.</p>
      </article>
      <p class="reveal" style="color:var(--muted)">Questions about an order or agreement: <a href="mailto:<?= h(AppConfig::supportEmail()) ?>"><?= h(AppConfig::supportEmail()) ?></a>.</p>
    </div>
  </section>
<?php
render_footer();
