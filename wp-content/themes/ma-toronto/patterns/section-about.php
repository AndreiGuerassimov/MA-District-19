<?php
/**
 * Title: About Band with Counters
 * Slug: ma-toronto/section-about
 * Categories: ma-toronto
 * Description: Full-width forest band stating what the fellowship is, followed by two figures.
 * Keywords: about, band, statistics, counters
 * Viewport Width: 1280
 *
 * The two figures are editable text rather than live data (docs/build-plan.md
 * 8.4): they change rarely and carry no freshness or timezone hazard, unlike
 * the deferred "next meeting" card.
 *
 * @package MA_Toronto
 */

?>
<!-- wp:group {"align":"full","className":"ma-about is-style-forest","style":{"spacing":{"padding":{"top":"var:preset|spacing|100","bottom":"var:preset|spacing|100","left":"var:preset|spacing|80","right":"var:preset|spacing|80"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ma-about is-style-forest" style="padding-top:var(--wp--preset--spacing--100);padding-right:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--100);padding-left:var(--wp--preset--spacing--80)">

	<!-- wp:group {"className":"ma-about__copy","style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained","contentSize":"820px"}} -->
	<div class="wp-block-group ma-about__copy">

		<!-- wp:heading {"textAlign":"center","level":2,"className":"ma-about__title","fontSize":"xx-large"} -->
		<h2 class="wp-block-heading has-text-align-center ma-about__title has-xx-large-font-size"><?php echo esc_html_x( 'A fellowship of people who share experience, strength, and hope.', 'About band heading', 'ma-toronto' ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"align":"center","className":"ma-about__body","fontSize":"medium"} -->
		<p class="has-text-align-center ma-about__body has-medium-font-size"><?php echo esc_html_x( 'The only requirement for membership is a desire to stop using marijuana. There are no dues or fees. MA is not affiliated with any religious or secular organization — our primary purpose is to stay free of marijuana and to help the marijuana addict who still suffers achieve the same freedom.', 'About band body', 'ma-toronto' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:paragraph {"align":"center","className":"ma-about__highlight","textColor":"sand","fontSize":"medium"} -->
		<p class="has-text-align-center ma-about__highlight has-sand-color has-text-color has-medium-font-size"><strong><?php echo esc_html_x( 'No one is excluded from MA — every age, background, identity, and walk of life is welcome.', 'About band highlight', 'ma-toronto' ); ?></strong></p>
		<!-- /wp:paragraph -->

	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"ma-stats","style":{"spacing":{"blockGap":"120px","margin":{"top":"var:preset|spacing|80"}}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center","verticalAlignment":"stretch"}} -->
	<div class="wp-block-group ma-stats" style="margin-top:var(--wp--preset--spacing--80)">

		<!-- wp:group {"className":"ma-stat","style":{"spacing":{"blockGap":"var:preset|spacing|10"}},"layout":{"type":"default"}} -->
		<div class="wp-block-group ma-stat">
			<!-- wp:paragraph {"align":"center","className":"is-style-stat ma-stat__figure"} -->
			<p class="has-text-align-center is-style-stat ma-stat__figure"><?php echo esc_html_x( '392+', 'Statistic: number of MA meetings worldwide', 'ma-toronto' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"align":"center","className":"is-style-eyebrow ma-stat__label"} -->
			<p class="has-text-align-center is-style-eyebrow ma-stat__label"><?php echo esc_html_x( 'MA meetings worldwide', 'Statistic label', 'ma-toronto' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:separator {"className":"ma-stats__rule"} -->
		<hr class="wp-block-separator has-alpha-channel-opacity ma-stats__rule"/>
		<!-- /wp:separator -->

		<!-- wp:group {"className":"ma-stat","style":{"spacing":{"blockGap":"var:preset|spacing|10"}},"layout":{"type":"default"}} -->
		<div class="wp-block-group ma-stat">
			<!-- wp:paragraph {"align":"center","className":"is-style-stat ma-stat__figure"} -->
			<p class="has-text-align-center is-style-stat ma-stat__figure"><?php echo esc_html_x( '$366,218+', 'Statistic: money saved by members', 'ma-toronto' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"align":"center","className":"is-style-eyebrow ma-stat__label"} -->
			<p class="has-text-align-center is-style-eyebrow ma-stat__label"><?php echo esc_html_x( 'Saved by our members', 'Statistic label', 'ma-toronto' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
