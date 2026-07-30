(function () {
	function qeemaInitCarousel( root ) {
		var track = root.querySelector( '.qeema-testimonials__track' );
		var prev = root.querySelector( '.qeema-testimonials__prev' );
		var next = root.querySelector( '.qeema-testimonials__next' );
		if ( ! track ) {
			return;
		}
		var scrollAmount = function () {
			var card = track.querySelector( '.qeema-testimonial-card' );
			return card ? card.offsetWidth + 18 : 260;
		};
		if ( prev ) {
			prev.addEventListener( 'click', function () {
				track.scrollBy( { left: scrollAmount(), behavior: 'smooth' } );
			} );
		}
		if ( next ) {
			next.addEventListener( 'click', function () {
				track.scrollBy( { left: -scrollAmount(), behavior: 'smooth' } );
			} );
		}
	}

	function qeemaInitAllCarousels() {
		document.querySelectorAll( '.qeema-testimonials' ).forEach( function ( root ) {
			if ( root.dataset.qeemaInit === 'true' ) {
				return;
			}
			root.dataset.qeemaInit = 'true';
			qeemaInitCarousel( root );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', qeemaInitAllCarousels );
	if ( window.elementorFrontend ) {
		jQuery( window ).on( 'elementor/frontend/init', qeemaInitAllCarousels );
	}
})();
