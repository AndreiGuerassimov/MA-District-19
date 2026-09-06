<?php
/**
 * Title: Contact — Split with Form
 * Slug: ma-toronto/section-contact
 * Categories: ma-toronto
 * Description: Contact details and crisis information beside a Contact Form 7 form.
 * Keywords: contact, form, email
 * Viewport Width: 1280
 *
 * The form is Contact Form 7, styled entirely by the theme
 * (assets/css/blocks/core-group.css). CF7 supplies the markup, validation,
 * spam handling and mail; none of its default styling is used.
 *
 * The crisis note is deliberately placed in the left column, above the fold on
 * desktop and immediately after the contact details when stacked. Someone in
 * acute distress should not have to read past a contact form to find it.
 *
 * @package MA_Toronto
 */

$ma_form_id = 'ma_toronto_contact';

?>
<!-- wp:group {"templateLock":"contentOnly","align":"full","className":"ma-contact","style":{"spacing":{"blockGap":"var:preset|spacing|90"}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ma-contact">

	<!-- wp:group {"className":"ma-contact__aside","layout":{"type":"default"}} -->
	<div class="wp-block-group ma-contact__aside">

		<!-- wp:paragraph {"className":"ma-contact__intro","textColor":"body","fontSize":"medium"} -->
		<p class="ma-contact__intro has-body-color has-text-color has-medium-font-size"><?php echo esc_html_x( 'Questions about meetings, the program, or getting started? Reach out. A member will get back to you — everything you share is kept confidential.', 'Contact intro', 'ma-toronto' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:group {"className":"ma-contact__links","layout":{"type":"default"}} -->
		<div class="wp-block-group ma-contact__links">

			<!-- wp:group {"className":"ma-contact__card","layout":{"type":"default"}} -->
			<div class="wp-block-group ma-contact__card">
				<!-- wp:paragraph {"className":"ma-contact__icon ma-contact__icon--mail"} -->
				<p class="ma-contact__icon ma-contact__icon--mail">&#9993;</p>
				<!-- /wp:paragraph -->

				<!-- wp:group {"className":"ma-contact__card-text","layout":{"type":"default"}} -->
				<div class="wp-block-group ma-contact__card-text">
					<!-- wp:paragraph {"className":"ma-contact__card-label"} -->
					<p class="ma-contact__card-label"><?php echo esc_html_x( 'Email', 'Contact card label', 'ma-toronto' ); ?></p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"ma-contact__card-value"} -->
					<p class="ma-contact__card-value"><a href="mailto:info@matoronto.org">info@matoronto.org</a></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"className":"ma-contact__card","layout":{"type":"default"}} -->
			<div class="wp-block-group ma-contact__card">
				<!-- wp:paragraph {"className":"ma-contact__icon ma-contact__icon--clock"} -->
				<p class="ma-contact__icon ma-contact__icon--clock">&#9200;</p>
				<!-- /wp:paragraph -->

				<!-- wp:group {"className":"ma-contact__card-text","layout":{"type":"default"}} -->
				<div class="wp-block-group ma-contact__card-text">
					<!-- wp:paragraph {"className":"ma-contact__card-label"} -->
					<p class="ma-contact__card-label"><?php echo esc_html_x( 'Prefer to just show up?', 'Contact card label', 'ma-toronto' ); ?></p>
					<!-- /wp:paragraph -->

					<!-- wp:paragraph {"className":"ma-contact__card-value"} -->
					<p class="ma-contact__card-value"><a href="<?php echo esc_url( home_url( '/meetings/' ) ); ?>"><?php echo esc_html_x( 'See the meeting schedule', 'Contact card link', 'ma-toronto' ); ?> <span aria-hidden="true">&rarr;</span></a></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:group -->

		<!-- wp:paragraph {"className":"ma-contact__crisis","textColor":"muted","fontSize":"small"} -->
		<p class="ma-contact__crisis has-muted-color has-text-color has-small-font-size"><?php echo esc_html_x( 'If you are in crisis or need immediate help, please call or text 988 (Suicide Crisis Helpline) or 911. MA is a peer-support fellowship, not an emergency service.', 'Crisis note', 'ma-toronto' ); ?></p>
		<!-- /wp:paragraph -->

	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"ma-contact__form","layout":{"type":"default"}} -->
	<div class="wp-block-group ma-contact__form">
		<!-- wp:shortcode -->
		[contact-form-7 id="6e3a479" title="MA Toronto — Contact"]
		<!-- /wp:shortcode -->

		<!-- wp:paragraph {"className":"ma-contact__note","textColor":"muted","fontSize":"xx-small"} -->
		<p class="ma-contact__note has-muted-color has-text-color has-xx-small-font-size"><?php echo esc_html_x( 'We typically reply within a day or two. Your details are never shared.', 'Contact form note', 'ma-toronto' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
