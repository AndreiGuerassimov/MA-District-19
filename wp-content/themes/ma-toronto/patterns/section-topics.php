<?php
/**
 * Title: Topic Cards — How It Works
 * Slug: ma-toronto/section-topics
 * Categories: ma-toronto
 * Description: Five linked cards to the Steps, Traditions, Questions, Cross Addiction and Yesterday, Today and Tomorrow pages.
 * Keywords: cards, grid, steps, traditions, questions
 * Viewport Width: 1280
 *
 * Same card as the homepage pathways (.ma-card, stretched title link, hidden
 * h2 above h3 titles). Text only: the prototype's round marks were removed by
 * decision (14 Sep 2026).
 *
 * Links resolve to the child pages of How It Works when inserted.
 *
 * @package MA_Toronto
 */

$ma_link = static function ( string $path ): string {
	$page = get_page_by_path( $path );
	return $page ? (string) get_permalink( $page ) : home_url( '/' . $path . '/' );
};

$ma_topics = array(
	array(
		'title' => _x( 'The 12 Steps of MA', 'Topic card title', 'ma-toronto' ),
		'text'  => _x( 'The path of recovery members work at their own pace, usually with a sponsor.', 'Topic card description', 'ma-toronto' ),
		'cta'   => _x( 'Read the Steps', 'Topic card link', 'ma-toronto' ),
		'href'  => $ma_link( 'how-it-works/the-twelve-steps' ),
	),
	array(
		'title' => _x( 'The 12 Traditions of MA', 'Topic card title', 'ma-toronto' ),
		'text'  => _x( 'The principles that keep our groups and the fellowship as a whole healthy.', 'Topic card description', 'ma-toronto' ),
		'cta'   => _x( 'Read the Traditions', 'Topic card link', 'ma-toronto' ),
		'href'  => $ma_link( 'how-it-works/the-twelve-traditions' ),
	),
	array(
		'title' => _x( 'The 12 Questions of MA', 'Topic card title', 'ma-toronto' ),
		'text'  => _x( 'Twelve honest questions to help you decide for yourself whether MA is for you.', 'Topic card description', 'ma-toronto' ),
		'cta'   => _x( 'Ask yourself', 'Topic card link', 'ma-toronto' ),
		'href'  => $ma_link( 'how-it-works/the-twelve-questions' ),
	),
	array(
		'title' => _x( 'Dangers of Cross Addiction', 'Topic card title', 'ma-toronto' ),
		'text'  => _x( 'Why trading one substance for another so often undoes hard-won recovery.', 'Topic card description', 'ma-toronto' ),
		'cta'   => _x( 'Learn more', 'Topic card link', 'ma-toronto' ),
		'href'  => $ma_link( 'how-it-works/dangers-of-cross-addiction' ),
	),
	array(
		'title' => _x( 'Yesterday, Today and Tomorrow', 'Topic card title', 'ma-toronto' ),
		'text'  => _x( 'A short reading on living one day at a time — the heart of how this works.', 'Topic card description', 'ma-toronto' ),
		'cta'   => _x( 'Read it', 'Topic card link', 'ma-toronto' ),
		'href'  => $ma_link( 'how-it-works/yesterday-today-and-tomorrow' ),
	),
);

?>
<!-- wp:group {"templateLock":"contentOnly","lock":{"move":true,"remove":true},"align":"full","className":"ma-pathways ma-topics is-style-surface","style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80","left":"var:preset|spacing|80","right":"var:preset|spacing|80"}},"border":{"top":{"color":"var:custom|hairline|color","style":"solid","width":"1px"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group alignfull ma-pathways ma-topics is-style-surface" style="border-top-color:var(--wp--custom--hairline--color);border-top-style:solid;border-top-width:1px;padding-top:var(--wp--preset--spacing--80);padding-right:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--80);padding-left:var(--wp--preset--spacing--80)">

	<!-- wp:heading {"level":2,"className":"screen-reader-text"} -->
	<h2 class="wp-block-heading screen-reader-text"><?php echo esc_html_x( 'The program', 'Hidden heading for the How It Works topic cards', 'ma-toronto' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:group {"className":"ma-pathways__grid","style":{"spacing":{"blockGap":"18px"}},"layout":{"type":"grid","columnCount":5}} -->
	<div class="wp-block-group ma-pathways__grid">
<?php foreach ( $ma_topics as $ma_topic ) : ?>
		<!-- wp:group {"className":"ma-card ma-card--topic is-style-card","layout":{"type":"default"}} -->
		<div class="wp-block-group ma-card ma-card--topic is-style-card">
			<!-- wp:heading {"level":3,"className":"ma-card__title","fontSize":"large"} -->
			<h3 class="wp-block-heading ma-card__title has-large-font-size"><a href="<?php echo esc_url( $ma_topic['href'] ); ?>"><?php echo esc_html( $ma_topic['title'] ); ?></a></h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"ma-card__desc","textColor":"muted","fontSize":"small"} -->
			<p class="ma-card__desc has-muted-color has-text-color has-small-font-size"><?php echo esc_html( $ma_topic['text'] ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"className":"is-style-arrow-link ma-card__cta","fontSize":"x-small"} -->
			<p class="is-style-arrow-link ma-card__cta has-x-small-font-size"><?php echo esc_html( $ma_topic['cta'] ); ?> <span aria-hidden="true">&rarr;</span></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
<?php endforeach; ?>
	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
