<?php
/**
 * Title: Instapass Contact Page
 * Slug: instapass/page-contact
 * Categories: instapass, page
 * Description: Telegram and email support, with guidance for order and activation enquiries.
 * Block Types: core/post-content
 * Post Types: page
 * Viewport Width: 1000
 */
?>
<!-- wp:paragraph {"className":"ip-lead"} -->
<p class="ip-lead"><?php esc_html_e( 'Questions about an order, a licence key or activation link that will not work, a refund or an invoice? Message us on Telegram or email us below.', 'instapass' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2} -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Contact us', 'instapass' ); ?></h2>
<!-- /wp:heading -->

<?php /* WhatsApp is intentionally omitted until the owner supplies a number.
   Pages using this pattern reference receive its update. Pages edited into
   ordinary blocks keep their saved content and must be updated separately. */ ?>
<!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"var:preset|spacing|20","left":"var:preset|spacing|20"}}},"className":"ip-contact-methods"} -->
<div class="wp-block-columns ip-contact-methods"><!-- wp:column {"className":"ip-contact"} -->
<div class="wp-block-column ip-contact"><!-- wp:html -->
<span class="ip-contact__icon"><svg class="ip-icon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg></span>
<!-- /wp:html -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Telegram', 'instapass' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><a class="ip-contact__link" href="https://t.me/AJNF48" target="_blank" rel="noopener">@AJNF48</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"ip-contact"} -->
<div class="wp-block-column ip-contact"><!-- wp:html -->
<span class="ip-contact__icon"><svg class="ip-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m3 8 9 6 9-6"/></svg></span>
<!-- /wp:html -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Email', 'instapass' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><a class="ip-contact__link" href="mailto:ajnf0408@gmail.com">ajnf0408@gmail.com</a></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"var:preset|spacing|20","left":"var:preset|spacing|20"}}},"className":"ip-trust ip-contact-notes"} -->
<div class="wp-block-columns ip-trust ip-contact-notes"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'What to include', 'instapass' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Your order number and the email address you used at checkout, so we can find the purchase straight away. Refund requests and activation issues are handled first.', 'instapass' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php esc_html_e( 'Before you write', 'instapass' ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Nothing arrived? Check spam and promotions folders, and confirm the payment completed. Most delivery questions are answered on the FAQ page.', 'instapass' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->
<?php /* A contact form can be added here later as a block or shortcode. */ ?>
