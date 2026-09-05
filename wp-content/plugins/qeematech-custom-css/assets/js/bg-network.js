(function () {
	var canvas = document.getElementById( 'qeema-bg-network' );
	if ( ! canvas || ! canvas.getContext ) {
		return;
	}
	var ctx = canvas.getContext( '2d' );
	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var palette = [ '#22d3ee', '#8b5cf6', '#3b82f6', '#00ffaa' ];
	var particles = [];
	var mouse = { x: null, y: null };
	var rafId = null;
	var width = 0, height = 0;

	function sizeCanvas() {
		width = window.innerWidth;
		height = window.innerHeight;
		var dpr = window.devicePixelRatio || 1;
		canvas.width = width * dpr;
		canvas.height = height * dpr;
		canvas.style.width = width + 'px';
		canvas.style.height = height + 'px';
		ctx.setTransform( dpr, 0, 0, dpr, 0, 0 );
	}

	function initParticles() {
		var count = Math.round( ( width * height ) / 16000 );
		count = Math.max( 32, Math.min( count, 110 ) );
		particles = [];
		for ( var i = 0; i < count; i++ ) {
			particles.push( {
				x: Math.random() * width,
				y: Math.random() * height,
				vx: ( Math.random() - 0.5 ) * 0.3,
				vy: ( Math.random() - 0.5 ) * 0.3,
				r: 1.4 + Math.random() * 1.6,
				color: palette[ i % palette.length ]
			} );
		}
	}

	function step() {
		ctx.clearRect( 0, 0, width, height );

		particles.forEach( function ( p ) {
			p.x += p.vx;
			p.y += p.vy;
			if ( p.x < 0 || p.x > width ) p.vx *= -1;
			if ( p.y < 0 || p.y > height ) p.vy *= -1;
		} );

		for ( var i = 0; i < particles.length; i++ ) {
			for ( var j = i + 1; j < particles.length; j++ ) {
				var a = particles[ i ], b = particles[ j ];
				var dx = a.x - b.x, dy = a.y - b.y;
				var dist = Math.sqrt( dx * dx + dy * dy );
				var maxDist = 130;
				if ( dist < maxDist ) {
					ctx.strokeStyle = 'rgba(34,211,238,' + ( 0.16 * ( 1 - dist / maxDist ) ) + ')';
					ctx.lineWidth = 1;
					ctx.beginPath();
					ctx.moveTo( a.x, a.y );
					ctx.lineTo( b.x, b.y );
					ctx.stroke();
				}
			}
			if ( mouse.x !== null ) {
				var mdx = particles[ i ].x - mouse.x, mdy = particles[ i ].y - mouse.y;
				var mdist = Math.sqrt( mdx * mdx + mdy * mdy );
				if ( mdist < 170 ) {
					ctx.strokeStyle = 'rgba(255,255,255,' + ( 0.22 * ( 1 - mdist / 170 ) ) + ')';
					ctx.lineWidth = 1;
					ctx.beginPath();
					ctx.moveTo( particles[ i ].x, particles[ i ].y );
					ctx.lineTo( mouse.x, mouse.y );
					ctx.stroke();
				}
			}
		}

		particles.forEach( function ( p ) {
			ctx.beginPath();
			ctx.arc( p.x, p.y, p.r, 0, Math.PI * 2 );
			ctx.fillStyle = p.color;
			ctx.shadowColor = p.color;
			ctx.shadowBlur = 7;
			ctx.fill();
			ctx.shadowBlur = 0;
		} );

		if ( ! reduceMotion && ! document.hidden ) {
			rafId = requestAnimationFrame( step );
		}
	}

	function start() {
		if ( rafId ) return;
		rafId = requestAnimationFrame( step );
	}
	function stop() {
		if ( rafId ) cancelAnimationFrame( rafId );
		rafId = null;
	}

	sizeCanvas();
	initParticles();
	step();

	window.addEventListener( 'resize', function () {
		sizeCanvas();
		initParticles();
	} );
	window.addEventListener( 'mousemove', function ( e ) {
		mouse.x = e.clientX;
		mouse.y = e.clientY;
	} );
	window.addEventListener( 'mouseleave', function () {
		mouse.x = null;
		mouse.y = null;
	} );
	document.addEventListener( 'visibilitychange', function () {
		if ( document.hidden ) {
			stop();
		} else if ( ! reduceMotion ) {
			start();
		}
	} );
})();
