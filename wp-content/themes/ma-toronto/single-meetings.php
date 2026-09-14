<?php
/**
 * A meeting's page — /meetings/{slug}/
 *
 * Built from design/MeetingDetail.dc.html. The 12 Step Meeting List plugin
 * hands single meetings to this file (its supported extension point, as with
 * archive-meetings.php), so none of the plugin's own front-end assets load.
 *
 * The prototype draws one in-person meeting. Online meetings swap in their
 * equivalents, decided in docs/meeting-pages-scope.md:
 *   Get directions   -> Join on Zoom
 *   Location card    -> How to join (meeting ID, passcode, dial-in)
 *   Map, "Other meetings at this location" -> omitted
 *
 * Also from the scope doc: no "Typical size" tile (no data exists for it; the
 * meeting's length stands in), and group phone/email are shown publicly, as on
 * the previous site.
 *
 * @package MA_Toronto
 */

defined( 'ABSPATH' ) || exit;

/* --------------------------------------------------------------------------
 * Data
 * ------------------------------------------------------------------------ */

$ma_raw = function_exists( 'tsml_get_meeting' ) ? tsml_get_meeting( get_the_ID() ) : null;
if ( ! $ma_raw ) {
	wp_safe_redirect( get_post_type_archive_link( 'tsml_meeting' ), 302 );
	exit;
}
$ma_m = get_object_vars( $ma_raw );
$ma_t = static fn( string $key ): string => ma_toronto_meeting_text( $ma_m[ $key ] ?? '' );

$ma_name       = $ma_t( 'post_title' );
$ma_types      = array_map( 'strval', (array) ( $ma_m['types'] ?? array() ) );
$ma_groups     = ma_toronto_meeting_type_groups();
$ma_is_online  = ma_toronto_meeting_is_online( $ma_m );
$ma_in_person  = ma_toronto_meeting_is_in_person( $ma_m );
$ma_is_open    = in_array( 'O', $ma_types, true );
$ma_start      = $ma_t( 'time' );
$ma_end        = $ma_t( 'end_time' );
$ma_schedule   = trim( ma_toronto_meeting_day( $ma_m['day'] ?? '', true ) . ' · ' . ma_toronto_meeting_time_range( $ma_start, $ma_end ) . ' ' . __( 'ET', 'ma-toronto' ), ' ·' );
$ma_formats    = ma_toronto_meeting_type_labels( $ma_types, $ma_groups['format'] );
$ma_audience   = ma_toronto_meeting_type_labels( $ma_types, $ma_groups['audience'] );
$ma_access     = ma_toronto_meeting_type_labels( $ma_types, $ma_groups['access'] );
$ma_length     = ma_toronto_meeting_length( $ma_start, $ma_end );
$ma_attendance = ma_toronto_attendance_badge( (string) ( $ma_m['attendance_option'] ?? '' ) );
$ma_conference = (string) ( $ma_m['conference_url'] ?? '' );
$ma_location   = $ma_t( 'location' );
$ma_address    = ma_toronto_display_address( $ma_t( 'formatted_address' ), $ma_location );
$ma_lat        = is_numeric( $ma_m['latitude'] ?? null ) ? (float) $ma_m['latitude'] : null;
$ma_lng        = is_numeric( $ma_m['longitude'] ?? null ) ? (float) $ma_m['longitude'] : null;
$ma_osm        = ( $ma_in_person && null !== $ma_lat && null !== $ma_lng ) ? ma_toronto_osm_urls( $ma_lat, $ma_lng ) : null;
$ma_phone      = $ma_t( 'phone' );
$ma_email      = sanitize_email( $ma_t( 'email' ) );
$ma_contact    = get_page_by_path( 'contact' );
$ma_contact_url = $ma_contact ? get_permalink( $ma_contact ) : '';

/** Non-empty trimmed lines of a multi-line field. */
$ma_lines = static fn( string $text ): array => array_values( array_filter( array_map( 'trim', preg_split( '/\R/', $text ) ) ) );

