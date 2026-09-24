(function () {
	function qeemaInitTypingWidgets() {
		document.querySelectorAll( '.qt-code-typing-widget' ).forEach( function ( widget ) {
			if ( widget.dataset.qtInitialized === 'true' ) {
				return;
			}
			widget.dataset.qtInitialized = 'true';

			var el = widget.querySelector( '.qt-typed-code' );
			var pre = widget.querySelector( '.qt-pre' );
			if ( ! el || ! pre ) {
				return;
			}

			var code = widget.dataset.qtCode || '';
			var i = 0;
			var speed = 18;
			var pause = 1200;

			// PERF-6: this used to read pre.scrollHeight and write pre.scrollTop
			// on every character tick (~18ms, ~450 times per typing cycle).
			// Deferring that pair to requestAnimationFrame (an earlier pass)
			// only moved *when* the forced layout happened, not *whether* it
			// did — reading scrollHeight right after a textContent mutation
			// still forces a synchronous recalc to answer that read, same as
			// before, just one frame later. A scrollTop write clamps itself
			// to the actual max scroll position, so writing an arbitrarily
			// large number scrolls to the bottom exactly like
			// `scrollTop = scrollHeight` does, but without ever reading
			// scrollHeight — a pure write, nothing to force a layout for.
			function type() {
				el.textContent = code.slice( 0, i++ );
				window.requestAnimationFrame( function () {
					pre.scrollTop = 1e9;
				} );
				if ( i <= code.length ) {
					setTimeout( type, speed );
				} else {
					setTimeout( function () {
						i = 0;
						type();
					}, pause );
				}
			}
			type();
		} );
	}

	function qeemaInitChatWidgets() {
		document.querySelectorAll( '.qt-chat-widget' ).forEach( function ( widget ) {
			if ( widget.dataset.qtInitialized === 'true' ) {
				return;
			}
			widget.dataset.qtInitialized = 'true';

			var clientBubble = widget.querySelector( '.qt-chat-bubble--in' );
			var clientText   = widget.querySelector( '.qt-chat-client-text' );
			var typingEl     = widget.querySelector( '.qt-chat-typing' );
			var replyBubble  = widget.querySelector( '.qt-chat-bubble--out' );
			var replyTextEl  = widget.querySelector( '.qt-chat-reply-text' );
			if ( ! clientBubble || ! replyBubble || ! replyTextEl ) {
				return;
			}

			var clientMsg = widget.dataset.qtChatClient || '';
			var replyMsg  = widget.dataset.qtChatReply || '';
			var speed     = 24;

			clientText.textContent = clientMsg;

			function reset() {
				clientBubble.classList.remove( 'is-visible' );
				typingEl.classList.remove( 'is-visible' );
				replyBubble.classList.remove( 'is-visible' );
				replyTextEl.textContent = '';
			}

			function typeReply( i ) {
				replyTextEl.textContent = replyMsg.slice( 0, i );
				if ( i < replyMsg.length ) {
					setTimeout( function () { typeReply( i + 1 ); }, speed );
				} else {
					setTimeout( loop, 2200 );
				}
			}

			function loop() {
				reset();
				setTimeout( function () {
					clientBubble.classList.add( 'is-visible' );
					setTimeout( function () {
						typingEl.classList.add( 'is-visible' );
						setTimeout( function () {
							typingEl.classList.remove( 'is-visible' );
							replyBubble.classList.add( 'is-visible' );
							typeReply( 0 );
						}, 900 );
					}, 500 );
				}, 400 );
			}

			loop();
		} );
	}

	function qeemaInitServiceOrbitWidgets() {
		document.querySelectorAll( '.qt-services-widget' ).forEach( function ( widget ) {
			if ( widget.dataset.qtInitialized === 'true' ) {
				return;
			}
			widget.dataset.qtInitialized = 'true';

			var slideEl = widget.querySelector( '.qt-services-slide' );
			var iconWrap = widget.querySelector( '.qt-services-icon-wrap' );
			var nameEl  = widget.querySelector( '.qt-services-name' );
			var dots    = widget.querySelectorAll( '.qt-services-dot' );
			if ( ! slideEl || ! iconWrap || ! nameEl || ! dots.length ) {
				return;
			}

			var services = [];
			try {
				services = JSON.parse( widget.dataset.qtServices || '[]' );
			} catch ( e ) {
				return;
			}
			if ( ! services.length ) {
				return;
			}

			var index = 0;

			function show( i ) {
				// PERF-2: services[i].svg is pre-rendered inline-SVG markup
				// from qeema_fa_svg() (about-hero-widget.php) - swapping
				// innerHTML here avoids depending on the Font Awesome
				// icon-font/CSS at runtime the way a className swap did.
				iconWrap.innerHTML = services[ i ].svg;
				nameEl.textContent = services[ i ].label;
				dots.forEach( function ( dot, di ) {
					dot.classList.toggle( 'is-active', di === i );
				} );
			}

			function next() {
				slideEl.classList.add( 'is-fading' );
				setTimeout( function () {
					index = ( index + 1 ) % services.length;
					show( index );
					slideEl.classList.remove( 'is-fading' );
				}, 350 );
			}

			setInterval( next, 2400 );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', qeemaInitTypingWidgets );
	window.addEventListener( 'load', qeemaInitTypingWidgets );
	document.addEventListener( 'DOMContentLoaded', qeemaInitChatWidgets );
	window.addEventListener( 'load', qeemaInitChatWidgets );
	document.addEventListener( 'DOMContentLoaded', qeemaInitServiceOrbitWidgets );
	window.addEventListener( 'load', qeemaInitServiceOrbitWidgets );
	if ( window.elementorFrontend ) {
		jQuery( window ).on( 'elementor/frontend/init', qeemaInitTypingWidgets );
		jQuery( window ).on( 'elementor/frontend/init', qeemaInitChatWidgets );
		jQuery( window ).on( 'elementor/frontend/init', qeemaInitServiceOrbitWidgets );
	}
})();
