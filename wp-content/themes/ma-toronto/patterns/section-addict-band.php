<?php
/**
 * Title: Who Is a Marijuana Addict? — Band
 * Slug: ma-toronto/section-addict-band
 * Categories: ma-toronto
 * Description: Full-width forest band with a centred heading, paragraph and an amber button to the 12 Questions.
 * Keywords: band, forest, questions, call to action
 * Viewport Width: 1280
 *
 * Button: the "Amber Pill" style (styles/blocks/07-pill-amber.json) —
 * primary-dark on amber, 5.37:1.
 *
 * @package MA_Toronto
 */

$ma_questions = get_page_by_path( 'how-it-works/the-twelve-questions' );
$ma_questions = $ma_questions ? (string) get_permalink( $ma_questions ) : home_url( '/how-it-works/the-twelve-questions/' );

?>
<!-- wp:group {"templateLock":"contentOnly","lock":{"move":true,"remove":true},"align":"full","className":"ma-band is-style-forest","style":{"spacing":{"padding":{"top":"var:preset|spacing|100","bottom":"var:preset|spacing|100","left":"var:preset|spacing|80","right":"var:preset|spacing|80"},"blockGap":"var:preset|spacing|50"}},"layout":{"type":"constrained","contentSize":"760px"}} -->
<div class="wp-block-group alignfull ma-band is-style-forest" style="padding-top:var(--wp--preset--spacing--100);padding-right:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--100);padding-left:var(--wp--preset--spacing--80)">

	<!-- wp:heading {"textAlign":"center","level":2,"className":"ma-band__title","fontSize":"xx-large"} -->
	<h2 class="wp-block-heading has-text-align-center ma-band__title has-xx-large-font-size"><?php echo esc_html_x( 'Who is a Marijuana Addict?', 'Band heading', 'ma-toronto' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","className":"ma-band__body","fontSize":"medium"} -->
	<p class="has-text-align-center ma-band__body has-medium-font-size"><?php echo esc_html_x( 'We who are marijuana addicts know the answer to this question. Marijuana controls our lives! We lose interest in all else; our dreams go up in smoke. Ours is a progressive illness often leading us to addictions to other drugs, including alcohol. Our lives, our thinking, and our desires center around marijuana; scoring it, dealing it, and finding ways to stay high.', 'Band body', 'ma-toronto' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons {"className":"ma-band__actions","layout":{"type":"flex","justifyContent":"center"}} -->
	<div class="wp-block-buttons ma-band__actions">
		<!-- wp:button {"className":"is-style-pill-amber"} -->
		<div class="wp-block-button is-style-pill-amber"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $ma_questions ); ?>"><?php echo esc_html_x( 'Take the 12 Questions', 'Band button', 'ma-toronto' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->

</div>
<!-- /wp:group -->
