(function () {
	function qeemaInitFooterGlow( root ) {
		var cards = root.querySelectorAll( '.qeema-footer__brand, .qeema-footer__column' );
		if ( ! cards.length ) {
			return;
		}
		cards.forEach( function ( card ) {
			var cachedRect = null;
			card.addEventListener( 'mouseenter', function () {
				cachedRect = card.getBoundingClientRect();
			} );
			card.addEventListener( 'mousemove', function ( e ) {
				if ( ! cachedRect ) {
					cachedRect = card.getBoundingClientRect();
				}
				var x = ( ( e.clientX - cachedRect.left ) / cachedRect.width ) * 100;
				var y = ( ( e.clientY - cachedRect.top ) / cachedRect.height ) * 100;
				card.style.setProperty( '--mx', x + '%' );
				card.style.setProperty( '--my', y + '%' );
			} );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '.qeema-footer' ).forEach( qeemaInitFooterGlow );
	} );
})();