// Location notes: one bullet per line. "Room: …" lines sit under the address
// (as the design's "Room 1150, ground floor"); everything else is a bullet.
$ma_room    = '';
$ma_bullets = array();
foreach ( $ma_lines( $ma_t( 'location_notes' ) ) as $ma_line ) {
	if ( '' === $ma_room && preg_match( '/^Room:\s*(.+)$/i', $ma_line, $ma_match ) ) {
		$ma_room = $ma_match[1];
	} else {
		$ma_bullets[] = $ma_line;
	}
}

// Online: "Meeting ID: … · Passcode: …" becomes separate rows.
$ma_join_details = array_values( array_filter( array_map( 'trim', explode( '·', $ma_t( 'conference_url_notes' ) ) ) ) );
$ma_dial_in      = $ma_t( 'conference_phone_notes' );
$ma_one_tap      = $ma_t( 'conference_phone' );

// Other meetings at the same in-person location. All online meetings share a
// placeholder "Online" location, so this is skipped for them.
$ma_also_here = array();
if ( $ma_in_person && ! $ma_is_online ) {
	foreach ( (array) ( $ma_m['location_meetings'] ?? array() ) as $ma_other ) {
		if ( (int) ( $ma_other['id'] ?? 0 ) !== get_the_ID() ) {
			$ma_also_here[] = $ma_other;
		}
	}
	usort(
		$ma_also_here,
		static fn( array $a, array $b ): int => array( ( (int) $a['day'] + 6 ) % 7, $a['time'] ) <=> array( ( (int) $b['day'] + 6 ) % 7, $b['time'] )
	);
}

// "Meeting information" rows. Rows without data are left out.
$ma_info = array(
	array( __( 'Day & time', 'ma-toronto' ), esc_html( $ma_schedule ) ),
	array(
		__( 'Type', 'ma-toronto' ),
		esc_html( implode( ' · ', array_filter( array( $ma_attendance[1], $ma_is_open ? __( 'Open meeting', 'ma-toronto' ) : '' ) ) ) ),
	),
);
if ( $ma_formats ) {
	$ma_info[] = array( __( 'Format', 'ma-toronto' ), esc_html( implode( ', ', $ma_formats ) ) );
}
if ( $ma_access ) {
	$ma_info[] = array( __( 'Access', 'ma-toronto' ), esc_html( implode( ', ', $ma_access ) ) );
}
$ma_info[] = array(
	__( 'Who can come', 'ma-toronto' ),
	esc_html(
		$ma_is_open
			? __( 'Anyone with a desire to stop using marijuana, plus family, friends and anyone curious.', 'ma-toronto' )
			: __( 'Anyone with a desire to stop using marijuana.', 'ma-toronto' )
	) . ( $ma_audience
		/* translators: %s: comma-separated list, e.g. "Women, Non-Binary". */
		? ' ' . esc_html( sprintf( __( 'This group is for: %s.', 'ma-toronto' ), implode( ', ', $ma_audience ) ) )
		: '' ),
);
$ma_info[] = array( __( 'Cost', 'ma-toronto' ), esc_html__( 'Free. Contributing to the 7th Tradition is optional.', 'ma-toronto' ) );

if ( $ma_phone || $ma_email ) {
	$ma_contact_html = array();
	if ( $ma_phone ) {
		$ma_contact_html[] = '<a href="' . esc_attr( ma_toronto_tel_href( $ma_phone ) ) . '">' . esc_html( $ma_phone ) . '</a>';
	}
	if ( $ma_email ) {
		$ma_contact_html[] = '<a href="' . esc_attr( 'mailto:' . $ma_email ) . '">' . esc_html( $ma_email ) . '</a>';
	}
	$ma_group_notes = $ma_t( 'group_notes' );
	$ma_info[]      = array(
		__( 'Contact', 'ma-toronto' ),
		implode( '<br>', $ma_contact_html ) . ( $ma_group_notes ? '<span class="ma-info__note">' . esc_html( $ma_group_notes ) . '</span>' : '' ),
	);
}

$ma_badges = array_merge(
	$ma_is_open ? array( __( 'Open meeting', 'ma-toronto' ) ) : array(),
	$ma_audience,
	$ma_access
);

