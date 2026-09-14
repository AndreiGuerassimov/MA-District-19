<?php
/**
 * Next meeting — server render.
 *
 * Renders the card for "now" and embeds the week's schedule, which view.js
 * uses to recalculate in the visitor's browser. That keeps the card right when
 * the homepage is served from a page cache, or left open past a meeting.
 * Without JavaScript the server's version stands.
 *
 * Renders nothing when there are no meetings.
 *
 * @package MA_Toronto
 *
 * @var array    $attributes Block attributes (none).
 * @var string   $content    Inner content (none).
 * @var WP_Block $block      Block instance.
 */

defined( 'ABSPATH' ) || exit;

$ma_schedule = ma_toronto_next_meeting_schedule();
$ma_zone     = ma_toronto_meetings_timezone();
$ma_pick     = ma_toronto_pick_next_meeting( $ma_schedule, new DateTimeImmutable( 'now', $ma_zone ) );

if ( ! $ma_pick ) {
	return;
}

$ma_labels = array(
	'next'     => __( 'Next meeting:', 'ma-toronto' ),
	'live'     => __( 'Happening now:', 'ma-toronto' ),
	'tonight'  => __( 'Tonight', 'ma-toronto' ),
	'today'    => __( 'Today', 'ma-toronto' ),
	'tomorrow' => __( 'Tomorrow', 'ma-toronto' ),
	'days'     => array_map( 'ma_toronto_meeting_day', range( 0, 6 ) ),
);

$ma_config = array(
	'zone'     => $ma_zone->getName(),
	'liveFor'  => MA_TORONTO_HAPPENING_NOW_MINUTES,
	'evening'  => MA_TORONTO_EVENING_FROM,
	'labels'   => $ma_labels,
	'schedule' => $ma_schedule,
);

$ma_meeting = $ma_pick['meeting'];
$ma_lead    = $ma_pick['live']
	? $ma_labels['live'] . ' ' . $ma_meeting['name']
	: $ma_labels['next'] . ' ' . $ma_pick['when'] . ' — ' . $ma_meeting['name'];

?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'ma-next-meeting' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core-escaped. ?>
	data-ma-next-meeting="<?php echo esc_attr( wp_json_encode( $ma_config ) ); ?>">
	<a class="ma-next-meeting__card<?php echo $ma_pick['live'] ? ' is-live' : ''; ?>" href="<?php echo esc_url( $ma_meeting['url'] ); ?>">
		<span class="ma-next-meeting__dot" aria-hidden="true"></span>
		<span class="ma-next-meeting__text">
			<span class="ma-next-meeting__lead"><?php echo esc_html( $ma_lead ); ?></span>
			<span class="ma-next-meeting__place"><span aria-hidden="true">· </span><?php echo esc_html( $ma_meeting['place'] ); ?></span>
		</span>
	</a>
</div>
