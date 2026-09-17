(function () {
	var AUTOPLAY_DELAY = 4000;
	var RESUME_DELAY = 2600;

	// How far (px) a drag has to travel before it counts as one "step" to
	// the next/previous phone - crossing it again mid-drag steps again, so a
	// single long drag can flip through several phones in one gesture.
	var DRAG_STEP_PX = 90;
	// Below this, a press+release is treated as a plain click (e.g. tapping
	// a side phone to bring it forward) rather than a drag.
	var DRAG_CLICK_THRESHOLD_PX = 6;

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
		var isDragging = false;
		var dragMoved = false;
		var dragPointerId = null;
		var dragStartX = 0;
		var dragBaselineX = 0;

		// On a narrow phone screen, showing up to 3 side phones on each side
		// (7 phones at once) turns into a wall of overlapping slivers with
		// unreadable edge text - only the immediate neighbor peeking in on
		// each side reads clearly at that size.
		function maxVisibleOffset() {
			return window.innerWidth <= 600 ? 1 : 3;
		}

		// The outer stage is scaled way down on phones to fit the fanned
		// layout at all (see style.css), which shrinks the focused phone too
		// and makes its screen hard to read. Growing just the active phone's
		// own scale (independent of the outer stage scale) compensates for
		// that without affecting the side phones' size or spacing.
		function activeScale() {
			return window.innerWidth <= 600 ? 1.35 : 1;
		}

		function render() {
			var maxOffset = maxVisibleOffset();
			var focusScale = activeScale();
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
					transform = 'translateX(0) translateZ(0) rotateY(0) scale(' + focusScale + ')';
					opacity = 1;
					z = 10;
				} else if ( abs <= maxOffset ) {
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
				// A drag gesture ends with a native "click" on whatever's under
				// the pointer - without this guard, releasing a drag over a
				// phone's link would immediately navigate away.
				if ( dragMoved ) {
					e.preventDefault();
					dragMoved = false;
					return;
				}
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

		// Click-and-drag / swipe support: Pointer Events cover mouse, touch,
		// and pen with one code path. Dragging follows the pointer like
		// pulling a physical stack of cards - moving left brings the next
		// phone into focus, moving right brings the previous one back.
		function onDragStart( e ) {
			if ( ! e.isPrimary || null !== dragPointerId ) {
				return;
			}
			dragPointerId = e.pointerId;
			dragStartX = e.clientX;
			dragBaselineX = e.clientX;
			dragMoved = false;
			isDragging = true;
			stopAutoplay();
			root.classList.add( 'is-dragging' );
			try {
				stage.setPointerCapture( e.pointerId );
			} catch ( err ) {
				// Ignore - pointer capture is a progressive enhancement here.
			}
		}

		function onDragMove( e ) {
			if ( ! isDragging || e.pointerId !== dragPointerId ) {
				return;
			}
			if ( Math.abs( e.clientX - dragStartX ) > DRAG_CLICK_THRESHOLD_PX ) {
				dragMoved = true;
			}
			var delta = e.clientX - dragBaselineX;
			if ( Math.abs( delta ) >= DRAG_STEP_PX ) {
				goTo( active + ( delta < 0 ? 1 : -1 ) );
				dragBaselineX = e.clientX;
			}
		}

		function onDragEnd( e ) {
			if ( ! isDragging || e.pointerId !== dragPointerId ) {
				return;
			}
			isDragging = false;
			dragPointerId = null;
			root.classList.remove( 'is-dragging' );
			try {
				stage.releasePointerCapture( e.pointerId );
			} catch ( err ) {
				// Already released or never captured - nothing to clean up.
			}
			restartAutoplaySoon();
		}

		stage.style.touchAction = 'pan-y';
		stage.addEventListener( 'pointerdown', onDragStart );
		stage.addEventListener( 'pointermove', onDragMove );
		stage.addEventListener( 'pointerup', onDragEnd );
		stage.addEventListener( 'pointercancel', onDragEnd );

		// Re-render on resize/rotate so maxVisibleOffset/activeScale's
		// phone/tablet/desktop switch takes effect immediately instead of
		// only on the next goTo().
		var lastMaxOffset = maxVisibleOffset();
		var lastActiveScale = activeScale();
		window.addEventListener( 'resize', function () {
			var currentOffset = maxVisibleOffset();
			var currentScale = activeScale();
			if ( currentOffset !== lastMaxOffset || currentScale !== lastActiveScale ) {
				lastMaxOffset = currentOffset;
				lastActiveScale = currentScale;
				render();
			}
		} );

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