$ma_tiles = array_filter(
	array(
		__( 'Format', 'ma-toronto' )   => implode( ', ', $ma_formats ),
		__( 'Length', 'ma-toronto' )   => $ma_length,
		__( 'Language', 'ma-toronto' ) => __( 'English', 'ma-toronto' ),
	)
);

$ma_archive  = get_post_type_archive_link( 'tsml_meeting' );
$ma_ics      = add_query_arg( 'calendar', 'ics', get_permalink() );
$ma_updated  = get_the_modified_date( 'j F Y' );
$ma_paragraphs = $ma_lines( $ma_t( 'notes' ) );

wp_enqueue_script_module( 'ma-toronto/meeting-share' );

/* --------------------------------------------------------------------------
 * Render template parts and blocks before wp_head(), as core's
 * template-canvas.php does, so the styles they generate print in <head>.
 * ------------------------------------------------------------------------ */

$ma_header = do_blocks( '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->' );
$ma_footer = do_blocks( '<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->' );

ob_start();
?>
<p class="ma-meeting-page__back"><a href="<?php echo esc_url( $ma_archive ); ?>"><span aria-hidden="true">&larr;</span> <?php esc_html_e( 'Back to all meetings', 'ma-toronto' ); ?></a></p>
<div class="ma-meeting-page__title-row">
	<div>
		<h1 class="wp-block-heading ma-meeting-page__title"><?php echo esc_html( $ma_name ); ?></h1>
		<ul class="ma-meeting-page__badges">
			<li class="ma-badge ma-badge--<?php echo esc_attr( $ma_attendance[0] ); ?>"><?php echo esc_html( $ma_attendance[1] ); ?></li>
			<?php foreach ( $ma_badges as $ma_badge ) : ?>
				<li class="ma-badge ma-badge--online"><?php echo esc_html( $ma_badge ); ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<p class="ma-meeting-page__schedule"><?php echo esc_html( $ma_schedule ); ?></p>
</div>
<?php
$ma_hero_inner = (string) ob_get_clean();

