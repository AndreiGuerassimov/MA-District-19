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
