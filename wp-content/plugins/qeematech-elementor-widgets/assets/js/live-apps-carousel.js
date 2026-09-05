(function () {
	var AUTOPLAY_DELAY = 4000;
	var RESUME_DELAY = 2600;

	function qeemaInitLiveAppsCarousel( root ) {
		var stage = root.querySelector( '.qeema-live-apps-carousel__stage' );
		if ( ! stage ) {
			return;
		}
		var phones = Array.prototype.slice.call( stage.querySelectorAll( '.qeema-live-apps-carousel__phone' ) );
		var dots   = Array.prototype.slice.call( root.querySelectorAll( '.qeema-live-apps-carousel__dot' ) );
		var prevBtn = root.querySelector( '.qeema-live-apps-carousel__prev' );
		var nextBtn = root.querySelector( '.qeema-live-apps-carousel__next' );
		var n = phones.length;
		if ( ! n ) {
			return;
		}

		var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		var active = 0;
		var autoplayTimer = null;
		var resumeTimer = null;

		function render() {
			phones.forEach( function ( el, i ) {
				var offset = i - active;
				if ( offset > n / 2 ) {
					offset -= n;
				}
				if ( offset < -n / 2 ) {
					offset += n;
				}
				var abs = Math.abs( offset );
				var transform, opacity, z;
				if ( 0 === abs ) {
					transform = 'translateX(0) translateZ(0) rotateY(0) scale(1)';
					opacity = 1;
					z = 10;
				} else if ( abs <= 3 ) {
					var dir = offset > 0 ? 1 : -1;
					transform = 'translateX(' + ( dir * abs * 168 ) + 'px) translateZ(' + ( -abs * 110 ) + 'px) rotateY(' + ( -dir * 30 ) + 'deg) scale(' + ( 1 - abs * 0.13 ) + ')';
					opacity = 1 - abs * 0.24;
					z = 10 - abs;
				} else {
					transform = 'translateX(0) scale(.4)';
					opacity = 0;
					z = 0;
				}
				el.style.transform = transform;
				el.style.opacity = opacity;
				el.style.zIndex = z;
				el.classList.toggle( 'is-active', 0 === abs );
			} );
			dots.forEach( function ( d, i ) {
				d.classList.toggle( 'active', i === active );
			} );
		}

		function goTo( index ) {
			active = ( index % n + n ) % n;
			render();
		}

		function stopAutoplay() {
			if ( autoplayTimer ) {
				window.clearInterval( autoplayTimer );
				autoplayTimer = null;
			}
		}

		function startAutoplay() {
			stopAutoplay();
			if ( reduceMotion ) {
				return;
			}
			autoplayTimer = window.setInterval( function () {
				goTo( active + 1 );
			}, AUTOPLAY_DELAY );
		}

		function restartAutoplaySoon() {
			stopAutoplay();
			window.clearTimeout( resumeTimer );
			resumeTimer = window.setTimeout( startAutoplay, RESUME_DELAY );
		}

		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', function () {
				goTo( active + 1 );
				restartAutoplaySoon();
			} );
		}
		if ( prevBtn ) {
			prevBtn.addEventListener( 'click', function () {
				goTo( active - 1 );
				restartAutoplaySoon();
			} );
		}
		dots.forEach( function ( d, i ) {
			d.addEventListener( 'click', function () {
				goTo( i );
				restartAutoplaySoon();
			} );
		} );

		// A side (non-active) phone just steps forward to the front on click;
		// only the already-front phone's own link actually navigates away —
		// otherwise a single tap on a half-hidden side card would unexpectedly
		// leave the page instead of bringing it into view first.
		phones.forEach( function ( el, i ) {
			el.addEventListener( 'click', function ( e ) {
				if ( i !== active ) {
					e.preventDefault();
					goTo( i );
				}
				restartAutoplaySoon();
			} );
		} );

		root.addEventListener( 'mouseenter', stopAutoplay );
		root.addEventListener( 'mouseleave', startAutoplay );
		root.addEventListener( 'touchstart', stopAutoplay, { passive: true } );
		root.addEventListener( 'touchend', restartAutoplaySoon, { passive: true } );

		render();
		startAutoplay();
	}

	function qeemaInitAllLiveAppsCarousels() {
		document.querySelectorAll( '.qeema-live-apps-carousel' ).forEach( function ( root ) {
			if ( 'true' === root.dataset.qeemaInit ) {
				return;
			}
			root.dataset.qeemaInit = 'true';
			qeemaInitLiveAppsCarousel( root );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', qeemaInitAllLiveAppsCarousels );
	if ( window.elementorFrontend ) {
		jQuery( window ).on( 'elementor/frontend/init', qeemaInitAllLiveAppsCarousels );
	}
})();
