(function () {
	// Shared between qeema-blog-archive and qeema-portfolio-archive: both
	// widgets render real '/page/N/' (+ optional '?cat=') links first
	// (progressive enhancement). Portfolio archive additionally supports
	// infinite scroll: IntersectionObserver + append mode on the same AJAX
	// endpoint, while filter clicks still replace the whole panel.
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

	function qeemaBuildBody( root, target, extra ) {
		var body = new URLSearchParams();
		body.set( 'action', root.dataset.ajaxAction );
		body.set( 'page_id', root.dataset.pageId || '' );
		body.set( 'paged', String( target.paged ) );
		body.set( 'cat', target.cat );
		body.set( 'posts_per_page', root.dataset.postsPerPage || '' );
		body.set( 'all_label', root.dataset.allLabel || '' );
		body.set( 'category', root.dataset.category || '' );
		body.set( 'excerpt_words', root.dataset.excerptWords || '' );
		body.set( 'locked_category', root.dataset.lockedCategory || '' );
		body.set( 'badge', root.dataset.badge || '' );
		body.set( 'heading', root.dataset.heading || '' );
		body.set( 'subheading', root.dataset.subheading || '' );
		body.set( 'meta_note', root.dataset.metaNote || '' );
		body.set( 'load_more_text', root.dataset.loadMoreText || '' );
		if ( extra ) {
			Object.keys( extra ).forEach( function ( key ) {
				body.set( key, extra[ key ] );
			} );
		}
		return body;
	}

	function qeemaRevealItems( nodes ) {
		nodes.forEach( function ( item, i ) {
			item.classList.add( 'qeema-portfolio-grid__item--enter' );
			item.style.transitionDelay = ( i * 0.04 ) + 's';
			requestAnimationFrame( function () {
				item.classList.add( 'is-in' );
			} );
		} );
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

		var infiniteEnabled = root.hasAttribute( 'data-qeema-infinite-archive' );
		var loadingMore = false;
		var observer = null;

		function disconnectObserver() {
			if ( observer ) {
				observer.disconnect();
				observer = null;
			}
		}

		function bindLinks() {
			root.querySelectorAll( LINK_SELECTOR ).forEach( function ( a ) {
				if ( a.dataset.qtBound === 'true' ) {
					return;
				}
				a.dataset.qtBound = 'true';
				a.addEventListener( 'click', function ( e ) {
					if ( e.metaKey || e.ctrlKey || e.shiftKey || e.altKey ) {
						return;
					}
					e.preventDefault();
					loadReplace( a.href, true );
				} );
			} );
		}

		function bindInfinite() {
			if ( ! infiniteEnabled ) {
				return;
			}
			disconnectObserver();

			var more = root.querySelector( '[data-qeema-infinite-more]' );
			if ( ! more || more.hidden || more.classList.contains( 'is-done' ) ) {
				return;
			}

			var btn = more.querySelector( '.qeema-portfolio-archive__load-btn' );
			if ( btn && btn.dataset.qtBound !== 'true' ) {
				btn.dataset.qtBound = 'true';
				btn.addEventListener( 'click', function () {
					loadAppend();
				} );
			}

			if ( ! ( 'IntersectionObserver' in window ) ) {
				return;
			}

			observer = new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						loadAppend();
					}
				} );
			}, { rootMargin: '280px 0px', threshold: 0.01 } );

			observer.observe( more );
		}

		function updateMoreState( data ) {
			var more = root.querySelector( '[data-qeema-infinite-more]' );
			if ( ! more ) {
				return;
			}
			if ( ! data.has_more ) {
				more.hidden = true;
				more.classList.add( 'is-done' );
				more.dataset.nextUrl = '';
				disconnectObserver();
				return;
			}
			more.hidden = false;
			more.classList.remove( 'is-done' );
			more.dataset.nextUrl = data.next_url || '';
			more.dataset.nextPage = String( data.next_page || '' );
			more.dataset.maxPages = String( data.max_pages || '' );
		}

		function setLoadingMore( on ) {
			loadingMore = on;
			var more = root.querySelector( '[data-qeema-infinite-more]' );
			if ( ! more ) {
				return;
			}
			more.classList.toggle( 'is-loading', on );
			var btn = more.querySelector( '.qeema-portfolio-archive__load-btn' );
			var status = more.querySelector( '.qeema-portfolio-archive__loading' );
			if ( btn ) {
				btn.hidden = on;
				btn.disabled = on;
			}
			if ( status ) {
				status.hidden = ! on;
			}
		}

		function loadAppend() {
			if ( loadingMore ) {
				return;
			}
			var more = root.querySelector( '[data-qeema-infinite-more]' );
			if ( ! more || more.hidden || more.classList.contains( 'is-done' ) ) {
				return;
			}
			var nextUrl = more.dataset.nextUrl;
			if ( ! nextUrl ) {
				return;
			}

			var target = qeemaParseTarget( nextUrl );
			setLoadingMore( true );

			fetch( ajaxUrl, {
				method: 'POST',
				body: qeemaBuildBody( root, target, { mode: 'append' } ),
				credentials: 'same-origin',
			} )
				.then( function ( r ) { return r.json(); } )
				.then( function ( json ) {
					if ( ! json || ! json.success || ! json.data ) {
						throw new Error( 'qeema-ajax-archive: unexpected append response' );
					}
					var data = json.data;
					var wrap = root.querySelector( '.qeema-portfolio-grid__wrap' );
					if ( ! wrap || typeof data.items_html !== 'string' ) {
						throw new Error( 'qeema-ajax-archive: missing items' );
					}

					var tmp = document.createElement( 'div' );
					tmp.innerHTML = data.items_html;
					var nodes = Array.prototype.slice.call( tmp.children );
					nodes.forEach( function ( node ) {
						wrap.appendChild( node );
					} );
					qeemaRevealItems( nodes );

					var grid = root.querySelector( '[data-qeema-archive-grid]' );
					if ( grid ) {
						grid.dataset.page = String( data.paged || '' );
						grid.dataset.maxPages = String( data.max_pages || '' );
					}

					updateMoreState( data );
					// Do not rewrite the URL on append — a refresh should restart
					// from the first batch and keep infinite scroll cumulative.
				} )
				.catch( function () {
					// Keep the button visible so the user can retry / fall back.
					var more = root.querySelector( '[data-qeema-infinite-more]' );
					if ( more && more.dataset.nextUrl ) {
						window.location.href = more.dataset.nextUrl;
					}
				} )
				.finally( function () {
					setLoadingMore( false );
					bindInfinite();
				} );
		}

		function loadReplace( url, pushState ) {
			var target = qeemaParseTarget( url );
			disconnectObserver();
			root.classList.add( 'is-loading' );

			fetch( ajaxUrl, {
				method: 'POST',
				body: qeemaBuildBody( root, target, { mode: 'replace' } ),
				credentials: 'same-origin',
			} )
				.then( function ( r ) { return r.json(); } )
				.then( function ( json ) {
					if ( ! json || ! json.success || ! json.data || 'string' !== typeof json.data.html ) {
						throw new Error( 'qeema-ajax-archive: unexpected response' );
					}
					root.innerHTML = json.data.html;
					bindLinks();
					bindInfinite();
					if ( pushState ) {
						window.history.pushState( { qeemaAjaxArchive: true }, '', url );
					}
					// Plain scrollIntoView({block:'start'}) puts the wrapper's
					// top edge at viewport y=0, which lands directly under the
					// site's fixed header - the filter tabs / first row of
					// results render hidden behind it right after every swap.
					// Compensate using the header's real (breakpoint-aware)
					// height instead of a hardcoded offset.
					var header       = document.querySelector( '.qeema-header' );
					var headerHeight = header ? header.offsetHeight : 0;
					var targetY      = root.getBoundingClientRect().top + window.scrollY - headerHeight - 16;
					window.scrollTo( { top: targetY, behavior: 'smooth' } );
				} )
				.catch( function () {
					window.location.href = url;
				} )
				.finally( function () {
					root.classList.remove( 'is-loading' );
				} );
		}

		bindLinks();
		bindInfinite();

		window.addEventListener( 'popstate', function () {
			loadReplace( window.location.href, false );
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
