/**
 * Next meeting — keeps the card current in the browser.
 *
 * Mirrors ma_toronto_pick_next_meeting() in inc/meetings.php; keep the two in
 * step. Runs on load (a cached page may be hours old) and once a minute. Uses
 * the meetings' time zone, not the visitor's, because meeting times are
 * listed in Toronto time.
 *
 * @package MA_Toronto
 */

const WEEK = 7 * 24 * 60;

/** Day of week (0 = Sunday) and minutes after midnight, in `zone`. */
function nowIn( zone ) {
	const parts = Object.fromEntries(
		new Intl.DateTimeFormat( 'en-US', { timeZone: zone, weekday: 'short', hour: 'numeric', minute: 'numeric', hourCycle: 'h23' } )
			.formatToParts( new Date() )
			.map( ( p ) => [ p.type, p.value ] )
	);
	const day = [ 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ].indexOf( parts.weekday );
	return { day, minutes: Number( parts.hour ) * 60 + Number( parts.minute ) };
}

/** 1170 -> "7:30 PM". */
function clock( minutes ) {
	const h = Math.floor( minutes / 60 );
	const m = String( minutes % 60 ).padStart( 2, '0' );
	return `${ ( h % 12 ) || 12 }:${ m } ${ h < 12 ? 'AM' : 'PM' }`;
}

function pick( config ) {
	const { day, minutes } = nowIn( config.zone );
	const nowWeek = day * 1440 + minutes;
	let best = null;

	for ( const meeting of config.schedule ) {
		const start = meeting.d * 1440 + meeting.s;
		const since = ( nowWeek - start + WEEK ) % WEEK;
		const live = since < config.liveFor;
		const rank = live ? since - WEEK : ( start - nowWeek + WEEK ) % WEEK;
		if ( ! best || rank < best.rank ) {
			best = { rank, meeting, live };
		}
	}
	if ( ! best ) {
		return null;
	}

	const { labels } = config;
	const { meeting, live, rank } = best;
	if ( live ) {
		return { meeting, live, lead: `${ labels.live } ${ meeting.name }` };
	}

	const daysAhead = Math.floor( ( minutes + rank ) / 1440 );
	let when;
	if ( daysAhead === 0 ) {
		when = meeting.s >= config.evening ? labels.tonight : labels.today;
	} else if ( daysAhead === 1 ) {
		when = labels.tomorrow;
	} else {
		when = labels.days[ meeting.d ];
	}
	return { meeting, live, lead: `${ labels.next } ${ when } ${ clock( meeting.s ) } — ${ meeting.name }` };
}

for ( const root of document.querySelectorAll( '[data-ma-next-meeting]' ) ) {
	let config;
	try {
		config = JSON.parse( root.dataset.maNextMeeting );
	} catch {
		continue; // Keep the server-rendered card.
	}

	const card = root.querySelector( '.ma-next-meeting__card' );
	const lead = root.querySelector( '.ma-next-meeting__lead' );
	const place = root.querySelector( '.ma-next-meeting__place' );

	const update = () => {
		const result = pick( config );
		if ( ! result || ! card || ! lead || ! place ) {
			return;
		}
		card.href = result.meeting.url;
		card.classList.toggle( 'is-live', result.live );
		lead.textContent = result.lead;
		place.lastChild.textContent = result.meeting.place;
	};

	update();
	setInterval( update, 60 * 1000 );
}