$ma_hero = do_blocks(
	'<!-- wp:group {"align":"full","className":"ma-meeting-page__hero is-style-hero-wash","layout":{"type":"default"}} -->' .
	'<div class="wp-block-group alignfull ma-meeting-page__hero is-style-hero-wash">' . $ma_hero_inner . '</div>' .
	'<!-- /wp:group -->'
);

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php
	// Core prints <title> only when it renders a block template itself
	// (_block_template_render_title_tag); this PHP template must do the same.
	?>
	<title><?php echo wp_get_document_title(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#wp--skip-link--target"><?php esc_html_e( 'Skip to content', 'ma-toronto' ); ?></a>
<div class="wp-site-blocks">

<?php echo $ma_header; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered block markup. ?>

<main id="wp--skip-link--target" class="wp-block-group ma-page ma-meeting-page">

	<?php echo $ma_hero; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered block markup. ?>

	<div class="ma-meeting-page__grid">

		<div class="ma-meeting-page__rail">

			<?php if ( $ma_is_online ) : ?>
				<a class="ma-meeting-page__primary-action" href="<?php echo esc_url( $ma_conference ); ?>">
					<?php echo ma_toronto_join_label( $ma_conference ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				</a>
			<?php elseif ( $ma_osm ) : ?>
				<a class="ma-meeting-page__primary-action" href="<?php echo esc_url( $ma_osm['directions'] ); ?>">
					<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
					<?php esc_html_e( 'Get directions', 'ma-toronto' ); ?>
				</a>
			<?php endif; ?>

			<section class="ma-panel" aria-labelledby="ma-info-title">
				<h2 class="ma-panel__label" id="ma-info-title"><?php esc_html_e( 'Meeting information', 'ma-toronto' ); ?></h2>
				<dl class="ma-info">
					<?php foreach ( $ma_info as [ $ma_label, $ma_value_html ] ) : ?>
						<div class="ma-info__row">
							<dt><?php echo esc_html( $ma_label ); ?></dt>
							<dd><?php echo $ma_value_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped when built above. ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>
				<div class="ma-meeting-page__tools">
					<a class="ma-tool" href="<?php echo esc_url( $ma_ics ); ?>" download>
						<?php
						printf(
							/* translators: %s: hidden file type note. */
							esc_html__( 'Add to calendar%s', 'ma-toronto' ),
							'<span class="screen-reader-text"> (' . esc_html__( '.ics file', 'ma-toronto' ) . ')</span>'
						);
						?>
					</a>
					<button class="ma-tool" type="button" data-ma-share data-ma-share-copied="<?php esc_attr_e( 'Link copied', 'ma-toronto' ); ?>" hidden><?php esc_html_e( 'Share', 'ma-toronto' ); ?></button>
				</div>
				<p class="screen-reader-text" data-ma-share-status aria-live="polite"></p>
			</section>

			<?php if ( $ma_is_online ) : ?>
				<section class="ma-panel" aria-labelledby="ma-join-title">
					<h2 class="ma-panel__label" id="ma-join-title"><?php esc_html_e( 'How to join', 'ma-toronto' ); ?></h2>
					<p class="ma-panel__lead"><?php echo esc_html( ma_toronto_conference_provider( $ma_conference ) ); ?></p>
					<?php if ( $ma_join_details ) : ?>
						<ul class="ma-bullets ma-bullets--plain">
							<?php foreach ( $ma_join_details as $ma_detail ) : ?>
								<li><?php echo esc_html( $ma_detail ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php if ( $ma_dial_in || $ma_one_tap ) : ?>
						<ul class="ma-bullets">
							<?php if ( $ma_one_tap ) : ?>
								<li><span class="ma-bullets__mark" aria-hidden="true">&#9656;</span><span><a href="<?php echo esc_attr( ma_toronto_tel_href( $ma_one_tap ) ); ?>"><?php esc_html_e( 'Join by phone (one tap)', 'ma-toronto' ); ?></a></span></li>
							<?php endif; ?>
							<?php if ( $ma_dial_in ) : ?>
								<li><span class="ma-bullets__mark" aria-hidden="true">&#9656;</span><span><?php echo esc_html( $ma_dial_in ); ?></span></li>
							<?php endif; ?>
						</ul>
					<?php endif; ?>
				</section>
			<?php elseif ( $ma_in_person ) : ?>
				<section class="ma-panel" aria-labelledby="ma-location-title">
					<h2 class="ma-panel__label" id="ma-location-title"><?php esc_html_e( 'Location', 'ma-toronto' ); ?></h2>
					<p class="ma-panel__lead"><?php echo esc_html( $ma_location ); ?></p>
					<p class="ma-panel__address">
						<?php echo esc_html( $ma_address ); ?>
						<?php if ( $ma_room ) : ?>
							<br><?php echo esc_html( $ma_room ); ?>
						<?php endif; ?>
					</p>
					<?php if ( $ma_bullets ) : ?>
						<ul class="ma-bullets">
							<?php foreach ( $ma_bullets as $ma_bullet ) : ?>
								<li><span class="ma-bullets__mark" aria-hidden="true">&#9656;</span><span><?php echo esc_html( $ma_bullet ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</section>
			<?php endif; ?>

			<aside class="ma-first-time" aria-labelledby="ma-first-time-title">
				<h2 id="ma-first-time-title"><?php esc_html_e( 'First time here?', 'ma-toronto' ); ?></h2>
				<p>
					<?php
					echo esc_html(
						$ma_is_online
							? __( 'Join a few minutes early and let the group know it is your first meeting. You can keep your camera off and just listen. There is nothing to sign and no cost, ever.', 'ma-toronto' )
							: __( 'Come a few minutes early and tell someone it is your first meeting. You can just listen. There is nothing to sign and no cost, ever.', 'ma-toronto' )
					);
					?>
				</p>
				<?php if ( $ma_contact_url ) : ?>
					<a class="ma-meetings__help-button" href="<?php echo esc_url( $ma_contact_url ); ?>"><?php esc_html_e( 'Ask us a question', 'ma-toronto' ); ?></a>
				<?php endif; ?>
			</aside>
		</div>

		<div class="ma-meeting-page__main">

			<?php if ( $ma_osm ) : ?>
				<figure class="ma-panel ma-map">
					<iframe
						class="ma-map__frame"
						title="<?php echo esc_attr( sprintf( /* translators: %s: place and address. */ __( 'Map showing %s', 'ma-toronto' ), trim( $ma_location . ', ' . $ma_address, ', ' ) ) ); ?>"
						src="<?php echo esc_url( $ma_osm['embed'] ); ?>"
						loading="lazy"
						referrerpolicy="no-referrer-when-downgrade"></iframe>
					<figcaption class="ma-map__caption">
						<span><?php echo esc_html( $ma_address ); ?></span>
						<a href="<?php echo esc_url( $ma_osm['view'] ); ?>"><?php esc_html_e( 'Open in OpenStreetMap', 'ma-toronto' ); ?> <span aria-hidden="true">&rarr;</span></a>
					</figcaption>
				</figure>
			<?php endif; ?>

			<section class="ma-panel ma-panel--roomy" aria-labelledby="ma-about-title">
				<h2 class="ma-panel__title" id="ma-about-title"><?php esc_html_e( 'About this meeting', 'ma-toronto' ); ?></h2>
				<?php foreach ( $ma_paragraphs as $ma_paragraph ) : ?>
					<p class="ma-panel__prose"><?php echo esc_html( $ma_paragraph ); ?></p>
				<?php endforeach; ?>
				<?php if ( $ma_tiles ) : ?>
					<dl class="ma-tiles">
						<?php foreach ( $ma_tiles as $ma_label => $ma_value ) : ?>
							<div class="ma-tile">
								<dt><?php echo esc_html( $ma_label ); ?></dt>
								<dd><?php echo esc_html( $ma_value ); ?></dd>
							</div>
						<?php endforeach; ?>
					</dl>
				<?php endif; ?>
			</section>

			<?php if ( $ma_also_here ) : ?>
				<section class="ma-panel ma-panel--roomy" aria-labelledby="ma-also-title">
					<h2 class="ma-panel__title ma-panel__title--sm" id="ma-also-title"><?php esc_html_e( 'Other meetings at this location', 'ma-toronto' ); ?></h2>
					<p class="ma-panel__subtitle"><?php echo esc_html( $ma_location ); ?></p>
					<ul class="ma-also">
						<?php foreach ( $ma_also_here as $ma_other ) : ?>
							<li>
								<a class="ma-also__link" href="<?php echo esc_url( get_permalink( (int) $ma_other['id'] ) ); ?>">
									<span class="ma-also__when"><?php echo esc_html( ma_toronto_meeting_day( $ma_other['day'] ?? '', true ) . ' · ' . ma_toronto_meeting_time( (string) ( $ma_other['time'] ?? '' ) ) ); ?></span>
									<span class="ma-also__name"><?php echo esc_html( ma_toronto_meeting_text( $ma_other['name'] ?? '' ) ); ?></span>
									<span class="ma-also__view" aria-hidden="true"><?php esc_html_e( 'View', 'ma-toronto' ); ?> &rarr;</span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<aside class="ma-notice" aria-labelledby="ma-notice-title">
				<h2 class="ma-notice__label" id="ma-notice-title"><?php esc_html_e( 'Meeting details change', 'ma-toronto' ); ?></h2>
				<p>
					<?php
					if ( $ma_contact_url ) {
						printf(
							/* translators: 1: "let us know" link, 2: date. */
							esc_html__( 'Times and rooms occasionally shift. If something here is out of date, please %1$s so we can fix it. Last updated %2$s.', 'ma-toronto' ),
							'<a href="' . esc_url( $ma_contact_url ) . '">' . esc_html__( 'let us know', 'ma-toronto' ) . '</a>',
							esc_html( $ma_updated )
						);
					} else {
						/* translators: %s: date. */
						echo esc_html( sprintf( __( 'Times and rooms occasionally shift. Last updated %s.', 'ma-toronto' ), $ma_updated ) );
					}
					?>
				</p>
			</aside>
		</div>
	</div>

</main>

<?php echo $ma_footer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered block markup. ?>

</div>
<?php wp_footer(); ?>
</body>
</html>
