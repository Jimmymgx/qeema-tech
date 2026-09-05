(function () {
	var AUTOPLAY_DELAY = 3500;
	var RESUME_DELAY = 2200;
	var SCROLL_DURATION = 650;

	function easeInOutCubic( t ) {
		return t < 0.5 ? 4 * t * t * t : 1 - Math.pow( -2 * t + 2, 3 ) / 2;
	}

	// Custom rAF-driven scroll with a fixed duration/easing curve, used instead
	// of the native `behavior:'smooth'` so autoplay glides at a consistent,
	// deliberate pace instead of the browser's own (fast, near-linear) smooth
	// scroll timing.
	function smoothScrollTo( el, targetLeft, duration ) {
		return new Promise( function ( resolve ) {
			var startLeft = el.scrollLeft;
			var change = targetLeft - startLeft;
			if ( ! change ) {
				resolve();
				return;
			}
			var startTime = null;
			function step( timestamp ) {
				if ( startTime === null ) {
					startTime = timestamp;
				}
				var progress = Math.min( ( timestamp - startTime ) / duration, 1 );
				el.scrollLeft = startLeft + change * easeInOutCubic( progress );
				if ( progress < 1 ) {
					window.requestAnimationFrame( step );
				} else {
					resolve();
				}
			}
			window.requestAnimationFrame( step );
		} );
	}

	function qeemaBuildMedia( type, src ) {
		if ( 'video' === type ) {
			var video = document.createElement( 'video' );
			video.src = src;
			video.controls = true;
			video.autoplay = true;
			video.playsInline = true;
			return video;
		}
		var iframe = document.createElement( 'iframe' );
		iframe.src = src;
		iframe.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
		iframe.frameBorder = '0';
		return iframe;
	}

	function qeemaInitVideoModal( root ) {
		var modal = root.querySelector( '.qeema-video-modal' );
		if ( ! modal ) {
			return;
		}
		var mediaHolder = modal.querySelector( '.qeema-video-modal__media' );
		var closeBtn = modal.querySelector( '.qeema-video-modal__close' );
		var backdrop = modal.querySelector( '.qeema-video-modal__backdrop' );

		function closeModal() {
			modal.classList.remove( 'is-open' );
			modal.setAttribute( 'aria-hidden', 'true' );
			mediaHolder.innerHTML = '';
			document.body.classList.remove( 'qeema-modal-lock' );
		}

		function openModal( type, src ) {
			mediaHolder.innerHTML = '';
			mediaHolder.appendChild( qeemaBuildMedia( type, src ) );
			modal.classList.add( 'is-open' );
			modal.setAttribute( 'aria-hidden', 'false' );
			document.body.classList.add( 'qeema-modal-lock' );
			closeBtn.focus();
		}

		root.querySelectorAll( '.qeema-testimonial-card__play' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var type = btn.dataset.videoType;
				var src = btn.dataset.videoSrc;
				if ( src ) {
					openModal( type, src );
				}
			} );
		} );

		closeBtn.addEventListener( 'click', closeModal );
		backdrop.addEventListener( 'click', closeModal );
		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && modal.classList.contains( 'is-open' ) ) {
				closeModal();
			}
		} );
	}

	function qeemaInitCarousel( root ) {
		var track = root.querySelector( '.qeema-testimonials__track' );
		qeemaInitVideoModal( root );
		if ( ! track ) {
			return;
		}
		var progressBar = root.querySelector( '.qeema-testimonials__progress-bar' );

		var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		var autoplayTimer = null;
		var resumeTimer = null;
		var animating = false;

		// the progress rail stands in for the (now hidden) native scrollbar —
		// it's the tell that this is a scrollable strip, not a static grid
		function updateProgress() {
			if ( ! progressBar ) {
				return;
			}
			var maxMagnitude = track.scrollWidth - track.clientWidth;
			if ( maxMagnitude <= 0 ) {
				return;
			}
			var widthPct = Math.max( 12, ( track.clientWidth / track.scrollWidth ) * 100 );
			var fraction = Math.min( 1, Math.max( 0, -track.scrollLeft / maxMagnitude ) );
			progressBar.style.width = widthPct + '%';
			progressBar.style.left = ( fraction * ( 100 - widthPct ) ) + '%';
		}

		function refreshScrollableState() {
			track.classList.toggle( 'is-scrollable', track.scrollWidth - track.clientWidth > 1 );
			updateProgress();
		}

		track.addEventListener( 'scroll', updateProgress, { passive: true } );
		window.addEventListener( 'resize', refreshScrollableState );

		function scrollAmount() {
			var card = track.querySelector( '.qeema-testimonial-card' );
			var gap = parseFloat( getComputedStyle( track ).columnGap ) || 0;
			return card ? card.offsetWidth + gap : 280;
		}

		function stopAutoplay() {
			if ( autoplayTimer ) {
				window.clearInterval( autoplayTimer );
				autoplayTimer = null;
			}
		}

		function tick() {
			if ( animating ) {
				return;
			}
			var maxMagnitude = Math.max( 0, track.scrollWidth - track.clientWidth );
			var target;
			if ( -track.scrollLeft >= maxMagnitude - 1 ) {
				// already showing the last complete set of cards — loop back to the start
				target = 0;
			} else {
				var nextTarget = track.scrollLeft - scrollAmount();
				// don't overshoot past the last card; land exactly on the end instead
				target = ( -nextTarget > maxMagnitude ) ? -maxMagnitude : nextTarget;
			}
			animating = true;
			smoothScrollTo( track, target, SCROLL_DURATION ).then( function () {
				animating = false;
			} );
		}

		function startAutoplay() {
			stopAutoplay();
			if ( reduceMotion ) {
				return;
			}
			autoplayTimer = window.setInterval( tick, AUTOPLAY_DELAY );
		}

		function restartAutoplaySoon() {
			stopAutoplay();
			window.clearTimeout( resumeTimer );
			resumeTimer = window.setTimeout( startAutoplay, RESUME_DELAY );
		}

		root.addEventListener( 'mouseenter', stopAutoplay );
		root.addEventListener( 'mouseleave', startAutoplay );

		// mouse click-and-drag to scroll, like a native swipe carousel
		var isDown = false;
		var dragStartX = 0;
		var dragStartScroll = 0;
		var dragDistance = 0;

		track.addEventListener( 'mousedown', function ( e ) {
			isDown = true;
			dragDistance = 0;
			dragStartX = e.pageX;
			dragStartScroll = track.scrollLeft;
			track.classList.add( 'is-dragging' );
			animating = false;
			stopAutoplay();
			e.preventDefault();
		} );

		window.addEventListener( 'mousemove', function ( e ) {
			if ( ! isDown ) {
				return;
			}
			var dx = e.pageX - dragStartX;
			dragDistance = Math.abs( dx );
			track.scrollLeft = dragStartScroll - dx;
		} );

		window.addEventListener( 'mouseup', function () {
			if ( ! isDown ) {
				return;
			}
			isDown = false;
			track.classList.remove( 'is-dragging' );
			restartAutoplaySoon();
		} );

		// dragging shouldn't also trigger the video-play button underneath the cursor
		track.addEventListener( 'click', function ( e ) {
			if ( dragDistance > 6 ) {
				e.preventDefault();
				e.stopPropagation();
			}
		}, true );

		refreshScrollableState();
		startAutoplay();
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
