<?php
/**
 * Title: Hero — Home
 * Slug: ma-toronto/section-hero
 * Categories: ma-toronto
 * Description: Homepage hero — eyebrow, headline, lede, two calls to action, and a supporting image.
 * Keywords: hero, banner, home
 * Viewport Width: 1280
 *
 * The "Next meeting" card from the prototype is deliberately absent: it needs
 * the meetings data model and is scoped with that phase. See docs/build-plan.md
 * section 4.2.
 *
 * @package MA_Toronto
 */

?>
<!-- wp:group {"templateLock":"contentOnly","lock":{"move":true,"remove":true},"align":"full","className":"ma-hero is-style-hero-wash","style":{"spacing":{"padding":{"top":"var:preset|spacing|100","bottom":"var:preset|spacing|90","left":"var:preset|spacing|80","right":"var:preset|spacing|80"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ma-hero is-style-hero-wash" style="padding-top:var(--wp--preset--spacing--100);padding-right:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--90);padding-left:var(--wp--preset--spacing--80)">

	<!-- wp:columns {"verticalAlignment":"center","className":"ma-hero__cols","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|90","left":"var:preset|spacing|90"}}}} -->
	<div class="wp-block-columns are-vertically-aligned-center ma-hero__cols">

		<!-- wp:column {"verticalAlignment":"center","width":"52.5%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:52.5%">

			<!-- wp:paragraph {"className":"is-style-eyebrow ma-hero__eyebrow","textColor":"accent","fontSize":"xx-small"} -->
			<p class="is-style-eyebrow ma-hero__eyebrow has-accent-color has-text-color has-xx-small-font-size"><?php echo esc_html_x( 'A fellowship of recovery', 'Hero eyebrow', 'ma-toronto' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:heading {"level":1,"className":"ma-hero__title","fontSize":"display-lg"} -->
			<h1 class="wp-block-heading ma-hero__title has-display-lg-font-size"><?php echo esc_html_x( 'You are no longer alone.', 'Hero headline', 'ma-toronto' ); ?></h1>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"ma-hero__lede","textColor":"body","fontSize":"large"} -->
			<p class="ma-hero__lede has-body-color has-text-color has-large-font-size"><?php echo esc_html_x( 'Marijuana Anonymous Toronto is a free peer-support fellowship for anyone with a desire to stop using marijuana. Meetings run every week — in person across the GTA and online.', 'Hero lede', 'ma-toronto' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:group {"className":"ma-hero__actions","style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"center"}} -->
			<div class="wp-block-group ma-hero__actions">
				<!-- wp:buttons -->
				<div class="wp-block-buttons">
					<!-- wp:button -->
					<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/meetings/' ) ); ?>"><?php echo esc_html_x( 'Find a Meeting', 'Hero primary action', 'ma-toronto' ); ?></a></div>
					<!-- /wp:button -->
				</div>
				<!-- /wp:buttons -->

				<!-- wp:paragraph {"className":"is-style-underline-link ma-hero__secondary","fontSize":"medium"} -->
				<p class="is-style-underline-link ma-hero__secondary has-medium-font-size"><a href="<?php echo esc_url( home_url( '/the-twelve-questions/' ) ); ?>"><?php echo esc_html_x( 'Am I an addict? Take the 12 Questions', 'Hero secondary action', 'ma-toronto' ); ?></a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->

		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center","width":"47.5%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:47.5%">
			<!-- wp:image {"className":"ma-hero__media","style":{"border":{"radius":"var:custom|radius|lg"},"shadow":"var:preset|shadow|lg"}} -->
			<figure class="wp-block-image has-custom-border ma-hero__media"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/hero-placeholder.svg' ) ); ?>" alt="" style="border-radius:var(--wp--custom--radius--lg);box-shadow:var(--wp--preset--shadow--lg)"/></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</div>
<!-- /wp:group -->
