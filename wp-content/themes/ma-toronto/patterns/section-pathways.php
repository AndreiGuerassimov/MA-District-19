<?php
/**
 * Title: Pathway Cards
 * Slug: ma-toronto/section-pathways
 * Categories: ma-toronto
 * Description: A grid of five linked cards routing visitors to the main sections of the site.
 * Keywords: cards, grid, links, pathways
 * Viewport Width: 1280
 *
 * Accessibility notes (see docs/build-plan.md 0.1 and 4.3):
 * - The prototype wraps each whole card in one <a>. Here the link sits on the
 *   card's heading and is stretched over the card with a pseudo-element, so the
 *   whole card stays clickable while the accessible name is just the title
 *   rather than title + description + call to action run together.
 * - Card titles are <h3>. The section has no visible heading in the design, so
 *   a visually hidden <h2> keeps the outline from jumping h1 -> h3.
 * - The arrow is decorative and hidden from assistive tech.
 *
 * @package MA_Toronto
 */

$ma_cards = array(
	array(
		'title' => _x( 'Meetings', 'Pathway card title', 'ma-toronto' ),
		'text'  => _x( 'All MA meetings in the Toronto area, in person and online.', 'Pathway card description', 'ma-toronto' ),
		'cta'   => _x( 'See schedule', 'Pathway card link', 'ma-toronto' ),
		'href'  => home_url( '/meetings/' ),
	),
	array(
		'title' => _x( 'A Solution', 'Pathway card title', 'ma-toronto' ),
		'text'  => _x( 'How it works: the 12 Steps of recovery, founded by AA.', 'Pathway card description', 'ma-toronto' ),
		'cta'   => _x( 'How it works', 'Pathway card link', 'ma-toronto' ),
		'href'  => home_url( '/the-twelve-steps/' ),
	),
	array(
		'title' => _x( 'Our Stories', 'Pathway card title', 'ma-toronto' ),
		'text'  => _x( 'Members share what it was like — before and after recovery.', 'Pathway card description', 'ma-toronto' ),
		'cta'   => _x( 'Read stories', 'Pathway card link', 'ma-toronto' ),
		'href'  => home_url( '/our-stories/' ),
	),
	array(
		'title' => _x( 'FAQ', 'Pathway card title', 'ma-toronto' ),
		'text'  => _x( 'You have questions. We have answers, from people who\'ve been there.', 'Pathway card description', 'ma-toronto' ),
		'cta'   => _x( 'Get answers', 'Pathway card link', 'ma-toronto' ),
		'href'  => home_url( '/faq/' ),
	),
	array(
		'title' => _x( 'Literature', 'Pathway card title', 'ma-toronto' ),
		'text'  => _x( 'Life with Hope, pamphlets, and all official MA literature.', 'Pathway card description', 'ma-toronto' ),
		'cta'   => _x( 'Browse', 'Pathway card link', 'ma-toronto' ),
		'href'  => home_url( '/literature/' ),
	),
	array(
		'title' => _x( 'Contact Us', 'Pathway card title', 'ma-toronto' ),
		'text'  => _x( 'Reach the Toronto fellowship directly. We are glad to hear from you.', 'Pathway card description', 'ma-toronto' ),
		'cta'   => _x( 'Get in touch', 'Pathway card link', 'ma-toronto' ),
		'href'  => home_url( '/contact/' ),
	),
);

?>
<!-- wp:group {"templateLock":"contentOnly","lock":{"move":true,"remove":true},"align":"full","className":"ma-pathways is-style-surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|90","bottom":"var:preset|spacing|90","left":"var:preset|spacing|80","right":"var:preset|spacing|80"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ma-pathways is-style-surface" style="padding-top:var(--wp--preset--spacing--90);padding-right:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--90);padding-left:var(--wp--preset--spacing--80)">

	<!-- wp:heading {"level":2,"className":"screen-reader-text"} -->
	<h2 class="wp-block-heading screen-reader-text"><?php echo esc_html_x( 'Explore MA Toronto', 'Hidden heading for the pathway card grid', 'ma-toronto' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:group {"className":"ma-pathways__grid","style":{"spacing":{"blockGap":"18px"}},"layout":{"type":"grid","columnCount":6}} -->
	<div class="wp-block-group ma-pathways__grid">
<?php foreach ( $ma_cards as $ma_card ) : ?>
		<!-- wp:group {"className":"ma-card is-style-card","layout":{"type":"default"}} -->
		<div class="wp-block-group ma-card is-style-card">
			<!-- wp:heading {"level":3,"className":"ma-card__title","fontSize":"large"} -->
			<h3 class="wp-block-heading ma-card__title has-large-font-size"><a href="<?php echo esc_url( $ma_card['href'] ); ?>"><?php echo esc_html( $ma_card['title'] ); ?></a></h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"ma-card__desc","textColor":"muted","fontSize":"small"} -->
			<p class="ma-card__desc has-muted-color has-text-color has-small-font-size"><?php echo esc_html( $ma_card['text'] ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"is-style-arrow-link ma-card__cta","fontSize":"x-small"} -->
			<p class="is-style-arrow-link ma-card__cta has-x-small-font-size"><?php echo esc_html( $ma_card['cta'] ); ?> <span aria-hidden="true">&rarr;</span></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
<?php endforeach; ?>
	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
