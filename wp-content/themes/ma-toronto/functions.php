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
 * Mirrors the theme's stylesheets into the block editor.
 *
 * theme.json tokens reach the editor on their own, but hand-written CSS does
 * not: `add_editor_style()` is ignored unless the theme declares `editor-styles`
 * support (see the gate in wp-includes/block-editor.php). Without this, sections
 * that rely on CSS -- the contact split, the card grids, the accordion -- render
 * unstyled while editing, which makes the editor a poor guide to the result.
 *
 * Every stylesheet is registered rather than a fixed list, so a new file is
 * picked up without touching this function.
 */
function ma_toronto_editor_styles(): void {
	add_theme_support( 'editor-styles' );

	$sheets = array_merge(
		(array) glob( get_theme_file_path( 'assets/css/*.css' ) ),
		(array) glob( get_theme_file_path( 'assets/css/blocks/*.css' ) )
	);

	$root = trailingslashit( get_stylesheet_directory() );

	foreach ( $sheets as $sheet ) {
		if ( is_string( $sheet ) ) {
			// add_editor_style() expects a path relative to the theme directory.
			add_editor_style( str_replace( $root, '', $sheet ) );
		}
	}
}
add_action( 'after_setup_theme', 'ma_toronto_editor_styles' );

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

/*
 * ---------------------------------------------------------------------------
 * Meetings (12 Step Meeting List plugin)
 * ---------------------------------------------------------------------------
 *
 * The plugin stores and manages meetings. The theme renders them, using the
 * plugin's supported extension point: in its default "legacy_ui" mode it hands
 * the /meetings/ archive to archive-meetings.php and each meeting to
 * single-meetings.php. Formatting helpers shared by both are in
 * inc/meetings.php. See docs/meetings-scope.md and docs/meeting-pages-scope.md.
 */

require_once get_theme_file_path( 'inc/meetings.php' );

/**
 * Registers the theme's own blocks from blocks/{name}/block.json.
 *
 * - ma-toronto/next-meeting: the homepage hero's "Next meeting" card. Dynamic,
 *   no settings; generated from the meeting list.
 */
function ma_toronto_register_blocks(): void {
	register_block_type( get_theme_file_path( 'blocks/next-meeting' ) );
}
add_action( 'init', 'ma_toronto_register_blocks' );

/**
 * Sends location URLs back to the meetings list.
 *
 * The plugin publishes a page for every location, but there is no design for
 * one: a meeting's own page already shows its location, map, and the other
 * meetings held there. Nothing on the site links to location pages, so
 * visitors who reach one by URL are taken to the list instead of an unstyled
 * plugin page. 302, not 301, in case a location page is designed later.
 */
function ma_toronto_redirect_location_pages(): void {
	if ( ! function_exists( 'tsml_get_meetings' ) ) {
		return;
	}

	if ( is_singular( 'tsml_location' ) || is_post_type_archive( 'tsml_location' ) ) {
		wp_safe_redirect( get_post_type_archive_link( 'tsml_meeting' ), 302 );
		exit;
	}
}
add_action( 'template_redirect', 'ma_toronto_redirect_location_pages' );

/**
 * Keeps the plugin's location pages out of the Yoast sitemap while they
 * redirect. Meeting pages are real pages and stay in.
 *
 * @param bool   $excluded  Whether the post type is excluded.
 * @param string $post_type Post type name.
 * @return bool
 */
function ma_toronto_exclude_location_pages_from_sitemap( bool $excluded, string $post_type ): bool {
	return 'tsml_location' === $post_type ? true : $excluded;
}
add_filter( 'wpseo_sitemap_exclude_post_type', 'ma_toronto_exclude_location_pages_from_sitemap', 10, 2 );

/**
 * Loads the meetings stylesheets: meetings.css on the list and on meeting
 * pages (they share badges and the help button), meeting.css on meeting pages.
 */
