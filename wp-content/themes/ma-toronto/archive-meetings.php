<?php
/**
 * Meetings list — /meetings/
 *
 * Rendered from the 12 Step Meeting List plugin's data. The plugin hands its
 * meetings archive to this file (its supported extension point in the default
 * "legacy_ui" mode), so the plugin's own front-end template, scripts and styles
 * are never loaded.
 *
 * This is deliberately a PHP template rather than a block template: meetings are
 * structured records with a fixed layout, managed in the plugin's admin, not
 * content an editor rearranges. See docs/meetings-scope.md.
 *
 * Everything is rendered on the server:
 * - filters are links (?type=online, ?type=in-person), so they work without
 *   JavaScript and filtered views can be shared;
 * - the count reflects the active filter;
 * - days with no meetings are omitted.
 *
 * Meeting cards deliberately do not link to individual meeting pages yet — those
 * pages are designed in a later round, and currently redirect back here
 * (see functions.php). The "Join" and "Get directions" actions do work.
 *
 * @package MA_Toronto
 */

defined( 'ABSPATH' ) || exit;

/* --------------------------------------------------------------------------
 * Data
 * ------------------------------------------------------------------------ */

$ma_filters = array(
	''          => __( 'All', 'ma-toronto' ),
	'in-person' => __( 'In person', 'ma-toronto' ),
	'online'    => __( 'Online', 'ma-toronto' ),
);

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only view filter.
$ma_filter = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : '';
if ( ! array_key_exists( $ma_filter, $ma_filters ) ) {
	$ma_filter = '';
}

$ma_all = function_exists( 'tsml_get_meetings' ) ? (array) tsml_get_meetings() : array();

/**
 * Whether a meeting belongs in the active filter. Hybrid meetings are both.
 */
$ma_matches = static function ( array $meeting ) use ( $ma_filter ): bool {
	$attendance = $meeting['attendance_option'] ?? '';

	if ( 'inactive' === $attendance ) {
		return false;
	}
	if ( 'online' === $ma_filter ) {
		return in_array( $attendance, array( 'online', 'hybrid' ), true );
	}
	if ( 'in-person' === $ma_filter ) {
		return in_array( $attendance, array( 'in_person', 'hybrid' ), true );
	}
	return true;
};

$ma_visible = array_values( array_filter( $ma_all, $ma_matches ) );

// Group Monday -> Sunday. The plugin numbers days 0 (Sunday) to 6 (Saturday).
$ma_day_names = array(
	1 => __( 'Monday', 'ma-toronto' ),
	2 => __( 'Tuesday', 'ma-toronto' ),
	3 => __( 'Wednesday', 'ma-toronto' ),
	4 => __( 'Thursday', 'ma-toronto' ),
	5 => __( 'Friday', 'ma-toronto' ),
	6 => __( 'Saturday', 'ma-toronto' ),
	0 => __( 'Sunday', 'ma-toronto' ),
);

$ma_days = array();
foreach ( $ma_day_names as $ma_index => $ma_name ) {
	$ma_on_day = array_filter(
		$ma_visible,
		static fn( array $m ): bool => isset( $m['day'] ) && '' !== $m['day'] && (int) $m['day'] === $ma_index
	);
	if ( $ma_on_day ) {
		usort( $ma_on_day, static fn( array $a, array $b ): int => strcmp( $a['time'] ?? '', $b['time'] ?? '' ) );
		$ma_days[ $ma_name ] = $ma_on_day;
	}
}

/* --------------------------------------------------------------------------
 * Formatting helpers
 * ------------------------------------------------------------------------ */

/** "19:30" -> "7:30 PM". */
$ma_time = static function ( string $time ): string {
	$dt = DateTime::createFromFormat( 'H:i', $time );
	return $dt ? $dt->format( 'g:i A' ) : $time;
};

/** Name of the conferencing service from its URL, e.g. "Zoom". */
$ma_provider = static function ( string $url ): string {
	if ( function_exists( 'tsml_conference_provider' ) ) {
		$provider = tsml_conference_provider( $url );
		if ( is_string( $provider ) && '' !== $provider ) {
			return $provider;
		}
	}
	return __( 'online', 'ma-toronto' );
};

/**
 * The "place" line under a meeting's name.
 * In person: "Location · 162 Bloor St W, Toronto" (province, postcode and
 * country dropped; city kept, because meetings span the GTA).
 * Online: "Zoom · ID 842 1179 4420" when a Zoom meeting ID is in the link.
 */
