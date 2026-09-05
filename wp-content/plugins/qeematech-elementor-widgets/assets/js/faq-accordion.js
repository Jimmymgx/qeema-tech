(function () {
	var sections = document.querySelectorAll( '.qeema-faq' );
	if ( ! sections.length ) {
		return;
	}

	sections.forEach( function ( section ) {
		var items = section.querySelectorAll( '.qeema-faq-item' );

		items.forEach( function ( item ) {
			var trigger = item.querySelector( '.qeema-faq-item__q' );
			if ( ! trigger ) {
				return;
			}

			trigger.addEventListener( 'click', function () {
				var wasOpen = item.classList.contains( 'is-open' );

				items.forEach( function ( other ) {
					other.classList.remove( 'is-open' );
					var otherTrigger = other.querySelector( '.qeema-faq-item__q' );
					if ( otherTrigger ) {
						otherTrigger.setAttribute( 'aria-expanded', 'false' );
					}
				} );

				if ( ! wasOpen ) {
					item.classList.add( 'is-open' );
					trigger.setAttribute( 'aria-expanded', 'true' );
				}
			} );
		} );
	} );
})();