function ma_toronto_enqueue_meetings_styles(): void {
	$sheets = array();

	if ( is_post_type_archive( 'tsml_meeting' ) || is_singular( 'tsml_meeting' ) ) {
		$sheets['ma-toronto-meetings'] = 'assets/css/meetings.css';
	}
	if ( is_singular( 'tsml_meeting' ) ) {
		$sheets['ma-toronto-meeting'] = 'assets/css/meeting.css';
	}

	foreach ( $sheets as $handle => $path ) {
		if ( file_exists( get_theme_file_path( $path ) ) ) {
			wp_enqueue_style(
				$handle,
				get_theme_file_uri( $path ),
				array(),
				(string) filemtime( get_theme_file_path( $path ) )
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'ma_toronto_enqueue_meetings_styles' );

/**
 * Registers the meeting page's Share button module. Enqueued by
 * single-meetings.php. Progressive enhancement: the button ships `hidden` and
 * the module reveals it, so there is no dead button without JavaScript.
 */
function ma_toronto_register_meeting_share_module(): void {
	$path = 'assets/js/meeting-share.js';

	if ( file_exists( get_theme_file_path( $path ) ) ) {
		wp_register_script_module(
			'ma-toronto/meeting-share',
			get_theme_file_uri( $path ),
			array(),
			(string) filemtime( get_theme_file_path( $path ) )
		);
	}
}
add_action( 'init', 'ma_toronto_register_meeting_share_module' );

/**
 * "Add to calendar": serves a meeting as a weekly recurring .ics event at
 * /meetings/{slug}/?calendar=ics. Works in Apple Calendar, Google Calendar
 * (import) and Outlook. No personal data involved; the file carries the same
 * details as the public page.
 */
function ma_toronto_meeting_calendar_download(): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only download.
	if ( ! isset( $_GET['calendar'] ) || 'ics' !== $_GET['calendar'] || ! is_singular( 'tsml_meeting' ) || ! function_exists( 'tsml_get_meeting' ) ) {
		return;
	}

	$meeting = get_object_vars( tsml_get_meeting( get_queried_object_id() ) );
	$day     = $meeting['day'] ?? '';
	$start   = DateTime::createFromFormat( 'H:i', (string) ( $meeting['time'] ?? '' ) );

	if ( ! is_numeric( $day ) || ! $start ) {
		return; // Not a weekly meeting with a time; show the page instead.
	}

	$zone  = new DateTimeZone( 'America/Toronto' );
	$first = new DateTimeImmutable( 'today', $zone );
	$first = $first->modify( '+' . ( ( (int) $day - (int) $first->format( 'w' ) + 7 ) % 7 ) . ' days' )
		->setTime( (int) $start->format( 'G' ), (int) $start->format( 'i' ) );
	$end   = DateTime::createFromFormat( 'H:i', (string) ( $meeting['end_time'] ?? '' ) );
	$last  = $end ? $first->setTime( (int) $end->format( 'G' ), (int) $end->format( 'i' ) ) : $first->modify( '+1 hour' );

	$name     = ma_toronto_meeting_text( $meeting['post_title'] ?? '' );
	$url      = get_permalink();
	$online   = ma_toronto_meeting_is_online( $meeting );
	$location = $online
		? (string) $meeting['conference_url']
		: trim( ma_toronto_meeting_text( $meeting['location'] ?? '' ) . ', ' . ma_toronto_display_address( ma_toronto_meeting_text( $meeting['formatted_address'] ?? '' ), ma_toronto_meeting_text( $meeting['location'] ?? '' ) ), ', ' );
	$byday    = array( 'SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA' )[ (int) $day ];

	$escape = static fn( string $text ): string => str_replace( array( '\\', ';', ',', "\r\n", "\n" ), array( '\\\\', '\;', '\,', '\n', '\n' ), $text );
	$fold   = static function ( string $line ): string {
		// RFC 5545: lines over 75 octets continue on the next line after a space.
		$out = '';
		while ( strlen( $line ) > 75 ) {
			$cut = 75;
			while ( $cut > 0 && ( ord( $line[ $cut ] ) & 0xC0 ) === 0x80 ) {
				--$cut; // Don't split a UTF-8 character.
			}
			$out .= substr( $line, 0, $cut ) . "\r\n ";
			$line = substr( $line, $cut );
		}
		return $out . $line;
	};

	$description = $online
		/* translators: 1: join link, 2: meeting page URL. */
		? sprintf( __( 'Join: %1$s — Details: %2$s', 'ma-toronto' ), $meeting['conference_url'], $url )
		/* translators: %s: meeting page URL. */
		: sprintf( __( 'Details: %s', 'ma-toronto' ), $url );

	$lines = array(
		'BEGIN:VCALENDAR',
		'VERSION:2.0',
		'PRODID:-//MA Toronto//Meetings//EN',
		'CALSCALE:GREGORIAN',
		'METHOD:PUBLISH',
		'BEGIN:VTIMEZONE',
		'TZID:America/Toronto',
		'BEGIN:DAYLIGHT',
		'TZOFFSETFROM:-0500',
		'TZOFFSETTO:-0400',
		'TZNAME:EDT',
		'DTSTART:19700308T020000',
		'RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=2SU',
		'END:DAYLIGHT',
		'BEGIN:STANDARD',
		'TZOFFSETFROM:-0400',
		'TZOFFSETTO:-0500',
		'TZNAME:EST',
		'DTSTART:19701101T020000',
		'RRULE:FREQ=YEARLY;BYMONTH=11;BYDAY=1SU',
		'END:STANDARD',
		'END:VTIMEZONE',
		'BEGIN:VEVENT',
		'UID:meeting-' . get_queried_object_id() . '@' . wp_parse_url( home_url(), PHP_URL_HOST ),
		'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ),
		'DTSTART;TZID=America/Toronto:' . $first->format( 'Ymd\THis' ),
		'DTEND;TZID=America/Toronto:' . $last->format( 'Ymd\THis' ),
		'RRULE:FREQ=WEEKLY;BYDAY=' . $byday,
		'SUMMARY:' . $escape( $name . ' (MA meeting)' ),
		'LOCATION:' . $escape( $location ),
		'DESCRIPTION:' . $escape( $description ),
		'URL:' . $url,
		'END:VEVENT',
		'END:VCALENDAR',
	);

	nocache_headers();
	header( 'Content-Type: text/calendar; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( get_post_field( 'post_name', get_queried_object_id() ) ) . '.ics"' );
	echo implode( "\r\n", array_map( $fold, $lines ) ) . "\r\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- text/calendar, escaped per RFC 5545 above.
	exit;
}
add_action( 'template_redirect', 'ma_toronto_meeting_calendar_download' );
