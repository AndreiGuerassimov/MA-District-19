<?php
/**
 * Title: Page Hero — Centred
 * Slug: ma-toronto/section-page-hero
 * Categories: ma-toronto
 * Description: Centred page opener — eyebrow, page heading and an introduction — for pages using the "Full-width sections" template.
 * Keywords: hero, heading, intro, page
 * Viewport Width: 1280
 *
 * The heading is a real h1 in content (not the post title block) so the page's
 * visible heading can differ from its menu/SEO title, as on the homepage.
 *
 * @package MA_Toronto
 */

?>
<!-- wp:group {"templateLock":"contentOnly","lock":{"move":true,"remove":true},"align":"full","className":"ma-page-hero is-style-hero-wash","style":{"spacing":{"padding":{"top":"60px","bottom":"44px","left":"var:preset|spacing|80","right":"var:preset|spacing|80"},"blockGap":"0"}},"layout":{"type":"constrained","contentSize":"820px"}} -->
<div class="wp-block-group alignfull ma-page-hero is-style-hero-wash" style="padding-top:60px;padding-right:var(--wp--preset--spacing--80);padding-bottom:44px;padding-left:var(--wp--preset--spacing--80)">

	<!-- wp:paragraph {"align":"center","className":"is-style-eyebrow ma-page-hero__eyebrow","textColor":"accent"} -->
	<p class="has-text-align-center is-style-eyebrow ma-page-hero__eyebrow has-accent-color has-text-color"><?php echo esc_html_x( 'A proven program', 'How It Works eyebrow', 'ma-toronto' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:heading {"textAlign":"center","level":1,"className":"ma-page-hero__title","fontSize":"display"} -->
	<h1 class="wp-block-heading has-text-align-center ma-page-hero__title has-display-font-size"><?php echo esc_html_x( 'How It Works', 'How It Works heading', 'ma-toronto' ); ?></h1>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","className":"ma-page-hero__intro","textColor":"body","fontSize":"large"} -->
	<p class="has-text-align-center ma-page-hero__intro has-body-color has-text-color has-large-font-size"><?php echo esc_html_x( 'Marijuana Anonymous uses the basic 12 Steps of Recovery founded by Alcoholics Anonymous, because it has been proven that the 12 Step Recovery program works!', 'How It Works intro', 'ma-toronto' ); ?></p>
	<!-- /wp:paragraph -->

</div>
<!-- /wp:group -->
