<?php
/**
 * Title: Our Stories — Cards and Note
 * Slug: ma-toronto/section-stories
 * Categories: ma-toronto
 * Description: Heading, introduction, three linked story cards from Life with Hope, and the fellowship's note about personal stories.
 * Keywords: stories, cards, life with hope
 * Viewport Width: 1280
 *
 * Stories are hosted by Marijuana Anonymous World Services
 * (marijuana-anonymous.org); the cards link there, as the old site did. URLs
 * checked 13 Sep 2026 — MAWS has moved them before.
 *
 * The note's label uses `ink`, not the prototype's terracotta: `accent` on
 * `base-alt` measures 4.45:1, under AA (same call as the meeting page).
 *
 * @package MA_Toronto
 */

$ma_stories = array(
	array( _x( 'I’m Not An Addict', 'Story title', 'ma-toronto' ), 'https://marijuana-anonymous.org/im-not-an-addict/' ),
	array( _x( 'A Slave To Marijuana', 'Story title', 'ma-toronto' ), 'https://marijuana-anonymous.org/a-slave-to-marijuana/' ),
	array( _x( 'A Life Worth Living', 'Story title', 'ma-toronto' ), 'https://marijuana-anonymous.org/a-life-worth-living/' ),
);

?>
<!-- wp:group {"templateLock":"contentOnly","lock":{"move":true,"remove":true},"align":"full","className":"ma-stories","style":{"spacing":{"padding":{"top":"var:preset|spacing|100","bottom":"var:preset|spacing|100","left":"var:preset|spacing|80","right":"var:preset|spacing|80"},"blockGap":"var:preset|spacing|30"}},"layout":{"type":"constrained","contentSize":"860px"}} -->
<div class="wp-block-group alignfull ma-stories" style="padding-top:var(--wp--preset--spacing--100);padding-right:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--100);padding-left:var(--wp--preset--spacing--80)">

	<!-- wp:heading {"textAlign":"center","level":2,"className":"ma-stories__title","fontSize":"xx-large"} -->
	<h2 class="wp-block-heading has-text-align-center ma-stories__title has-xx-large-font-size"><?php echo esc_html_x( 'Some Of Our Stories', 'Stories heading', 'ma-toronto' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","className":"ma-stories__intro","textColor":"body","fontSize":"medium"} -->
	<p class="has-text-align-center ma-stories__intro has-body-color has-text-color has-medium-font-size"><?php echo esc_html_x( 'Here are a few stories from real people, taken from the Marijuana Anonymous “Life with Hope” book. We hope these stories will give you an idea on how some of us overcame our Marijuana Addiction, one day at a time.', 'Stories intro', 'ma-toronto' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:group {"className":"ma-stories__grid","style":{"spacing":{"blockGap":"18px"}},"layout":{"type":"grid","columnCount":3}} -->
	<div class="wp-block-group ma-stories__grid">
<?php foreach ( $ma_stories as [ $ma_title, $ma_url ] ) : ?>
		<!-- wp:group {"className":"ma-card ma-card--story is-style-card","layout":{"type":"default"}} -->
		<div class="wp-block-group ma-card ma-card--story is-style-card">
			<!-- wp:heading {"level":3,"className":"ma-card__title","fontSize":"x-large"} -->
			<h3 class="wp-block-heading ma-card__title has-x-large-font-size"><a href="<?php echo esc_url( $ma_url ); ?>"><?php echo esc_html( $ma_title ); ?></a></h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"is-style-arrow-link ma-card__cta","fontSize":"x-small"} -->
			<p class="is-style-arrow-link ma-card__cta has-x-small-font-size"><?php echo esc_html_x( 'Read this story', 'Story card link', 'ma-toronto' ); ?> <span aria-hidden="true">&rarr;</span></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->
<?php endforeach; ?>
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"ma-note","style":{"spacing":{"blockGap":"var:preset|spacing|10"}},"backgroundColor":"base-alt","layout":{"type":"default"}} -->
	<div class="wp-block-group ma-note has-base-alt-background-color has-background">
		<!-- wp:paragraph {"className":"ma-note__label","textColor":"ink"} -->
		<p class="ma-note__label has-ink-color has-text-color"><?php echo esc_html_x( 'Regarding the personal stories', 'Stories note label', 'ma-toronto' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:paragraph {"className":"ma-note__text","textColor":"muted","fontSize":"small"} -->
		<p class="ma-note__text has-muted-color has-text-color has-small-font-size"><?php echo esc_html_x( 'The following stories were written by recovering marijuana addicts within the fellowship of Marijuana Anonymous and represent their individual experiences and viewpoint. Marijuana Anonymous has no opinion on outside issues (see “Tradition Ten”) including the opinions expressed, language used, or experiences related by its individual members.', 'Stories note', 'ma-toronto' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

</div>
<!-- /wp:group -->
