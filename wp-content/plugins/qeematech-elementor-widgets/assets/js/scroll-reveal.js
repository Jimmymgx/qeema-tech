(function () {
	function qeemaInitScrollReveal() {
		var items = document.querySelectorAll( '.qeema-reveal:not([data-qeema-reveal-armed])' );
		if ( ! items.length ) {
			return;
		}
		if ( ! ( 'IntersectionObserver' in window ) || window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			return;
		}

		var observer = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-visible' );
					observer.unobserve( entry.target );
				}
			} );
		}, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' } );

		items.forEach( function ( el ) {
			el.dataset.qeemaRevealArmed = 'true';
			el.classList.add( 'qeema-reveal--armed' );
			observer.observe( el );

			// Safety net: a large negative jump (fast programmatic scroll,
			// a crawler/screenshot tool that never renders intermediate
			// scroll frames, etc.) can skip past an element without ever
			// intersecting it, leaving real content permanently invisible.
			// Force it visible after a few seconds no matter what.
			setTimeout( function () {
				el.classList.add( 'is-visible' );
				observer.unobserve( el );
			}, 4000 );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', qeemaInitScrollReveal );
	if ( window.elementorFrontend ) {
		jQuery( window ).on( 'elementor/frontend/init', qeemaInitScrollReveal );
	}
})();
