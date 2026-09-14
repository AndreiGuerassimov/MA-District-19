/**
 * Next meeting — editor.
 *
 * No controls: the card is generated from the meeting list. The editor shows
 * the same server-rendered card visitors see, so the hero previews accurately.
 * Plain script, no build step.
 */
( function ( blocks, element, blockEditor, ServerSideRender, i18n ) {
	const el = element.createElement;

	blocks.registerBlockType( 'ma-toronto/next-meeting', {
		edit: function () {
			return el(
				'div',
				blockEditor.useBlockProps(),
				el( ServerSideRender, {
					block: 'ma-toronto/next-meeting',
					EmptyResponsePlaceholder: function () {
						return el( 'p', { className: 'ma-next-meeting__empty' }, i18n.__( 'Next meeting: none listed yet. Add meetings in Meetings.', 'ma-toronto' ) );
					},
				} )
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.serverSideRender, window.wp.i18n );
