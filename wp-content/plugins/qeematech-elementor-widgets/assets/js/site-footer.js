(function () {
	function qeemaInitFooterGlow( root ) {
		var cards = root.querySelectorAll( '.qeema-footer__brand, .qeema-footer__column' );
		if ( ! cards.length ) {
			return;
		}
		cards.forEach( function ( card ) {
			card.addEventListener( 'mousemove', function ( e ) {
				var rect = card.getBoundingClientRect();
				var x = ( ( e.clientX - rect.left ) / rect.width ) * 100;
				var y = ( ( e.clientY - rect.top ) / rect.height ) * 100;
				card.style.setProperty( '--mx', x + '%' );
				card.style.setProperty( '--my', y + '%' );
			} );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '.qeema-footer' ).forEach( qeemaInitFooterGlow );
	} );
})();
