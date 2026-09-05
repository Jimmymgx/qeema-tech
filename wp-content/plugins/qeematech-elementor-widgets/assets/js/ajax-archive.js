(function () {
	// Shared between qeema-blog-archive and qeema-portfolio-archive: both
	// widgets render a real, fully working '/page/N/' (+ optional '?cat=')
	// link first (progressive enhancement — the archive works identically
	// with JS disabled), then this script intercepts clicks on just the
	// pagination links / filter tabs inside the widget's own wrapper and
	// swaps the wrapper's contents via AJAX instead of a full page load.
	// Deliberately does NOT bind every <a> in the wrapper — the portfolio
	// grid's own card links (to case-study pages) live inside the same
	// wrapper and must keep navigating normally.
	var LINK_SELECTOR = '.qeema-portfolio-archive__filter, .qeema-blog-pagination a';

	function qeemaParseTarget( url ) {
		var u = new URL( url, window.location.href );
		var paged = 1;
		var m = u.pathname.match( /\/page\/(\d+)\/?$/ );
		if ( m ) {
			paged = parseInt( m[ 1 ], 10 ) || 1;
		}
		return { paged: paged, cat: u.searchParams.get( 'cat' ) || '' };
	}

	function qeemaInitOneArchive( root ) {
		if ( root.dataset.qtInitialized === 'true' ) {
			return;
		}
		root.dataset.qtInitialized = 'true';

		var ajaxUrl    = root.dataset.ajaxUrl;
		var ajaxAction = root.dataset.ajaxAction;
		if ( ! ajaxUrl || ! ajaxAction ) {
			return;
		}

		function bindLinks() {
			root.querySelectorAll( LINK_SELECTOR ).forEach( function ( a ) {
				if ( a.dataset.qtBound === 'true' ) {
					return;
				}
				a.dataset.qtBound = 'true';
				a.addEventListener( 'click', function ( e ) {
					if ( e.metaKey || e.ctrlKey || e.shiftKey || e.altKey ) {
						return; // let the user open it in a new tab/window as usual
					}
					e.preventDefault();
					load( a.href, true );
				} );
			} );
		}

		function load( url, pushState ) {
			var target = qeemaParseTarget( url );
			var body   = new URLSearchParams();
			body.set( 'action', ajaxAction );
			body.set( 'page_id', root.dataset.pageId || '' );
			body.set( 'paged', target.paged );
			body.set( 'cat', target.cat );
			body.set( 'posts_per_page', root.dataset.postsPerPage || '' );
			body.set( 'all_label', root.dataset.allLabel || '' );
			body.set( 'category', root.dataset.category || '' );
			body.set( 'excerpt_words', root.dataset.excerptWords || '' );
			body.set( 'locked_category', root.dataset.lockedCategory || '' );

			root.classList.add( 'is-loading' );

			fetch( ajaxUrl, { method: 'POST', body: body, credentials: 'same-origin' } )
				.then( function ( r ) { return r.json(); } )
				.then( function ( json ) {
					if ( ! json || ! json.success || ! json.data || 'string' !== typeof json.data.html ) {
						throw new Error( 'qeema-ajax-archive: unexpected response' );
					}
					root.innerHTML = json.data.html;
					bindLinks();
					if ( pushState ) {
						window.history.pushState( { qeemaAjaxArchive: true }, '', url );
					}
					root.scrollIntoView( { behavior: 'smooth', block: 'start' } );
				} )
				.catch( function () {
					// Progressive enhancement's fallback: if the AJAX request
					// fails for any reason, fall back to the real link the
					// server already rendered instead of leaving the click
					// silently doing nothing.
					window.location.href = url;
				} )
				.finally( function () {
					root.classList.remove( 'is-loading' );
				} );
		}

		bindLinks();

		window.addEventListener( 'popstate', function () {
			load( window.location.href, false );
		} );
	}

	function qeemaInitAllArchives() {
		document.querySelectorAll( '[data-qeema-ajax-archive]' ).forEach( qeemaInitOneArchive );
	}

	document.addEventListener( 'DOMContentLoaded', qeemaInitAllArchives );
	window.addEventListener( 'load', qeemaInitAllArchives );
	if ( window.elementorFrontend ) {
		jQuery( window ).on( 'elementor/frontend/init', qeemaInitAllArchives );
	}
})();
