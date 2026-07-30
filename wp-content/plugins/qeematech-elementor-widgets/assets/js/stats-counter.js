(function () {
	function qeemaAnimateCounter( el ) {
		if ( el.dataset.done === 'true' ) {
			return;
		}
		var target = parseInt( el.dataset.target || '0', 10 );
		var prefix = el.dataset.prefix || '';
		var suffix = el.dataset.suffix || '';
		var duration = 1600;
		var startTime = performance.now();

		function update( now ) {
			var progress = Math.min( ( now - startTime ) / duration, 1 );
			var eased = 1 - Math.pow( 1 - progress, 3 );
			var value = Math.floor( target * eased );
			el.textContent = prefix + value + suffix;

			if ( progress < 1 ) {
				requestAnimationFrame( update );
			} else {
				el.textContent = prefix + target + suffix;
				el.dataset.done = 'true';
			}
		}
		requestAnimationFrame( update );
	}

	function qeemaInitStatsCounters() {
		var counters = document.querySelectorAll( '.qeema-stat-box__num[data-target]' );
		if ( ! counters.length ) {
			return;
		}
		var observer = new IntersectionObserver( function ( entries, obs ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					qeemaAnimateCounter( entry.target );
					obs.unobserve( entry.target );
				}
			} );
		}, { threshold: 0.4 } );

		counters.forEach( function ( counter ) {
			if ( counter.dataset.observed === 'true' ) {
				return;
			}
			counter.dataset.observed = 'true';
			observer.observe( counter );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', qeemaInitStatsCounters );
	window.addEventListener( 'load', qeemaInitStatsCounters );
	if ( window.elementorFrontend ) {
		jQuery( window ).on( 'elementor/frontend/init', qeemaInitStatsCounters );
	}
})();
