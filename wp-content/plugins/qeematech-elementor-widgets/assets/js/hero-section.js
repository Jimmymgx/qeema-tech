(function () {
	function qeemaInitTypingWidgets() {
		document.querySelectorAll( '.qt-code-typing-widget' ).forEach( function ( widget ) {
			if ( widget.dataset.qtInitialized === 'true' ) {
				return;
			}
			widget.dataset.qtInitialized = 'true';

			var el = widget.querySelector( '.qt-typed-code' );
			var pre = widget.querySelector( '.qt-pre' );
			if ( ! el || ! pre ) {
				return;
			}

			var code = widget.dataset.qtCode || '';
			var i = 0;
			var speed = 18;
			var pause = 1200;

			function type() {
				el.textContent = code.slice( 0, i++ );
				pre.scrollTop = pre.scrollHeight;
				if ( i <= code.length ) {
					setTimeout( type, speed );
				} else {
					setTimeout( function () {
						i = 0;
						type();
					}, pause );
				}
			}
			type();
		} );
	}

	document.addEventListener( 'DOMContentLoaded', qeemaInitTypingWidgets );
	window.addEventListener( 'load', qeemaInitTypingWidgets );
	if ( window.elementorFrontend ) {
		jQuery( window ).on( 'elementor/frontend/init', qeemaInitTypingWidgets );
	}
})();
