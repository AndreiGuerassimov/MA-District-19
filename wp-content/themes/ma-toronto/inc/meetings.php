<?php
/**
 * Meetings — formatting helpers shared by archive-meetings.php (the list),
 * single-meetings.php (a meeting page) and the calendar download.
 *
 * Data comes from the 12 Step Meeting List plugin. Two quirks of that data are
 * handled here so the templates don't have to:
 *
 * - tsml_get_meeting() returns most strings already HTML-entity-encoded
 *   ("East End United &mdash; Jackman Room"). ma_toronto_meeting_text() decodes
 *   them, so templates can escape once, on output, like everywhere else.
 * - Days are numbered 0 (Sunday) to 6 (Saturday); times are "HH:MM", 24-hour.
 *
 * @package MA_Toronto
 */

declare( strict_types = 1 );

defined( 'ABSPATH' ) || exit;

/**
 * Plain text from a plugin field: entities decoded, whitespace trimmed.
 *
 * @param mixed $value Field value.
 */
function ma_toronto_meeting_text( $value ): string {
	return is_scalar( $value ) ? trim( html_entity_decode( (string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) : '';
}

/**
 * Day name, singular ("Monday") or plural ("Mondays").
 *
 * @param mixed $day    Plugin day number, 0 = Sunday.
 * @param bool  $plural Whether to pluralise.
 */
function ma_toronto_meeting_day( $day, bool $plural = false ): string {
	if ( ! is_numeric( $day ) ) {
		return '';
	}
	$singular = array(
		__( 'Sunday', 'ma-toronto' ),
		__( 'Monday', 'ma-toronto' ),
		__( 'Tuesday', 'ma-toronto' ),
		__( 'Wednesday', 'ma-toronto' ),
		__( 'Thursday', 'ma-toronto' ),
		__( 'Friday', 'ma-toronto' ),
		__( 'Saturday', 'ma-toronto' ),
	);
	$plurals  = array(
		__( 'Sundays', 'ma-toronto' ),
		__( 'Mondays', 'ma-toronto' ),
		__( 'Tuesdays', 'ma-toronto' ),
		__( 'Wednesdays', 'ma-toronto' ),
		__( 'Thursdays', 'ma-toronto' ),
		__( 'Fridays', 'ma-toronto' ),
		__( 'Saturdays', 'ma-toronto' ),
	);
	return ( $plural ? $plurals : $singular )[ (int) $day ] ?? '';
}

/**
 * "19:30" -> "7:30 PM".
 *
 * @param string $time 24-hour time.
 */
function ma_toronto_meeting_time( string $time ): string {
	$dt = DateTime::createFromFormat( 'H:i', $time );
	return $dt ? $dt->format( 'g:i A' ) : $time;
}

/**
 * "7:30 – 8:30 PM", or "11:30 AM – 1:00 PM" when the meeting crosses noon.
 * Just the start time when there is no end time.
 *
 * @param string $start 24-hour start.
 * @param string $end   24-hour end.
 */
function ma_toronto_meeting_time_range( string $start, string $end ): string {
	$from = DateTime::createFromFormat( 'H:i', $start );
	$to   = DateTime::createFromFormat( 'H:i', $end );

	if ( ! $from ) {
		return $start;
	}
	if ( ! $to ) {
		return $from->format( 'g:i A' );
	}
	if ( $from->format( 'A' ) === $to->format( 'A' ) ) {
		return $from->format( 'g:i' ) . ' – ' . $to->format( 'g:i A' );
	}
	return $from->format( 'g:i A' ) . ' – ' . $to->format( 'g:i A' );
}

/**
 * Length in words: "1 hour", "75 minutes", "1.5 hours". Empty if unknown.
 *
 * @param string $start 24-hour start.
 * @param string $end   24-hour end.
 */
function ma_toronto_meeting_length( string $start, string $end ): string {
	$from = DateTime::createFromFormat( 'H:i', $start );
	$to   = DateTime::createFromFormat( 'H:i', $end );

	if ( ! $from || ! $to || $to <= $from ) {
		return '';
	}
	$minutes = (int) ( ( $to->getTimestamp() - $from->getTimestamp() ) / 60 );

	if ( 0 === $minutes % 30 && $minutes >= 60 ) {
		$hours = $minutes / 60;
		/* translators: %s: number of hours, e.g. 1 or 1.5. */
		return sprintf( _n( '%s hour', '%s hours', (int) ceil( $hours ), 'ma-toronto' ), (string) $hours );
	}
	/* translators: %d: number of minutes. */
	return sprintf( _n( '%d minute', '%d minutes', $minutes, 'ma-toronto' ), $minutes );
}

/**
 * Name of the conferencing service from its URL, e.g. "Zoom".
 *
 * @param string $url Conference URL.
 */
function ma_toronto_conference_provider( string $url ): string {
	if ( function_exists( 'tsml_conference_provider' ) ) {
		$provider = tsml_conference_provider( $url );
		if ( is_string( $provider ) && '' !== $provider ) {
			return ucfirst( $provider );
		}
	}
	return __( 'Online', 'ma-toronto' );
}

/**
 * Formatted Zoom meeting ID from a join link: ".../j/4161235813" -> "416 123 5813".
 * Empty when the link carries no ID.
 *
 * @param string $url Conference URL.
 */
function ma_toronto_zoom_id( string $url ): string {
	if ( ! preg_match( '#/j/(\d{9,11})#', $url, $match ) ) {
		return '';
	}
	$digits = $match[1];
	$split  = 11 === strlen( $digits ) ? array( 3, 4, 4 ) : array( 3, 3, 4 );
	$parts  = array();
	$offset = 0;
	foreach ( $split as $length ) {
		$parts[] = substr( $digits, $offset, $length );
		$offset += $length;
	}
	return trim( implode( ' ', array_filter( $parts ) ) );
}

/**
 * Address without a leading place name the location already states. The
 * geocoder sometimes prefixes the building: location "CAMH Bell Gateway
 * Building", address "Bell Gateway Building, 100 Stokes St., …".
 *
 * @param string $address  Formatted address.
 * @param string $location Location name.
 */
function ma_toronto_address_without_place( string $address, string $location ): string {
	$segments = array_map( 'trim', explode( ',', trim( $address ) ) );
	if ( count( $segments ) > 2 && '' !== $location && ! preg_match( '/\d/', $segments[0] ) && false !== stripos( $location, $segments[0] ) ) {
		array_shift( $segments );
	}
	return implode( ', ', $segments );
}

/**
 * Address without the trailing province/postcode and country, keeping the
 * city — meetings span the GTA. "310 Danforth Ave, Toronto, ON M4K 2X4, Canada"
 * -> "310 Danforth Ave, Toronto".
 *
 * @param string $address  Formatted address.
 * @param string $location Location name, to drop a repeated place name.
 */
function ma_toronto_short_address( string $address, string $location = '' ): string {
	$segments = array_map( 'trim', explode( ',', ma_toronto_address_without_place( $address, $location ) ) );
	if ( count( $segments ) >= 3 ) {
		$segments = array_slice( $segments, 0, -2 );
	}
	return implode( ', ', array_filter( $segments ) );
}

/**
 * Address without the country: "100 Stokes St., Toronto, ON M6J 1H4".
 *
 * @param string $address  Formatted address.
 * @param string $location Location name, to drop a repeated place name.
 */
function ma_toronto_display_address( string $address, string $location = '' ): string {
	return (string) preg_replace( '/,\s*Canada$/i', '', ma_toronto_address_without_place( $address, $location ) );
}

/**
 * Whether a meeting can be attended in person / online. Hybrid is both.
 *
 * @param array<string, mixed> $meeting Meeting from tsml_get_meetings(), or a cast tsml_get_meeting().
 */
function ma_toronto_meeting_is_online( array $meeting ): bool {
	return in_array( $meeting['attendance_option'] ?? '', array( 'online', 'hybrid' ), true ) && ! empty( $meeting['conference_url'] );
}

/**
 * @param array<string, mixed> $meeting Meeting data.
 */
function ma_toronto_meeting_is_in_person( array $meeting ): bool {
	return in_array( $meeting['attendance_option'] ?? '', array( 'in_person', 'hybrid' ), true ) && ! empty( $meeting['formatted_address'] );
}

/**
 * Attendance badge: [modifier, label].
 *
 * @param string $attendance Plugin attendance option.
 * @return array{0: string, 1: string}
 */
function ma_toronto_attendance_badge( string $attendance ): array {
	return array(
		'online'    => array( 'online', __( 'Online', 'ma-toronto' ) ),
		'hybrid'    => array( 'hybrid', __( 'Hybrid', 'ma-toronto' ) ),
		'in_person' => array( 'in-person', __( 'In person', 'ma-toronto' ) ),
	)[ $attendance ] ?? array( 'in-person', __( 'In person', 'ma-toronto' ) );
}

/**
 * Plugin type codes, sorted into what the meeting page does with them.
 *
 * MA's type list in the plugin has no "Closed" code — only "O" (open to
 * non-addicts) — so the absence of O is not shown as "closed". Closed/open
 * wording that varies by week lives in the meeting's notes.
 *
 * @return array{format: string[], audience: string[], access: string[]}
 */
function ma_toronto_meeting_type_groups(): array {
	return array(
		'format'   => array( 'D', 'SP', 'BB', 'MED', 'H' ),
		'audience' => array( 'W', 'M', 'NB', 'T', 'LGBTQI+', 'Y', 'POC', 'BE' ),
		'access'   => array( 'X', 'CCAP', 'OUT' ),
	);
}

/**
 * Human labels for the given type codes, in the plugin's wording.
 *
 * @param string[] $types Codes present on the meeting.
 * @param string[] $only  Codes to keep.
 * @return string[]
 */
function ma_toronto_meeting_type_labels( array $types, array $only ): array {
	global $tsml_programs, $tsml_program;

	$names  = $tsml_programs[ $tsml_program ]['types'] ?? array();
	$labels = array();
	foreach ( $only as $code ) {
		if ( in_array( $code, $types, true ) && ! empty( $names[ $code ] ) ) {
			$labels[] = (string) $names[ $code ];
		}
	}
	return $labels;
}

/**
 * "tel:" href from a display number: "+1 (647) 909-9687" -> "tel:+16479099687".
 *
 * @param string $phone Phone number as entered.
 */
function ma_toronto_tel_href( string $phone ): string {
	return 'tel:' . preg_replace( '/[^\d+,#*]/', '', $phone );
}

/**
 * Zoom wordmark (Simple Icons, CC0). Decorative: callers supply the accessible
 * name as text. Zoom's trademark; used only to mean "join on Zoom".
 */
function ma_toronto_zoom_logo(): string {
	return '<svg class="ma-zoom-logo" viewBox="0 9.2 24 5.6" aria-hidden="true" focusable="false"><path fill="currentColor" d="M5.033 14.649H.743a.74.74 0 0 1-.686-.458.74.74 0 0 1 .16-.808L3.19 10.41H1.06A1.06 1.06 0 0 1 0 9.35h3.957c.301 0 .57.18.686.458a.74.74 0 0 1-.161.808L1.51 13.59h2.464c.585 0 1.06.475 1.06 1.06zM24 11.338c0-1.14-.927-2.066-2.066-2.066-.61 0-1.158.265-1.537.686a2.061 2.061 0 0 0-1.536-.686c-1.14 0-2.066.926-2.066 2.066v3.311a1.06 1.06 0 0 0 1.06-1.06v-2.251a1.004 1.004 0 0 1 2.013 0v2.251c0 .586.474 1.06 1.06 1.06v-3.311a1.004 1.004 0 0 1 2.012 0v2.251c0 .586.475 1.06 1.06 1.06zM16.265 12a2.728 2.728 0 1 1-5.457 0 2.728 2.728 0 0 1 5.457 0zm-1.06 0a1.669 1.669 0 1 0-3.338 0 1.669 1.669 0 0 0 3.338 0zm-4.82 0a2.728 2.728 0 1 1-5.458 0 2.728 2.728 0 0 1 5.457 0zm-1.06 0a1.669 1.669 0 1 0-3.338 0 1.669 1.669 0 0 0 3.338 0z"/></svg>';
}

/**
 * "Join on Zoom" link content: visible "Join on" + wordmark, with "Zoom" as
 * hidden text so the accessible name reads in full. Other services get plain text.
 *
 * @param string $url          Conference URL.
 * @param string $meeting_name Meeting name, added as hidden text so repeated
 *                             links on the list are distinguishable. Empty on a
 *                             meeting's own page.
 */
function ma_toronto_join_label( string $url, string $meeting_name = '' ): string {
	$provider = ma_toronto_conference_provider( $url );
	$hidden   = '' !== $meeting_name ? '<span class="screen-reader-text"> ' . esc_html( $meeting_name ) . '</span>' : '';

	if ( 'Zoom' === $provider ) {
		return '<span>' . esc_html__( 'Join', 'ma-toronto' ) . $hidden . ' ' . esc_html__( 'on', 'ma-toronto' ) . '</span> '
			. ma_toronto_zoom_logo()
			. '<span class="screen-reader-text">' . esc_html__( 'Zoom', 'ma-toronto' ) . '</span>';
	}
	/* translators: 1: hidden meeting name, 2: service, e.g. Google Meet. */
	return sprintf( esc_html__( 'Join%1$s on %2$s', 'ma-toronto' ), $hidden, esc_html( $provider ) );
}

/**
 * OpenStreetMap URLs for a point: embeddable map, full map, and directions.
 * No API key or account; the embed is subject to OSM's tile usage policy.
 *
 * @param float $lat Latitude.
 * @param float $lng Longitude.
 * @return array{embed: string, view: string, directions: string}
 */
function ma_toronto_osm_urls( float $lat, float $lng ): array {
	$bbox = implode( ',', array( $lng - 0.01, $lat - 0.005, $lng + 0.01, $lat + 0.005 ) );
	return array(
		'embed'      => 'https://www.openstreetmap.org/export/embed.html?' . http_build_query(
			array(
				'bbox'   => $bbox,
				'layer'  => 'mapnik',
				'marker' => $lat . ',' . $lng,
			)
		),
		'view'       => 'https://www.openstreetmap.org/?' . http_build_query(
			array(
				'mlat' => $lat,
				'mlon' => $lng,
			)
		) . '#map=17/' . $lat . '/' . $lng,
		'directions' => 'https://www.openstreetmap.org/directions?route=' . rawurlencode( ';' . $lat . ',' . $lng ),
	);
}
