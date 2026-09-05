(function () {
	var overlay = document.getElementById( 'qeema-preloader' );
	if ( ! overlay ) {
		return;
	}
	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	var html = document.documentElement;
	html.classList.add( 'qeema-preloader-active' );

	var finished = false;
	var minTimeDone = false;
	var loadFired = false;

	function maybeFinish() {
		if ( minTimeDone && loadFired ) {
			finish();
		}
	}

	function finish() {
		if ( finished ) {
			return;
		}
		finished = true;
		overlay.classList.add( 'qeema-preloader--done' );
		setTimeout( function () {
			html.classList.remove( 'qeema-preloader-active' );
			if ( overlay.parentNode ) {
				overlay.parentNode.removeChild( overlay );
			}
		}, 500 );
	}

	// Gate on the DOM being parsed/ready rather than window 'load' — the latter
	// waits for every image/video on the page to finish downloading, which on
	// media-heavy pages left the overlay up far longer than the animation
	// itself needed. This script runs in the footer, so DOMContentLoaded may
	// already have fired by the time it executes — check readyState first.
	function onDomReady( fn ) {
		if ( 'loading' === document.readyState ) {
			document.addEventListener( 'DOMContentLoaded', fn );
		} else {
			fn();
		}
	}
	onDomReady( function () {
		loadFired = true;
		maybeFinish();
	} );

	// Safety net: never let a slow third-party script/resource leave a
	// visitor stuck behind the overlay indefinitely.
	setTimeout( function () {
		loadFired = true;
		minTimeDone = true;
		finish();
	}, 6000 );

	// Minimum display time so the assemble animation gets to actually play at
	// least one pass (pieces flying in and snapping together) instead of
	// flashing past before a fast page load dismisses it.
	setTimeout( function () {
		minTimeDone = true;
		maybeFinish();
	}, reduceMotion ? 150 : 900 );
})();
