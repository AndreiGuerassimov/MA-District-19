<?php
/**
 * Title: FAQ Accordion
 * Slug: ma-toronto/section-faq
 * Categories: ma-toronto
 * Description: Frequently asked questions in a collapsible list.
 * Keywords: faq, accordion, questions
 * Viewport Width: 820
 *
 * Built on core/accordion, which ships with WordPress 7.1 and already provides
 * everything the prototype's hand-rolled version did: one-panel-at-a-time via
 * `autoclose`, a toggle icon that rotates 45 degrees on open, keyboard
 * operation, and the correct ARIA. No custom block or script is needed.
 *
 * headingLevel 3 keeps the outline correct: the page <h1> comes from the
 * template, so questions sit one level below.
 *
 * @package MA_Toronto
 */

$ma_faqs = array(
	array(
		'q' => _x( 'What is Marijuana Anonymous?', 'FAQ question', 'ma-toronto' ),
		'a' => _x( 'MA is a fellowship of people who share their experience, strength, and hope with each other so they may solve their common problem and help others recover from marijuana addiction. We use the same Twelve Steps of recovery founded by Alcoholics Anonymous.', 'FAQ answer', 'ma-toronto' ),
	),
	array(
		'q' => _x( 'How much does it cost?', 'FAQ question', 'ma-toronto' ),
		'a' => _x( 'Nothing. There are no dues or fees for membership. MA is entirely self-supporting through voluntary contributions from members — the 7th Tradition.', 'FAQ answer', 'ma-toronto' ),
	),
	array(
		'q' => _x( 'Do I have to speak or share personal details?', 'FAQ question', 'ma-toronto' ),
		'a' => _x( 'No. You can come to a meeting and simply listen for as long as you like. Many people attend several meetings before sharing anything. Only your first name is ever needed — anonymity is a core principle.', 'FAQ answer', 'ma-toronto' ),
	),
	array(
		'q' => _x( 'How do I know if I am a marijuana addict?', 'FAQ question', 'ma-toronto' ),
		'a' => _x( 'Only you can decide. Many members found it helpful to read The Twelve Questions — an honest self-inventory about how marijuana affects your life. If marijuana is causing problems and you cannot seem to stop, MA may be for you.', 'FAQ answer', 'ma-toronto' ),
	),
	array(
		'q' => _x( 'Is MA religious?', 'FAQ question', 'ma-toronto' ),
		'a' => _x( 'No. MA is not affiliated with any religious or secular organization. The program refers to a higher power of your own understanding — that can be anything, including the group itself. Members hold every belief and none.', 'FAQ answer', 'ma-toronto' ),
	),
	array(
		'q' => _x( 'What happens at a meeting?', 'FAQ question', 'ma-toronto' ),
		'a' => _x( 'Meetings usually open with a reading, followed by members sharing their experience on a topic or a Step. They typically last about an hour. In-person meetings are held around the GTA; online meetings run on Zoom. Newcomers are always welcome.', 'FAQ answer', 'ma-toronto' ),
	),
);

?>
<!-- wp:accordion {"autoclose":true,"headingLevel":3,"className":"ma-faq","style":{"spacing":{"blockGap":"var:preset|spacing|20"}}} -->
<div class="wp-block-accordion ma-faq" role="group">
<?php foreach ( $ma_faqs as $i => $ma_faq ) : ?>
	<!-- wp:accordion-item <?php echo 0 === $i ? '{"openByDefault":true} ' : ''; ?>-->
	<div class="wp-block-accordion-item<?php echo 0 === $i ? ' is-open' : ''; ?>">
		<!-- wp:accordion-heading -->
		<h3 class="wp-block-accordion-heading has-icon has-icon-right"><button type="button" class="wp-block-accordion-heading__toggle"><span class="wp-block-accordion-heading__toggle-title"><?php echo esc_html( $ma_faq['q'] ); ?></span><span class="wp-block-accordion-heading__toggle-icon" aria-hidden="true">+</span></button></h3>
		<!-- /wp:accordion-heading -->

		<!-- wp:accordion-panel -->
		<div class="wp-block-accordion-panel" role="region">
			<!-- wp:paragraph {"className":"ma-faq__answer","textColor":"muted","fontSize":"medium"} -->
			<p class="ma-faq__answer has-muted-color has-text-color has-medium-font-size"><?php echo esc_html( $ma_faq['a'] ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:accordion-panel -->
	</div>
	<!-- /wp:accordion-item -->
<?php endforeach; ?>
</div>
<!-- /wp:accordion -->
