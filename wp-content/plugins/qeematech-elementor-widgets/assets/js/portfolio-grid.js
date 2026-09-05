(function () {
	function slugFromHref( href ) {
		try {
			var url = new URL( href, window.location.href );
			var parts = url.pathname.split( '/' ).filter( Boolean );
			return parts.length ? decodeURIComponent( parts[ parts.length - 1 ] ) : '';
		} catch ( e ) {
			return '';
		}
	}

	function qeemaInitPortfolioGrid() {
		document.querySelectorAll( '.qeema-portfolio-grid' ).forEach( qeemaInitOneGrid );
	}

	// Scoped per grid (rather than reading tabs/items from the whole document)
	// so each qeema-portfolio-teaser instance filters and caps its own cards
	// independently if more than one ever ends up on the same page.
	function qeemaInitOneGrid( grid ) {
		if ( grid.qeemaGridInitialized ) {
			return;
		}
		grid.qeemaGridInitialized = true;

		var section = grid.closest( '.qeema-portfolio-teaser' ) || document;
		var tabs = section.querySelectorAll( '.qeema-portfolio-teaser__cat' );
		var items = grid.querySelectorAll( '.qeema-portfolio-grid__item' );
		var counter = grid.querySelector( '.qeema-portfolio-grid__count' );
		var showMoreBtn = grid.querySelector( '.qeema-portfolio-grid__show-more' );
		var initialCount = parseInt( grid.dataset.initialCount || '0', 10 );
		var expanded = false;

		if ( ! items.length ) {
			return;
		}

		// Cards not matching the active filter fade out first, THEN drop out of
		// flow (display:none) so the grid reflows only once the fade finishes -
		// matching cards fade back in with a small stagger instead of snapping
		// in all at once. Cards beyond the initial-count cap (when set) follow
		// the same hide path as non-matching cards until "show more" is clicked.
		function applyFilter( slug ) {
			var visibleIndex = 0;
			var matchedIndex = 0;
			// A tab's slug can be a single category or several comma-joined
			// categories (e.g. a combined "all website types" tab spanning
			// company/educational/tourism/news) - show an item if it matches
			// any one of them. A plain single-slug tab still works unchanged
			// since splitting a comma-free string just yields itself.
			var wanted = slug ? slug.split( ',' ) : [];

			items.forEach( function ( item ) {
				var cats = ( item.dataset.cats || '' ).split( ' ' );
				var matches = ! slug || cats.some( function ( cat ) {
					return wanted.indexOf( cat ) !== -1;
				} );

				var show = false;
				if ( matches ) {
					matchedIndex++;
					show = ! initialCount || expanded || matchedIndex <= initialCount;
				}

				if ( show ) {
					item.style.transitionDelay = ( visibleIndex * 0.03 ) + 's';
					visibleIndex++;
					if ( item.style.display === 'none' ) {
						item.style.display = '';
						// force reflow so the browser registers the pre-transition
						// state before the class below flips it
						void item.offsetWidth;
					}
					item.classList.remove( 'qeema-portfolio-grid__item--hide' );
				} else {
					item.style.transitionDelay = '0s';
					item.classList.add( 'qeema-portfolio-grid__item--hide' );
					window.setTimeout( function () {
						if ( item.classList.contains( 'qeema-portfolio-grid__item--hide' ) ) {
							item.style.display = 'none';
						}
					}, 260 );
				}
			} );

			if ( counter ) {
				counter.textContent = matchedIndex + ' مشروع';
			}

			if ( showMoreBtn ) {
				showMoreBtn.style.display = ( initialCount && ! expanded && matchedIndex > initialCount ) ? '' : 'none';
			}
		}

		function currentSlug() {
			var activeTab = null;
			tabs.forEach( function ( t ) {
				if ( t.classList.contains( 'active' ) ) {
					activeTab = t;
				}
			} );
			return activeTab ? slugFromHref( activeTab.getAttribute( 'href' ) || '' ) : '';
		}

		tabs.forEach( function ( tab ) {
			tab.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				tabs.forEach( function ( t ) {
					t.classList.remove( 'active' );
				} );
				tab.classList.add( 'active' );
				expanded = false;
				applyFilter( slugFromHref( tab.getAttribute( 'href' ) || '' ) );
			} );
		} );

		if ( showMoreBtn ) {
			showMoreBtn.addEventListener( 'click', function () {
				expanded = true;
				applyFilter( currentSlug() );
			} );
		}

		applyFilter( currentSlug() );
	}

	document.addEventListener( 'DOMContentLoaded', qeemaInitPortfolioGrid );
	if ( window.elementorFrontend ) {
		jQuery( window ).on( 'elementor/frontend/init', qeemaInitPortfolioGrid );
	}
})();
