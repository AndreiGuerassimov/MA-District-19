/**
 * Meeting page Share button — progressive enhancement.
 *
 * The button ships `hidden`, so without JavaScript there is no dead control.
 * Uses the native share sheet where there is one (phones, Safari), and falls
 * back to copying the page link, announced through a polite live region.
 *
 * @package MA_Toronto
 */

const button = document.querySelector( '[data-ma-share]' );
const status = document.querySelector( '[data-ma-share-status]' );

if ( button && ( navigator.share || navigator.clipboard ) ) {
	const label = button.textContent;
	const copied = button.dataset.maShareCopied || 'Link copied';
	let timer;

	button.hidden = false;

	button.addEventListener( 'click', async () => {
		const data = { title: document.title, url: location.href.split( '#' )[ 0 ] };

		if ( navigator.share ) {
			try {
				await navigator.share( data );
			} catch {
				// Dismissed by the user; nothing to do.
			}
			return;
		}

		try {
			await navigator.clipboard.writeText( data.url );
			button.textContent = copied;
			if ( status ) {
				status.textContent = copied;
			}
			clearTimeout( timer );
			timer = setTimeout( () => {
				button.textContent = label;
				if ( status ) {
					status.textContent = '';
				}
			}, 2500 );
		} catch {
			// Clipboard blocked; leave the button as it was.
		}
	} );
}
