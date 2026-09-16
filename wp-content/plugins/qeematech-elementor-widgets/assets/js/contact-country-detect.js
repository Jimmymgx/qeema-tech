(function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		if ( typeof qeemaCountryDetect === 'undefined' || ! qeemaCountryDetect.ajaxUrl ) {
			return;
		}
		var select = document.getElementById( 'form-field-country' );
		if ( ! select || select.tagName !== 'SELECT' ) {
			return;
		}

		var url = qeemaCountryDetect.ajaxUrl + '?action=qeema_detect_country';
		fetch( url, { credentials: 'same-origin' } )
			.then( function ( res ) { return res.json(); } )
			.then( function ( json ) {
				var dial = json && json.data ? json.data.dial : null;
				if ( ! dial ) {
					return;
				}
				var matched = false;
				for ( var i = 0; i < select.options.length; i++ ) {
					if ( select.options[ i ].value === dial ) {
						select.selectedIndex = i;
						matched = true;
						break;
					}
				}
				if ( matched ) {
					select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				}
			} )
			.catch( function () {
				// Detection failing is fine — the field just stays unselected.
			} );
	} );
})();
