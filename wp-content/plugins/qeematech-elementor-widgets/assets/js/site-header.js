(function () {
	function qeemaInitHeader( root ) {
		var toggle = root.querySelector( '.qeema-header__toggle' );
		var panel = root.querySelector( '.qeema-header__mobile-panel' );
		var closeBtn = root.querySelector( '.qeema-header__mobile-close' );
		if ( ! toggle || ! panel ) {
			return;
		}
		toggle.addEventListener( 'click', function () {
			panel.classList.add( 'qeema-open' );
			toggle.setAttribute( 'aria-expanded', 'true' );
		} );
		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', function () {
				panel.classList.remove( 'qeema-open' );
				toggle.setAttribute( 'aria-expanded', 'false' );
			} );
		}
	}

	function qeemaInitAllHeaders() {
		document.querySelectorAll( '.qeema-header' ).forEach( function ( root ) {
			if ( root.dataset.qeemaInit === 'true' ) {
				return;
			}
			root.dataset.qeemaInit = 'true';
			qeemaInitHeader( root );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', qeemaInitAllHeaders );
	if ( window.elementorFrontend ) {
		jQuery( window ).on( 'elementor/frontend/init', qeemaInitAllHeaders );
	}
})();
