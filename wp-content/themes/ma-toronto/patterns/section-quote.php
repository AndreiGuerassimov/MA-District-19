<?php
/**
 * Title: Quote Slider
 * Slug: ma-toronto/section-quote
 * Categories: ma-toronto
 * Description: Fellowship slogans in a swipeable, keyboard-operable slider.
 * Keywords: quote, slogan, slider, carousel
 * Viewport Width: 1280
 *
 * The markup here is deliberately plain: a heading and a row of core/quote
 * blocks. Everything that makes it a slider is added by CSS and JavaScript.
 *
 * - CSS turns the row into a scroll-snap track, which gives native swipe on
 *   touch and native keyboard scrolling with no JavaScript at all.
 * - assets/js/quote-slider.js adds the previous/next buttons, the dots, and the
 *   carousel ARIA. Those controls are useless without JavaScript, so JavaScript
 *   creates them rather than shipping dead buttons in the markup.
 *
 * Without JavaScript this degrades to a readable, swipeable row of quotes.
 *
 * Editors add or remove slogans by adding or removing quote blocks; slide
 * numbering is worked out at runtime, so nothing needs renumbering by hand.
 *
 * Scope and decisions: docs/quote-slider-scope.md. No autoplay, by decision --
 * the prototype's six-second rotation fails WCAG 2.2.2 (Pause, Stop, Hide).
 *
 * @package MA_Toronto
 */

$ma_slogans = array(
	_x( 'One day at a time.', 'Fellowship slogan', 'ma-toronto' ),
	_x( 'Keep it simple.', 'Fellowship slogan', 'ma-toronto' ),
	_x( 'Progress, not perfection.', 'Fellowship slogan', 'ma-toronto' ),
);

?>
<!-- wp:group {"align":"full","className":"ma-quote","style":{"spacing":{"padding":{"top":"var:preset|spacing|100","bottom":"var:preset|spacing|100","left":"var:preset|spacing|80","right":"var:preset|spacing|80"},"blockGap":"var:preset|spacing|50"}},"layout":{"type":"constrained","contentSize":"900px"}} -->
<div class="wp-block-group alignfull ma-quote" style="padding-top:var(--wp--preset--spacing--100);padding-right:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--100);padding-left:var(--wp--preset--spacing--80)">

	<!-- wp:heading {"textAlign":"center","level":2,"className":"is-style-eyebrow ma-quote__label","textColor":"accent","fontSize":"xx-small"} -->
	<h2 class="wp-block-heading has-text-align-center is-style-eyebrow ma-quote__label has-accent-color has-text-color has-xx-small-font-size"><?php echo esc_html_x( 'Quotes &amp; slogans', 'Quote slider section label', 'ma-toronto' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:group {"className":"ma-quote__track","layout":{"type":"default"}} -->
	<div class="wp-block-group ma-quote__track">
<?php foreach ( $ma_slogans as $ma_slogan ) : ?>
		<!-- wp:quote {"className":"ma-quote__slide"} -->
		<blockquote class="wp-block-quote ma-quote__slide">
			<!-- wp:paragraph {"align":"center","fontSize":"quote"} -->
			<p class="has-text-align-center has-quote-font-size"><?php echo esc_html( $ma_slogan ); ?></p>
			<!-- /wp:paragraph -->
		</blockquote>
		<!-- /wp:quote -->
<?php endforeach; ?>
	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
