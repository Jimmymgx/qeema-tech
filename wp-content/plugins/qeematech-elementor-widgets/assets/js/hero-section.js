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

			function type() {
				el.textContent = code.slice( 0, i++ );
				pre.scrollTop = pre.scrollHeight;
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
			var iconEl  = widget.querySelector( '.qt-services-icon' );
			var nameEl  = widget.querySelector( '.qt-services-name' );
			var dots    = widget.querySelectorAll( '.qt-services-dot' );
			if ( ! slideEl || ! iconEl || ! nameEl || ! dots.length ) {
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
				iconEl.className = services[ i ].icon + ' qt-services-icon';
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
