<?php
/**
 * Title: Literature Grid
 * Slug: ma-toronto/section-literature
 * Categories: ma-toronto
 * Description: A grid of MA publications, each with placeholder cover art, a description and a link.
 * Keywords: literature, books, pamphlets, grid, cards
 * Viewport Width: 1280
 *
 * Cover art is a coloured block, exactly as in the prototype — its own note says
 * the covers are placeholders. The gradients are theme.json presets applied by
 * CSS on nth-child, so they cycle automatically and adding a seventh item needs
 * no new markup or classes.
 *
 * The drawn gradients were contrast-corrected: white on the amber cover measured
 * 2.14:1. See docs/build-plan.md 0.1.
 *
 * Whole-card linking uses the same stretched-link technique as the homepage
 * pathway cards, so each card exposes one link named after its title.
 *
 * @package MA_Toronto
 */

$ma_items = array(
	array(
		'kind'  => _x( 'Book', 'Literature type', 'ma-toronto' ),
		'title' => _x( 'Life with Hope', 'Literature title', 'ma-toronto' ),
		'desc'  => _x( 'The basic text of Marijuana Anonymous — the 12 Steps and 12 Traditions as they apply to marijuana addiction, with members\' experience throughout.', 'Literature description', 'ma-toronto' ),
		'cta'   => _x( 'Read online', 'Literature link', 'ma-toronto' ),
	),
	array(
		'kind'  => _x( 'Daily reader', 'Literature type', 'ma-toronto' ),
		'title' => _x( 'A New Leaf', 'Literature title', 'ma-toronto' ),
		'desc'  => _x( 'A page for every day of the year — short reflections and meditations to carry recovery into daily life.', 'Literature description', 'ma-toronto' ),
		'cta'   => _x( 'Learn more', 'Literature link', 'ma-toronto' ),
	),
	array(
		'kind'  => _x( 'Pamphlet', 'Literature type', 'ma-toronto' ),
		'title' => _x( 'Detoxing from Marijuana', 'Literature title', 'ma-toronto' ),
		'desc'  => _x( 'What to expect physically and emotionally in early abstinence, and how the fellowship helps you through it.', 'Literature description', 'ma-toronto' ),
		'cta'   => _x( 'Read online', 'Literature link', 'ma-toronto' ),
	),
	array(
		'kind'  => _x( 'Pamphlet', 'Literature type', 'ma-toronto' ),
		'title' => _x( 'The Twelve Questions', 'Literature title', 'ma-toronto' ),
		'desc'  => _x( 'Twelve honest questions to help you decide for yourself whether MA is for you.', 'Literature description', 'ma-toronto' ),
		'cta'   => _x( 'Read online', 'Literature link', 'ma-toronto' ),
	),
	array(
		'kind'  => _x( 'Pamphlet', 'Literature type', 'ma-toronto' ),
		'title' => _x( 'For the Newcomer', 'Literature title', 'ma-toronto' ),
		'desc'  => _x( 'A gentle introduction to your first meetings — what happens, what to expect, and why you are welcome.', 'Literature description', 'ma-toronto' ),
		'cta'   => _x( 'Read online', 'Literature link', 'ma-toronto' ),
	),
	array(
		'kind'  => _x( 'Booklet', 'Literature type', 'ma-toronto' ),
		'title' => _x( 'Twelve Steps Workbook', 'Literature title', 'ma-toronto' ),
		'desc'  => _x( 'Guided questions for working each of the Twelve Steps, on your own or with a sponsor.', 'Literature description', 'ma-toronto' ),
		'cta'   => _x( 'Learn more', 'Literature link', 'ma-toronto' ),
	),
);

?>
<!-- wp:group {"templateLock":"contentOnly","align":"full","className":"ma-lit","style":{"spacing":{"blockGap":"26px"}},"layout":{"type":"grid","columnCount":3}} -->
<div class="wp-block-group alignfull ma-lit">
<?php foreach ( $ma_items as $ma_item ) : ?>
	<!-- wp:group {"className":"ma-lit-card","layout":{"type":"default"}} -->
	<div class="wp-block-group ma-lit-card">
		<!-- wp:group {"className":"ma-lit-card__cover","layout":{"type":"default"}} -->
		<div class="wp-block-group ma-lit-card__cover">
			<!-- wp:paragraph {"className":"ma-lit-card__kind"} -->
			<p class="ma-lit-card__kind"><?php echo esc_html( $ma_item['kind'] ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"ma-lit-card__cover-title"} -->
			<p class="ma-lit-card__cover-title"><?php echo esc_html( $ma_item['title'] ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:group {"className":"ma-lit-card__body","layout":{"type":"default"}} -->
		<div class="wp-block-group ma-lit-card__body">
			<!-- wp:heading {"level":3,"className":"ma-lit-card__title","fontSize":"large"} -->
			<h3 class="wp-block-heading ma-lit-card__title has-large-font-size"><a href="#"><?php echo esc_html( $ma_item['title'] ); ?></a></h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"ma-lit-card__desc","textColor":"muted","fontSize":"small"} -->
			<p class="ma-lit-card__desc has-muted-color has-text-color has-small-font-size"><?php echo esc_html( $ma_item['desc'] ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"is-style-arrow-link ma-lit-card__cta","fontSize":"x-small"} -->
			<p class="is-style-arrow-link ma-lit-card__cta has-x-small-font-size"><?php echo esc_html( $ma_item['cta'] ); ?> <span aria-hidden="true">&rarr;</span></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
<?php endforeach; ?>
</div>
<!-- /wp:group -->

<!-- wp:paragraph {"align":"full","className":"ma-lit__note","textColor":"muted","fontSize":"small"} -->
<p class="alignfull ma-lit__note has-muted-color has-text-color has-small-font-size"><?php echo esc_html_x( 'All proceeds from MA literature support the fellowship\'s primary purpose.', 'Literature footnote', 'ma-toronto' ); ?></p>
<!-- /wp:paragraph -->
