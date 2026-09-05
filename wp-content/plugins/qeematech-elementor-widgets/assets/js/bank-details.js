(function () {
	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '.qeema-bank-details__copy' );
		if ( ! button ) {
			return;
		}

		var value = button.getAttribute( 'data-copy-value' );
		if ( ! value || ! navigator.clipboard ) {
			return;
		}

		navigator.clipboard.writeText( value ).then( function () {
			var icon = button.querySelector( 'i' );
			var originalClass = icon ? icon.className : '';

			button.classList.add( 'is-copied' );
			if ( icon ) {
				icon.className = 'fas fa-check';
			}

			window.clearTimeout( button._qeemaCopyTimeout );
			button._qeemaCopyTimeout = window.setTimeout( function () {
				button.classList.remove( 'is-copied' );
				if ( icon ) {
					icon.className = originalClass;
				}
			}, 1500 );
		} );
	} );
})();
