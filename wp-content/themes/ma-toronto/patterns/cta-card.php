<?php
/**
 * Title: Call to Action Card
 * Slug: ma-toronto/cta-card
 * Categories: ma-toronto
 * Description: A bordered card inviting the reader to a meeting. Used at the foot of interior pages.
 * Keywords: cta, card, meeting, call to action
 * Viewport Width: 820
 *
 * Insertable rather than baked into a template: which pages want it, and what
 * it should say, is an editorial decision. Locked with contentOnly like every
 * other section, so the text is editable but the layout is not.
 *
 * @package MA_Toronto
 */

?>
<!-- wp:group {"templateLock":"contentOnly","className":"ma-cta-card","style":{"spacing":{"padding":{"top":"30px","bottom":"30px","left":"34px","right":"34px"},"blockGap":"var:preset|spacing|10","margin":{"top":"var:preset|spacing|70"}},"border":{"color":"var:custom|hairline|color","style":"solid","width":"1px","radius":"var:custom|radius|lg"}},"backgroundColor":"surface","layout":{"type":"constrained"}} -->
<div class="wp-block-group ma-cta-card has-border-color has-surface-background-color has-background" style="border-color:var(--wp--custom--hairline--color);border-style:solid;border-width:1px;border-radius:var(--wp--custom--radius--lg);margin-top:var(--wp--preset--spacing--70);padding-top:30px;padding-right:34px;padding-bottom:30px;padding-left:34px">

	<!-- wp:heading {"textAlign":"center","level":2,"className":"ma-cta-card__title","fontSize":"x-large"} -->
	<h2 class="wp-block-heading has-text-align-center ma-cta-card__title has-x-large-font-size"><?php echo esc_html_x( 'You don\'t have to do this alone.', 'CTA card heading', 'ma-toronto' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph {"align":"center","className":"ma-cta-card__text","textColor":"muted","fontSize":"medium"} -->
	<p class="has-text-align-center ma-cta-card__text has-muted-color has-text-color has-medium-font-size"><?php echo esc_html_x( 'The Steps come alive in meetings, with people who have walked the same road.', 'CTA card body', 'ma-toronto' ); ?></p>
	<!-- /wp:paragraph -->

	<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
	<div class="wp-block-buttons">
		<!-- wp:button -->
		<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/meetings/' ) ); ?>"><?php echo esc_html_x( 'Find a Meeting', 'CTA card button', 'ma-toronto' ); ?></a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->

</div>
<!-- /wp:group -->
