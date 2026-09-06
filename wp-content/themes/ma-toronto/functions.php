<?php
/**
 * MA Toronto — theme setup.
 *
 * Registrations only. No markup, no styling.
 *
 * Design tokens live in theme.json. Named block and section styles live in
 * styles/ as theme.json partials, which WordPress registers automatically —
 * they need no PHP here. This file exists for the few things that genuinely
 * require it.
 *
 * @package MA_Toronto
 */

declare( strict_types = 1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the pattern category used by every pattern in this theme.
 *
 * Patterns declare `Categories: ma-toronto` in their file header, so this must
 * be registered before patterns are read.
 */
function ma_toronto_register_pattern_categories(): void {
	register_block_pattern_category(
		'ma-toronto',
		array(
			'label'       => __( 'MA Toronto', 'ma-toronto' ),
			'description' => __( 'Page sections built for the MA Toronto site.', 'ma-toronto' ),
		)
	);
}
add_action( 'init', 'ma_toronto_register_pattern_categories' );

/**
 * Enqueues per-block stylesheets.
 *
 * Each file in assets/css/blocks/ is named after the core block it styles, with
 * the namespace slash replaced by a dash — so `core/group` reads group.css.
 * wp_enqueue_block_style() loads a file only on pages where that block actually
 * renders, which is why per-block CSS is split up rather than bundled.
 *
 * Anything theme.json can express should be in theme.json instead of here.
 */
function ma_toronto_enqueue_block_styles(): void {
	$blocks = array(
		'core/group',
		'core/navigation',
		'core/columns',
		'core/paragraph',
		'core/quote',
		'core/list',
		'core/accordion',
	);

	foreach ( $blocks as $block ) {
		$handle = str_replace( '/', '-', $block );
		$path   = "assets/css/blocks/{$handle}.css";

		if ( ! file_exists( get_theme_file_path( $path ) ) ) {
			continue;
		}

		wp_enqueue_block_style(
			$block,
			array(
				'handle' => "ma-toronto-{$handle}",
				'src'    => get_theme_file_uri( $path ),
				'path'   => get_theme_file_path( $path ),
			)
		);
	}
}
add_action( 'after_setup_theme', 'ma_toronto_enqueue_block_styles' );

/**
 * Enqueues stylesheets for site chrome that appears on every page.
 *
 * The header and footer are not tied to a single block, so they cannot be
 * loaded conditionally with wp_enqueue_block_style(). They are unconditional
 * anyway, since every page renders them.
 */
function ma_toronto_enqueue_chrome_styles(): void {
	foreach ( array( 'header', 'footer' ) as $part ) {
		$path = "assets/css/{$part}.css";

		if ( ! file_exists( get_theme_file_path( $path ) ) ) {
			continue;
		}

		wp_enqueue_style(
			"ma-toronto-{$part}",
			get_theme_file_uri( $path ),
			array(),
			(string) filemtime( get_theme_file_path( $path ) )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'ma_toronto_enqueue_chrome_styles' );

/**
 * Loads the contact page styles only where the contact form appears.
 *
 * Keyed off the wrapper class rather than the shortcode, so it works whether
 * the section came from the pattern file or from saved page content.
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block.
 * @return string Unmodified block HTML.
 */
function ma_toronto_enqueue_contact_styles( string $block_content, array $block ): string {
	if ( 'core/group' !== ( $block['blockName'] ?? '' ) ) {
		return $block_content;
	}

	$class = $block['attrs']['className'] ?? '';
	$path  = 'assets/css/contact.css';

	if ( is_string( $class ) && str_contains( $class, 'ma-contact' ) && file_exists( get_theme_file_path( $path ) ) ) {
		wp_enqueue_style(
			'ma-toronto-contact',
			get_theme_file_uri( $path ),
			array(),
			(string) filemtime( get_theme_file_path( $path ) )
		);
	}

	return $block_content;
}
add_filter( 'render_block', 'ma_toronto_enqueue_contact_styles', 10, 2 );

/**
 * Stops Contact Form 7 inserting its own paragraphs and line breaks.
 *
 * CF7 runs a wpautop pass over the form template, which wraps the theme's
 * markup in <p> tags and breaks the two-column name/email row. The form
 * template supplies its own structure, so the pass is not wanted.
 */
add_filter( 'wpcf7_autop_or_not', '__return_false' );

/**
 * Registers the quote slider's view module.
 *
 * A script module rather than a classic script: it is an ES module, deferred by
 * default, and needs no dependencies. The slider is progressive enhancement --
 * the section is a readable, swipeable row of quotes without it.
 */
function ma_toronto_register_script_modules(): void {
	$path = 'assets/js/quote-slider.js';

	if ( ! file_exists( get_theme_file_path( $path ) ) ) {
		return;
	}

	wp_register_script_module(
		'ma-toronto/quote-slider',
		get_theme_file_uri( $path ),
		array(),
		(string) filemtime( get_theme_file_path( $path ) )
	);
}
add_action( 'init', 'ma_toronto_register_script_modules' );

/**
 * Loads the quote slider module only on pages that actually contain one.
 *
 * Keyed off the wrapper's class rather than a block name, so it works whether
 * the section arrives from the pattern file or from page content that the
 * pattern was expanded into.
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block.
 * @return string Unmodified block HTML.
 */
function ma_toronto_enqueue_quote_slider( string $block_content, array $block ): string {
	if ( 'core/group' !== ( $block['blockName'] ?? '' ) ) {
		return $block_content;
	}

	$class = $block['attrs']['className'] ?? '';

	if ( is_string( $class ) && str_contains( $class, 'ma-quote' ) ) {
		wp_enqueue_script_module( 'ma-toronto/quote-slider' );
	}

	return $block_content;
}
add_filter( 'render_block', 'ma_toronto_enqueue_quote_slider', 10, 2 );

/**
 * Marks the hero image as the LCP candidate.
 *
 * fetchpriority belongs at render time, not in saved block markup: core/image's
 * save() never emits it, so writing it into a pattern makes the block fail
 * validation in the editor. WordPress's own heuristic does not catch this image
 * because it is a theme file rather than an attachment, so it is set here.
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block.
 * @return string Block HTML, with the hero image prioritised.
 */
function ma_toronto_prioritise_hero_image( string $block_content, array $block ): string {
	if ( 'core/image' !== ( $block['blockName'] ?? '' ) ) {
		return $block_content;
	}

	$class = $block['attrs']['className'] ?? '';

	if ( ! is_string( $class ) || ! str_contains( $class, 'ma-hero__media' ) ) {
		return $block_content;
	}

	$tags = new WP_HTML_Tag_Processor( $block_content );

	if ( $tags->next_tag( array( 'tag_name' => 'IMG' ) ) ) {
		$tags->set_attribute( 'fetchpriority', 'high' );
		// Above the fold by definition; lazy-loading it would defeat the point.
		$tags->remove_attribute( 'loading' );
	}

	return $tags->get_updated_html();
}
add_filter( 'render_block', 'ma_toronto_prioritise_hero_image', 10, 2 );

/**
 * Hides the decorative "MA" roundels from assistive technology.
 *
 * The roundel repeats the wordmark beside it, so announcing it would produce
 * "MA, Marijuana Anonymous Toronto". aria-hidden cannot live in the saved
 * markup: core/paragraph's save() emits only its own class attribute, so any
 * extra attribute on the wrapper makes the block fail validation in the editor.
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block.
 * @return string Block HTML, with decorative marks hidden.
 */
function ma_toronto_hide_decorative_marks( string $block_content, array $block ): string {
	if ( 'core/paragraph' !== ( $block['blockName'] ?? '' ) ) {
		return $block_content;
	}

	$class = $block['attrs']['className'] ?? '';

	if ( ! is_string( $class ) ) {
		return $block_content;
	}

	foreach ( array( 'ma-logo__mark', 'ma-footer__mark' ) as $decorative ) {
		if ( ! str_contains( $class, $decorative ) ) {
			continue;
		}

		$tags = new WP_HTML_Tag_Processor( $block_content );

		if ( $tags->next_tag( array( 'tag_name' => 'P' ) ) ) {
			$tags->set_attribute( 'aria-hidden', 'true' );
		}

		return $tags->get_updated_html();
	}

	return $block_content;
}
add_filter( 'render_block', 'ma_toronto_hide_decorative_marks', 10, 2 );

/**
 * Drops the page hero's intro when the page has no manual excerpt.
 *
 * core/post-excerpt falls back to auto-generating from the content, which in a
 * hero would dump the opening sentences of the page under its own title. The
 * intro should appear only where an editor has deliberately written one, in the
 * Excerpt panel.
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block.
 * @return string Block HTML, or an empty string.
 */
function ma_toronto_hide_empty_page_intro( string $block_content, array $block ): string {
	if ( 'core/post-excerpt' !== ( $block['blockName'] ?? '' ) ) {
		return $block_content;
	}

	$class = $block['attrs']['className'] ?? '';

	if ( is_string( $class ) && str_contains( $class, 'ma-page__intro' ) && ! has_excerpt() ) {
		return '';
	}

	return $block_content;
}
add_filter( 'render_block', 'ma_toronto_hide_empty_page_intro', 10, 2 );
