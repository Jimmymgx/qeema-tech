(function () {
	function extractAccent( img ) {
		var size = 32;
		var canvas = document.createElement( 'canvas' );
		canvas.width = size;
		canvas.height = size;
		var ctx = canvas.getContext( '2d' );

		try {
			ctx.drawImage( img, 0, 0, size, size );
			var data = ctx.getImageData( 0, 0, size, size ).data;
		} catch ( e ) {
			return null; // tainted canvas (cross-origin without CORS) — keep the CSS fallback accent
		}

		var r = 0, g = 0, b = 0, wsum = 0;
		for ( var i = 0; i < data.length; i += 4 ) {
			var rr = data[ i ], gg = data[ i + 1 ], bb = data[ i + 2 ], aa = data[ i + 3 ];
			if ( aa < 200 ) {
				continue;
			}
			var max = Math.max( rr, gg, bb );
			var min = Math.min( rr, gg, bb );
			var lightness = ( max + min ) / 2 / 255;
			if ( lightness > 0.93 || lightness < 0.06 ) {
				continue; // skip near-white/near-black pixels (icon padding, backgrounds)
			}
			var sat = max === min ? 0 : ( max - min ) / ( 255 - Math.abs( max + min - 255 ) );
			var weight = 0.15 + sat * sat;
			r += rr * weight;
			g += gg * weight;
			b += bb * weight;
			wsum += weight;
		}

		if ( wsum === 0 ) {
			return null;
		}

		r = Math.round( r / wsum );
		g = Math.round( g / wsum );
		b = Math.round( b / wsum );

		var brightest = Math.max( r, g, b );
		if ( brightest < 80 && brightest > 0 ) {
			var boost = 140 / brightest;
			r = Math.min( 255, Math.round( r * boost ) );
			g = Math.min( 255, Math.round( g * boost ) );
			b = Math.min( 255, Math.round( b * boost ) );
		}

		return [ r, g, b ];
	}

	function applyAccent( card ) {
		var img = card.querySelector( '[data-qeema-logo]' );
		if ( ! img ) {
			return;
		}

		var run = function () {
			var rgb = extractAccent( img );
			if ( ! rgb ) {
				return;
			}
			card.style.setProperty( '--ax', rgb[ 0 ] );
			card.style.setProperty( '--ay', rgb[ 1 ] );
			card.style.setProperty( '--az', rgb[ 2 ] );
		};

		if ( img.complete && img.naturalWidth ) {
			run();
		} else {
			img.addEventListener( 'load', run );
		}
	}

	function init() {
		document.querySelectorAll( '.qeema-app-store-proof__card' ).forEach( applyAccent );
	}

	document.addEventListener( 'DOMContentLoaded', init );
	if ( window.elementorFrontend ) {
		jQuery( window ).on( 'elementor/frontend/init', init );
	}
})();
