/**
 * Quote slider — progressive enhancement for .ma-quote.
 *
 * The markup ships as a heading and a row of quotes. CSS makes that row a
 * scroll-snap track, which already gives native swipe and keyboard scrolling.
 * This module adds only what CSS cannot: previous/next buttons, dot
 * indicators, and the carousel semantics.
 *
 * Deliberate choices:
 * - Controls are BUILT HERE rather than shipped in the markup, because they do
 *   nothing without JavaScript. No dead buttons if this file fails to load.
 * - No autoplay. The prototype rotated every six seconds with no pause control,
 *   which fails WCAG 2.2.2. See docs/quote-slider-scope.md section 4.
 * - Off-screen slides are NOT aria-hidden. All slides stay readable, so a
 *   screen reader user can browse every slogan instead of operating a widget
 *   to reach them. For the same reason there is no aria-live region: nothing
 *   updates on its own, so there is nothing to announce.
 *
 * @package MA_Toronto
 */

const REDUCED_MOTION = window.matchMedia( '(prefers-reduced-motion: reduce)' );

/** Inline SVG chevron. `currentColor` lets the button own its colour. */
function chevron( direction ) {
	const d = direction < 0 ? 'M15 5l-7 7 7 7' : 'M9 5l7 7-7 7';
	return `<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"
		fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
		stroke-linejoin="round"><path d="${ d }"/></svg>`;
}

function build( region ) {
	const track = region.querySelector( '.ma-quote__track' );
	if ( ! track ) {
		return;
	}

	const slides = Array.from( track.querySelectorAll( ':scope > .wp-block-quote' ) );

	// One slogan is not a slider. Leave the markup alone.
	if ( slides.length < 2 ) {
		return;
	}

	region.classList.add( 'is-enhanced' );

	// --- Semantics -------------------------------------------------------
	const label = region.querySelector( '.ma-quote__label' );
	const viewport = document.createElement( 'div' );
	viewport.className = 'ma-quote__viewport';
	viewport.setAttribute( 'role', 'group' );
	viewport.setAttribute( 'aria-roledescription', 'carousel' );
	if ( label ) {
		if ( ! label.id ) {
			label.id = 'ma-quote-label';
		}
		viewport.setAttribute( 'aria-labelledby', label.id );
	}

	track.parentNode.insertBefore( viewport, track );
	viewport.appendChild( track );

	slides.forEach( ( slide, i ) => {
		slide.setAttribute( 'role', 'group' );
		slide.setAttribute( 'aria-roledescription', 'slide' );
		slide.setAttribute( 'aria-label', `${ i + 1 } of ${ slides.length }` );
	} );

	// --- Controls --------------------------------------------------------
	const nav = ( direction, text ) => {
		const b = document.createElement( 'button' );
		b.type = 'button';
		b.className = 'ma-quote__nav';
		b.setAttribute( 'aria-label', text );
		b.innerHTML = chevron( direction );
		b.addEventListener( 'click', () => go( current() + direction ) );
		return b;
	};

	const prev = nav( -1, 'Previous quote' );
	const next = nav( 1, 'Next quote' );
	viewport.insertBefore( prev, track );
	viewport.appendChild( next );

	const dots = document.createElement( 'div' );
	dots.className = 'ma-quote__dots';
	dots.setAttribute( 'role', 'group' );
	dots.setAttribute( 'aria-label', 'Choose a quote' );

	const buttons = slides.map( ( _, i ) => {
		const b = document.createElement( 'button' );
		b.type = 'button';
		b.className = 'ma-quote__dot';
		b.setAttribute( 'aria-label', `Go to quote ${ i + 1 }` );
		b.addEventListener( 'click', () => go( i ) );
		dots.appendChild( b );
		return b;
	} );

	viewport.after( dots );

	// --- Movement --------------------------------------------------------
	let active = 0;

	function current() {
		return active;
	}

	/** Wraps at both ends, so the buttons are never dead. */
	function go( index ) {
		const i = ( index + slides.length ) % slides.length;
		track.scrollTo( {
			left: slides[ i ].offsetLeft - slides[ 0 ].offsetLeft,
			behavior: REDUCED_MOTION.matches ? 'auto' : 'smooth',
		} );
	}

	function setActive( i ) {
		active = i;
		buttons.forEach( ( b, n ) => {
			b.classList.toggle( 'is-active', n === i );
			if ( n === i ) {
				b.setAttribute( 'aria-current', 'true' );
			} else {
				b.removeAttribute( 'aria-current' );
			}
		} );
	}

	setActive( 0 );

	// Track position by observation rather than by counting clicks, so swiping
	// and native scrolling keep the dots honest.
	const observer = new IntersectionObserver(
		( entries ) => {
			entries
				.filter( ( e ) => e.isIntersecting )
				.forEach( ( e ) => setActive( slides.indexOf( e.target ) ) );
		},
		{ root: track, threshold: 0.6 }
	);
	slides.forEach( ( s ) => observer.observe( s ) );

	// Left/right arrows move between slogans while focus is inside the region.
	viewport.addEventListener( 'keydown', ( event ) => {
		if ( event.key !== 'ArrowLeft' && event.key !== 'ArrowRight' ) {
			return;
		}
		event.preventDefault();
		go( current() + ( event.key === 'ArrowLeft' ? -1 : 1 ) );
	} );
}

document.querySelectorAll( '.ma-quote' ).forEach( build );
