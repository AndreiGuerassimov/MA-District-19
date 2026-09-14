<?php
/**
 * Title: Learn More — Literature and App
 * Slug: ma-toronto/section-learn-more
 * Categories: ma-toronto
 * Description: Where to get Life with Hope, a link to all MA literature, and a card for the Marijuana Anonymous app.
 * Keywords: literature, app, learn more, a new leaf
 * Viewport Width: 1280
 *
 * App store links are the Marijuana Anonymous app's real listings, taken from
 * the old site and checked 13 Sep 2026. Store buttons use the "Ink Block"
 * button style (styles/blocks/08-ink-block.json).
 *
 * @package MA_Toronto
 */

$ma_literature = get_page_by_path( 'literature' );
$ma_literature = $ma_literature ? (string) get_permalink( $ma_literature ) : home_url( '/literature/' );

?>
<!-- wp:group {"templateLock":"contentOnly","lock":{"move":true,"remove":true},"align":"full","className":"ma-learn is-style-surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|100","bottom":"var:preset|spacing|100","left":"var:preset|spacing|80","right":"var:preset|spacing|80"}},"border":{"top":{"color":"var:custom|hairline|color","style":"solid","width":"1px"}}},"layout":{"type":"constrained","contentSize":"900px"}} -->
<div class="wp-block-group alignfull ma-learn is-style-surface" style="border-top-color:var(--wp--custom--hairline--color);border-top-style:solid;border-top-width:1px;padding-top:var(--wp--preset--spacing--100);padding-right:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--100);padding-left:var(--wp--preset--spacing--80)">

	<!-- wp:columns {"verticalAlignment":"center","className":"ma-learn__cols","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|70","left":"var:preset|spacing|70"}}}} -->
	<div class="wp-block-columns are-vertically-aligned-center ma-learn__cols">

		<!-- wp:column {"verticalAlignment":"center","width":"55%","style":{"spacing":{"blockGap":"var:preset|spacing|30"}}} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:55%">
			<!-- wp:heading {"level":2,"className":"ma-learn__title","fontSize":"xx-large"} -->
			<h2 class="wp-block-heading ma-learn__title has-xx-large-font-size"><?php echo esc_html_x( 'Want to learn more about the Steps, Traditions or read more of our stories?', 'Learn more heading', 'ma-toronto' ); ?></h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"ma-learn__text","textColor":"body","fontSize":"medium"} -->
			<p class="ma-learn__text has-body-color has-text-color has-medium-font-size"><?php echo esc_html_x( 'You can obtain a copy of the Marijuana Anonymous “Life with Hope” book at A New Leaf Publications website.', 'Learn more text', 'ma-toronto' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:group {"className":"ma-learn__actions","style":{"spacing":{"blockGap":"var:preset|spacing|30"}},"layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"center"}} -->
			<div class="wp-block-group ma-learn__actions">
				<!-- wp:buttons -->
				<div class="wp-block-buttons">
					<!-- wp:button -->
					<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="https://anewleafpublications.org/"><?php echo esc_html_x( 'Visit A New Leaf Publications', 'Learn more button', 'ma-toronto' ); ?></a></div>
					<!-- /wp:button -->
				</div>
				<!-- /wp:buttons -->

				<!-- wp:paragraph {"className":"is-style-underline-link"} -->
				<p class="is-style-underline-link"><a href="<?php echo esc_url( $ma_literature ); ?>"><?php echo esc_html_x( 'All MA literature', 'Learn more link', 'ma-toronto' ); ?></a></p>
				<!-- /wp:paragraph -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column {"verticalAlignment":"center","width":"45%"} -->
		<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:45%">
			<!-- wp:group {"className":"ma-app-card","style":{"spacing":{"padding":{"top":"26px","bottom":"26px","left":"28px","right":"28px"},"blockGap":"var:preset|spacing|10"},"border":{"color":"var:custom|hairline|color","style":"solid","width":"1px","radius":"var:custom|radius|lg"}},"backgroundColor":"base","layout":{"type":"default"}} -->
			<div class="wp-block-group ma-app-card has-border-color has-base-background-color has-background" style="border-color:var(--wp--custom--hairline--color);border-style:solid;border-width:1px;border-radius:var(--wp--custom--radius--lg);padding-top:26px;padding-right:28px;padding-bottom:26px;padding-left:28px">
				<!-- wp:heading {"level":3,"className":"ma-app-card__title","fontSize":"large"} -->
				<h3 class="wp-block-heading ma-app-card__title has-large-font-size"><?php echo esc_html_x( 'Also recommended', 'App card heading', 'ma-toronto' ); ?></h3>
				<!-- /wp:heading -->

				<!-- wp:paragraph {"className":"ma-app-card__text","textColor":"muted","fontSize":"small"} -->
				<p class="ma-app-card__text has-muted-color has-text-color has-small-font-size"><?php echo esc_html_x( 'The Marijuana Anonymous App — meetings, readings, and a sobriety counter in your pocket.', 'App card text', 'ma-toronto' ); ?></p>
				<!-- /wp:paragraph -->

				<!-- wp:buttons {"className":"ma-app-card__stores","layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"},"style":{"spacing":{"blockGap":"10px"}}} -->
				<div class="wp-block-buttons ma-app-card__stores">
					<!-- wp:button {"className":"is-style-ink-block","style":{"dimensions":{"width":"100%"}}} -->
					<div class="wp-block-button is-style-ink-block"><a class="wp-block-button__link wp-element-button" href="https://play.google.com/store/apps/details?id=org.marijuana_anonymous.MA_Mobile"><?php echo esc_html_x( 'Get it on Google Play', 'App store button', 'ma-toronto' ); ?></a></div>
					<!-- /wp:button -->

					<!-- wp:button {"className":"is-style-ink-block","style":{"dimensions":{"width":"100%"}}} -->
					<div class="wp-block-button is-style-ink-block"><a class="wp-block-button__link wp-element-button" href="https://apps.apple.com/ca/app/marijuana-anonymous-app/id874705440"><?php echo esc_html_x( 'Download on the App Store', 'App store button', 'ma-toronto' ); ?></a></div>
					<!-- /wp:button -->
				</div>
				<!-- /wp:buttons -->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:column -->

	</div>
	<!-- /wp:columns -->

</div>
<!-- /wp:group -->