$ma_place = static function ( array $m ) use ( $ma_provider ): string {
	$attendance = $m['attendance_option'] ?? '';

	if ( 'online' === $attendance && ! empty( $m['conference_url'] ) ) {
		$provider = $ma_provider( $m['conference_url'] );
		if ( preg_match( '#/j/(\d{9,11})#', $m['conference_url'], $id ) ) {
			$digits = $id[1];
			$split  = 11 === strlen( $digits ) ? array( 3, 4, 4 ) : array( 3, 3, 4 );
			$parts  = array();
			$offset = 0;
			foreach ( $split as $len ) {
				$parts[] = substr( $digits, $offset, $len );
				$offset += $len;
			}
			/* translators: 1: service, e.g. Zoom. 2: formatted meeting ID. */
			return sprintf( __( '%1$s · ID %2$s', 'ma-toronto' ), $provider, trim( implode( ' ', array_filter( $parts ) ) ) );
		}
		return ucfirst( $provider );
	}

	$segments = array_map( 'trim', explode( ',', (string) ( $m['formatted_address'] ?? '' ) ) );
	if ( count( $segments ) >= 3 ) {
		$segments = array_slice( $segments, 0, -2 );
	}
	$address  = implode( ', ', array_filter( $segments ) );
	$location = trim( (string) ( $m['location'] ?? '' ) );

	if ( '' === $location || 0 === stripos( $address, $location ) ) {
		return $address;
	}
	return $address ? $location . ' · ' . $address : $location;
};

$ma_archive = get_post_type_archive_link( 'tsml_meeting' );
$ma_contact = get_page_by_path( 'contact' );

/* --------------------------------------------------------------------------
 * Render template parts and blocks before wp_head(), as core's
 * template-canvas.php does, so the styles they generate print in <head>.
 * ------------------------------------------------------------------------ */

$ma_header = do_blocks( '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->' );
$ma_footer = do_blocks( '<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->' );

$ma_hero = do_blocks(
	'<!-- wp:group {"align":"full","className":"ma-page__hero is-style-hero-wash","style":{"spacing":{"padding":{"top":"var:preset|spacing|90","bottom":"var:preset|spacing|70","left":"var:preset|spacing|80","right":"var:preset|spacing|80"},"blockGap":"var:preset|spacing|30"}},"layout":{"type":"default"}} -->' .
	'<div class="wp-block-group alignfull ma-page__hero is-style-hero-wash" style="padding-top:var(--wp--preset--spacing--90);padding-right:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--70);padding-left:var(--wp--preset--spacing--80)">' .
	'<!-- wp:heading {"level":1,"fontSize":"display"} --><h1 class="wp-block-heading has-display-font-size">' . esc_html__( 'Find a meeting', 'ma-toronto' ) . '</h1><!-- /wp:heading -->' .
	'<!-- wp:paragraph {"className":"ma-page__intro","fontSize":"large"} --><p class="ma-page__intro has-large-font-size">' .
	esc_html__( 'Every meeting is free and open — newcomers are always welcome, and you never have to speak. In-person meetings run across the GTA; online meetings run on Zoom. Times shown are Eastern (Toronto).', 'ma-toronto' ) .
	'</p><!-- /wp:paragraph -->' .
	'</div><!-- /wp:group -->'
);

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#wp--skip-link--target"><?php esc_html_e( 'Skip to content', 'ma-toronto' ); ?></a>
<div class="wp-site-blocks">

<?php echo $ma_header; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered block markup. ?>

