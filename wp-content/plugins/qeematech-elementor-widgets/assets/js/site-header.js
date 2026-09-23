(function () {
	function qeemaInitHeader( root ) {
		var toggle = root.querySelector( '.qeema-header__toggle' );
		var panel = root.querySelector( '.qeema-header__mobile-panel' );
		var overlay = root.querySelector( '.qeema-header__overlay' );
		var closeBtn = root.querySelector( '.qeema-header__mobile-close' );
		if ( ! toggle || ! panel ) {
			return;
		}

		function openPanel() {
			panel.classList.add( 'qeema-open' );
			if ( overlay ) {
				overlay.classList.add( 'qeema-open' );
			}
			toggle.setAttribute( 'aria-expanded', 'true' );
		}
		function closePanel() {
			panel.classList.remove( 'qeema-open' );
			if ( overlay ) {
				overlay.classList.remove( 'qeema-open' );
			}
			toggle.setAttribute( 'aria-expanded', 'false' );
		}

		toggle.addEventListener( 'click', openPanel );
		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', closePanel );
		}
		if ( overlay ) {
			overlay.addEventListener( 'click', closePanel );
		}

		// Per-item accordion for mobile dropdown children - a separate
		// toggle button next to the label so tapping the chevron expands
		// the submenu without hijacking the label's own link navigation.
		panel.querySelectorAll( '.qeema-mobile-toggle' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				var item = btn.closest( '.qeema-mobile-item' );
				var expanded = item.classList.toggle( 'qeema-expanded' );
				btn.setAttribute( 'aria-expanded', expanded ? 'true' : 'false' );
			} );
		} );
	}

	function qeemaInitScrollState( root ) {
		function update() {
			root.classList.toggle( 'qeema-scrolled', window.scrollY > 40 );
		}
		window.addEventListener( 'scroll', update, { passive: true } );
		update();
	}

	function qeemaInitProgressBar( root ) {
		var fill = root.querySelector( '.qeema-progress-bar__fill' );
		if ( ! fill ) {
			return;
		}
		var ticking = false;
		function update() {
			var scrollable = document.documentElement.scrollHeight - window.innerHeight;
			var pct = scrollable > 0 ? ( window.scrollY / scrollable ) * 100 : 0;
			fill.style.transform = 'scaleX(' + ( pct / 100 ) + ')';
			ticking = false;
		}
		function onScroll() {
			if ( ! ticking ) {
				ticking = true;
				window.requestAnimationFrame( update );
			}
		}
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		window.addEventListener( 'resize', update );
		update();
	}

	// The header is position:fixed (Elementor's Theme Builder wraps it in
	// containers sized to the header itself, leaving no room for
	// position:sticky to work), so normal document flow no longer reserves
	// space for it - push the page down by its rendered height instead.
	function qeemaInitFixedOffset( root ) {
		function update() {
			document.body.style.paddingTop = root.offsetHeight + 'px';
		}
		update();
		window.addEventListener( 'resize', update );
		// On slow connections the logo may not have finished decoding by
		// DOMContentLoaded, so offsetHeight is measured too small and the
		// page's first section renders partly under the fixed header until
		// something else triggers a resize (which may never happen on
		// mobile). Re-measure on window load and on each logo image's own
		// load event, matching the fallback pattern used elsewhere in this
		// plugin (works-hero-slider.js, hero-section.js, stats-counter.js).
		window.addEventListener( 'load', update );
		root.querySelectorAll( 'img' ).forEach( function ( img ) {
			if ( ! img.complete ) {
				img.addEventListener( 'load', update, { once: true } );
			}
		} );
	}

	function qeemaInitAllHeaders() {
		document.querySelectorAll( '.qeema-header' ).forEach( function ( root ) {
			if ( root.dataset.qeemaInit === 'true' ) {
				return;
			}
			root.dataset.qeemaInit = 'true';
			qeemaInitHeader( root );
			qeemaInitScrollState( root );
			qeemaInitFixedOffset( root );
			qeemaInitProgressBar( root );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', qeemaInitAllHeaders );
	if ( window.elementorFrontend ) {
		jQuery( window ).on( 'elementor/frontend/init', qeemaInitAllHeaders );
	}
})();
