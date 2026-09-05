(function () {
	var AUTOPLAY_DELAY = 4500;

	function qeemaInitOneSlider( root ) {
		if ( root.dataset.qtInitialized === 'true' ) {
			return;
		}
		root.dataset.qtInitialized = 'true';

		var slides = root.querySelectorAll( '.qeema-works-hero__slide' );
		var dots   = root.querySelectorAll( '.qeema-works-hero__dot' );
		if ( slides.length < 2 ) {
			return;
		}

		var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		var index = 0;
		var timer = null;

		function show( i ) {
			slides.forEach( function ( slide, si ) {
				slide.classList.toggle( 'is-active', si === i );
			} );
			dots.forEach( function ( dot, di ) {
				dot.classList.toggle( 'is-active', di === i );
			} );
			index = i;
		}

		function next() {
			show( ( index + 1 ) % slides.length );
		}

		function startAutoplay() {
			stopAutoplay();
			if ( reduceMotion ) {
				return;
			}
			timer = window.setInterval( next, AUTOPLAY_DELAY );
		}

		function stopAutoplay() {
			if ( timer ) {
				window.clearInterval( timer );
				timer = null;
			}
		}

		dots.forEach( function ( dot, i ) {
			dot.addEventListener( 'click', function () {
				show( i );
				startAutoplay();
			} );
		} );

		root.addEventListener( 'mouseenter', stopAutoplay );
		root.addEventListener( 'mouseleave', startAutoplay );

		startAutoplay();
	}

	function qeemaInitAllSliders() {
		document.querySelectorAll( '.qeema-works-hero' ).forEach( qeemaInitOneSlider );
	}

	document.addEventListener( 'DOMContentLoaded', qeemaInitAllSliders );
	window.addEventListener( 'load', qeemaInitAllSliders );
	if ( window.elementorFrontend ) {
		jQuery( window ).on( 'elementor/frontend/init', qeemaInitAllSliders );
	}
})();