<main id="wp--skip-link--target" class="wp-block-group ma-page ma-meetings">

	<?php echo $ma_hero; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered block markup. ?>

	<div class="ma-meetings__controls">
		<nav class="ma-meetings__filters" aria-label="<?php esc_attr_e( 'Filter meetings', 'ma-toronto' ); ?>">
			<ul>
				<?php foreach ( $ma_filters as $ma_key => $ma_label ) : ?>
					<li>
						<a class="ma-chip"
							href="<?php echo esc_url( '' === $ma_key ? $ma_archive : add_query_arg( 'type', $ma_key, $ma_archive ) ); ?>"
							<?php echo $ma_key === $ma_filter ? 'aria-current="true"' : ''; ?>><?php echo esc_html( $ma_label ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<p class="ma-meetings__count">
			<?php
			$ma_n = count( $ma_visible );
			if ( 'online' === $ma_filter ) {
				/* translators: %d: number of meetings. */
				$ma_count_text = sprintf( _n( '%d online meeting this week', '%d online meetings this week', $ma_n, 'ma-toronto' ), $ma_n );
			} elseif ( 'in-person' === $ma_filter ) {
				/* translators: %d: number of meetings. */
				$ma_count_text = sprintf( _n( '%d in-person meeting this week', '%d in-person meetings this week', $ma_n, 'ma-toronto' ), $ma_n );
			} else {
				/* translators: %d: number of meetings. */
				$ma_count_text = sprintf( _n( '%d meeting this week', '%d meetings this week', $ma_n, 'ma-toronto' ), $ma_n );
			}
			echo esc_html( $ma_count_text );
			?>
		</p>
	</div>

	<div class="ma-meetings__schedule">
		<?php if ( ! $ma_days ) : ?>
			<div class="ma-meetings__empty">
				<p><?php esc_html_e( 'There are no meetings matching this filter right now.', 'ma-toronto' ); ?></p>
				<?php if ( '' !== $ma_filter ) : ?>
					<p><a href="<?php echo esc_url( $ma_archive ); ?>"><?php esc_html_e( 'See all meetings', 'ma-toronto' ); ?></a></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php foreach ( $ma_days as $ma_day => $ma_meetings ) : ?>
			<?php $ma_heading_id = 'ma-day-' . sanitize_title( $ma_day ); ?>
			<section class="ma-day" aria-labelledby="<?php echo esc_attr( $ma_heading_id ); ?>">
				<h2 class="ma-day__name" id="<?php echo esc_attr( $ma_heading_id ); ?>"><?php echo esc_html( $ma_day ); ?></h2>

				<ul class="ma-day__list">
					<?php
					foreach ( $ma_meetings as $ma_m ) :
						$ma_attendance = $ma_m['attendance_option'] ?? 'in_person';
						$ma_is_online  = in_array( $ma_attendance, array( 'online', 'hybrid' ), true ) && ! empty( $ma_m['conference_url'] );
						$ma_is_inperson = in_array( $ma_attendance, array( 'in_person', 'hybrid' ), true ) && ! empty( $ma_m['formatted_address'] );
						$ma_badge = array(
							'online'    => array( 'online', __( 'Online', 'ma-toronto' ) ),
							'hybrid'    => array( 'hybrid', __( 'Hybrid', 'ma-toronto' ) ),
							'in_person' => array( 'in-person', __( 'In person', 'ma-toronto' ) ),
						)[ $ma_attendance ] ?? array( 'in-person', __( 'In person', 'ma-toronto' ) );
						?>
						<li class="ma-meeting">
							<time class="ma-meeting__time" datetime="<?php echo esc_attr( $ma_m['time'] ?? '' ); ?>"><?php echo esc_html( $ma_time( (string) ( $ma_m['time'] ?? '' ) ) ); ?></time>

							<div class="ma-meeting__body">
								<h3 class="ma-meeting__name"><?php echo esc_html( $ma_m['name'] ?? '' ); ?></h3>
								<p class="ma-meeting__place"><?php echo esc_html( $ma_place( $ma_m ) ); ?></p>
							</div>

							<div class="ma-meeting__actions">
								<span class="ma-badge ma-badge--<?php echo esc_attr( $ma_badge[0] ); ?>"><?php echo esc_html( $ma_badge[1] ); ?></span>

								<?php if ( $ma_is_online ) : ?>
									<a class="ma-meeting__cta" href="<?php echo esc_url( $ma_m['conference_url'] ); ?>">
										<?php
										printf(
											/* translators: 1: hidden meeting name, 2: service, e.g. Zoom. */
											esc_html__( 'Join%1$s on %2$s', 'ma-toronto' ),
											'<span class="screen-reader-text"> ' . esc_html( $ma_m['name'] ?? '' ) . '</span>',
											esc_html( ucfirst( $ma_provider( $ma_m['conference_url'] ) ) )
										);
										?>
										<span aria-hidden="true">&rarr;</span>
									</a>
								<?php endif; ?>

								<?php if ( $ma_is_inperson ) : ?>
									<a class="ma-meeting__cta" href="<?php echo esc_url( 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $ma_m['formatted_address'] ) ); ?>">
										<?php
										printf(
											/* translators: %s: hidden meeting name. */
											esc_html__( 'Get directions%s', 'ma-toronto' ),
											'<span class="screen-reader-text"> ' . esc_html__( 'to', 'ma-toronto' ) . ' ' . esc_html( $ma_m['name'] ?? '' ) . '</span>'
										);
										?>
										<span aria-hidden="true">&rarr;</span>
									</a>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endforeach; ?>
	</div>

	<aside class="ma-meetings__help" aria-labelledby="ma-meetings-help-title">
		<div class="ma-meetings__help-text">
			<h2 id="ma-meetings-help-title"><?php esc_html_e( 'New and not sure where to start?', 'ma-toronto' ); ?></h2>
			<p><?php esc_html_e( 'Come a few minutes early and let someone know it is your first meeting. You can just listen. There is nothing to sign and no cost, ever.', 'ma-toronto' ); ?></p>
		</div>
		<?php if ( $ma_contact ) : ?>
			<a class="ma-meetings__help-button" href="<?php echo esc_url( get_permalink( $ma_contact ) ); ?>"><?php esc_html_e( 'Contact us', 'ma-toronto' ); ?></a>
		<?php endif; ?>
	</aside>

</main>

<?php echo $ma_footer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered block markup. ?>

</div>
<?php wp_footer(); ?>
</body>
</html>
