(function () {
	// How far (px) a drag has to travel to fully rotate the 3-card fan by one
	// slot - matches live-apps-carousel.js's DRAG_PX_PER_INDEX pattern (1:1
	// pointer tracking, not stepped), sized here to roughly one card-width of
	// travel per slot advance.
	var DRAG_PX_PER_INDEX = 230;
	// Below this, a press+release is a plain click/tap (navigate to the
	// app's store link) rather than a drag.
	var DRAG_CLICK_THRESHOLD_PX = 6;

	// This widget has exactly 3 fixed, hand-tuned visual slots (front-center
	// + 2 angled side cards - see .app-mock-a/b/c in style.css), not a
	// repeating N-item ring like the homepage carousel. So instead of one
	// linear formula driven by abs(offset), each slot's geometry is looked
	// up from these keyframes (mirroring style.css's --dx/--dy/--s/--tilt/
	// opacity/z-index custom properties) and interpolated between them.
	//
	// FRONT = slot a (dead center, full size). RIGHT = slot c (tilt +26deg).
	// LEFT = slot b (tilt -26deg). Values below are the same numbers
	// style.css assigns to .app-mock-b/.app-mock-c at each breakpoint tier -
	// kept in sync manually since custom-property values aren't practical to
	// read back reliably mid-transition via getComputedStyle.
	var FRONT = { dx: 0, dy: 0, s: 1, tilt: 0, opacity: 1, z: 3 };
	var TIER_DESKTOP = {
		left:  { dx: -275, dy: 28, s: .8261, tilt: -26, opacity: .88, z: 2 },
		right: { dx: 275, dy: 28, s: .8261, tilt: 26, opacity: .82, z: 1 }
	};
	var TIER_TABLET = {
		left:  { dx: -220, dy: 28, s: .8, tilt: -26, opacity: .88, z: 2 },
		right: { dx: 230, dy: 28, s: .8, tilt: 26, opacity: .82, z: 1 }
	};
	// Where a card interpolates to once it's dragged past its own slot (i.e.
	// swinging behind the stage on its way to the opposite slot) - small,
	// centered, transparent, sent to the back. This is what keeps the
	// rotation a continuous "merry-go-round" instead of the two side cards
	// jump-swapping the instant a drag crosses the halfway point between
	// them (see geometryAt() below for the two-hop interpolation this feeds).
	var HIDDEN = { dx: 0, dy: 14, s: .5, tilt: 0, opacity: 0, z: 0 };

	function tier() {
		return window.innerWidth <= 1024 ? TIER_TABLET : TIER_DESKTOP;
	}

	// Below this width, style.css switches the stage to a plain horizontal
	// overflow-x scroll-snap strip (position:static cards) - the fan/slot
	// geometry this file computes doesn't apply there at all, so the drag
	// rotation is deliberately left off and the browser's native scroll +
	// each card's real <a href> tap-to-navigate carries the mobile
	// experience instead (simpler and lower-risk than reimplementing this
	// same pointer physics against a scrolling flex strip's geometry).
	function isMobileTier() {
		return window.innerWidth <= 768;
	}

	function lerp( a, b, t ) {
		return a + ( b - a ) * t;
	}

	function lerpGeometry( g1, g2, t ) {
		return {
			dx: lerp( g1.dx, g2.dx, t ),
			dy: lerp( g1.dy, g2.dy, t ),
			s: lerp( g1.s, g2.s, t ),
			tilt: lerp( g1.tilt, g2.tilt, t ),
			opacity: lerp( g1.opacity, g2.opacity, t ),
			z: lerp( g1.z, g2.z, t )
		};
	}

	// offset is a continuous, wrapped position in (-1.5, 1.5] - 0 is dead
	// center (front), +1 is fully in the right slot, -1 fully in the left
	// slot. Past +-1 (only reachable mid-drag, past a full slot's worth of
	// travel) it keeps interpolating on to HIDDEN so the card visibly swings
	// out and fades behind the stage rather than snapping, then grows back
	// in from the opposite side as the wrap continues - the two halves meet
	// exactly at HIDDEN on both sides so there's no seam.
	function geometryAt( offset, t ) {
		if ( offset >= 0 ) {
			return offset <= 1
				? lerpGeometry( FRONT, t.right, offset )
				: lerpGeometry( t.right, HIDDEN, ( offset - 1 ) / 0.5 );
		}
		var a = -offset;
		return a <= 1
			? lerpGeometry( FRONT, t.left, a )
			: lerpGeometry( t.left, HIDDEN, ( a - 1 ) / 0.5 );
	}

	function applyGeometry( card, g ) {
		card.style.setProperty( '--dx', g.dx + 'px' );
		card.style.setProperty( '--dy', g.dy + 'px' );
		card.style.setProperty( '--s', g.s );
		card.style.setProperty( '--tilt', g.tilt + 'deg' );
		card.style.opacity = g.opacity;
		card.style.zIndex = Math.round( g.z );
	}

	// Hands the box back to style.css's plain class-based mobile rules -
	// necessary because an inline style (even one JS set at a wider
	// viewport) otherwise keeps outranking those class rules after a resize
	// crosses down into the mobile breakpoint.
	function clearGeometry( card ) {
		[ '--dx', '--dy', '--s', '--tilt' ].forEach( function ( prop ) {
			card.style.removeProperty( prop );
		} );
		card.style.opacity = '';
		card.style.zIndex = '';
	}

	function qeemaInitAppStoreProof( root ) {
		var stage = root.querySelector( '.qeema-app-store-proof__stage' );
		if ( ! stage ) {
			return;
		}
		var allCards = Array.prototype.slice.call( stage.querySelectorAll( '.qeema-app-store-proof__card' ) );
		// The drag/rotate interaction only makes sense with all 3 fixed
		// slots filled - with fewer, there's nothing to rotate into.
		if ( allCards.length < 3 ) {
			return;
		}

		// Fixed identity per card (never reassigned): 0 = starts front
		// (slot a), 1 = starts right (slot c), 2 = starts left (slot b).
		// `active` below is which identity currently occupies the front
		// slot; dragging moves the *identities* through the 3 slots rather
		// than moving the DOM elements themselves.
		function bySlot( letter ) {
			return allCards.filter( function ( c ) {
				return c.classList.contains( 'app-mock-' + letter );
			} )[ 0 ];
		}
		var cards = [ bySlot( 'a' ), bySlot( 'c' ), bySlot( 'b' ) ];
		if ( cards.indexOf( undefined ) !== -1 ) {
			cards = allCards.slice( 0, 3 );
		}

		var active = 0;
		var isDragging = false;
		var dragMoved = false;
		var dragPointerId = null;
		var dragStartX = 0;
		var dragStartActive = 0;
		var dragCurrentPos = 0;

		function render( pos ) {
			if ( 'number' !== typeof pos ) {
				pos = active;
			}
			if ( isMobileTier() ) {
				cards.forEach( clearGeometry );
				return;
			}
			var t = tier();
			cards.forEach( function ( card, i ) {
				var offset = i - pos;
				if ( offset > 1.5 ) {
					offset -= 3;
				}
				if ( offset < -1.5 ) {
					offset += 3;
				}
				applyGeometry( card, geometryAt( offset, t ) );
			} );
		}

		function goTo( index ) {
			active = ( index % 3 + 3 ) % 3;
			render();
		}

		function onDragStart( e ) {
			if ( isMobileTier() || ! e.isPrimary || null !== dragPointerId ) {
				return;
			}
			dragPointerId = e.pointerId;
			dragStartX = e.clientX;
			dragStartActive = active;
			dragCurrentPos = active;
			dragMoved = false;
			isDragging = true;
			root.classList.add( 'is-dragging' );
			// Deliberately NOT using stage.setPointerCapture() here (unlike
			// live-apps-carousel.js's phones, which are plain divs with no
			// native navigation to preserve): capturing on the stage
			// redirects the eventual `click` event's target to the stage
			// div itself instead of whichever card the pointer went down on
			// - confirmed by testing this widget, that silently breaks
			// native href navigation on a plain click (no preventDefault
			// involved, the click's target just stops being the <a>).
			// Tracking the drag via window-level listeners instead achieves
			// the same "keep receiving events even if the pointer leaves
			// the stage" goal without touching click targeting.
			window.addEventListener( 'pointermove', onDragMove );
			window.addEventListener( 'pointerup', onDragEnd );
			window.addEventListener( 'pointercancel', onDragEnd );
			// Cards are real <a href> tags (kept on purpose for SEO / a
			// no-JS fallback), and an <a> is natively HTML5-draggable - left
			// unchecked, the browser recognizes the gesture as a native
			// link drag after the first small move and takes over the
			// pointer entirely, so pointermove/pointerup silently stop
			// arriving mid-gesture (confirmed by testing this widget - the
			// drag would freeze after one pointermove event with no
			// pointerup ever firing). preventDefault() on pointerdown is
			// the standard way to suppress that native drag-initiation; it
			// does NOT suppress the later `click` event for a real mouse
			// pointer (that only happens for touch/pen "compatibility
			// mouse events" per the Pointer Events spec) - the earlier
			// regression where a plain click stopped navigating was
			// actually caused by stage.setPointerCapture() above, not by
			// this preventDefault() call, and disappeared once capture was
			// removed.
			if ( e.cancelable ) {
				e.preventDefault();
			}
		}

		function onDragMove( e ) {
			if ( ! isDragging || e.pointerId !== dragPointerId ) {
				return;
			}
			var delta = e.clientX - dragStartX;
			if ( Math.abs( delta ) > DRAG_CLICK_THRESHOLD_PX ) {
				dragMoved = true;
			}
			dragCurrentPos = dragStartActive - delta / DRAG_PX_PER_INDEX;
			render( dragCurrentPos );
		}

		function endDrag() {
			isDragging = false;
			dragPointerId = null;
			root.classList.remove( 'is-dragging' );
			window.removeEventListener( 'pointermove', onDragMove );
			window.removeEventListener( 'pointerup', onDragEnd );
			window.removeEventListener( 'pointercancel', onDragEnd );
			// Rounds to the nearest whole slot and lets the CSS `transform`
			// transition (already declared on .qeema-app-store-proof__card)
			// ease the rest of the way there - same snap-on-release pattern
			// as live-apps-carousel.js's goTo(Math.round(...)).
			goTo( Math.round( dragCurrentPos ) );
		}

		function onDragEnd( e ) {
			if ( ! isDragging || e.pointerId !== dragPointerId ) {
				return;
			}
			endDrag();
		}

		// A drag gesture ends with a native "click" firing on whatever's
		// under the pointer on release - without this guard, releasing a
		// drag over a card's link would immediately navigate away.
		cards.forEach( function ( card ) {
			card.addEventListener( 'click', function ( e ) {
				if ( dragMoved ) {
					e.preventDefault();
					dragMoved = false;
				}
			} );
		} );

		// pointermove/pointerup/pointercancel are only attached to `window`
		// while a drag is actually in progress (see onDragStart/endDrag) -
		// not here on `stage` - so they never interfere with a plain click.
		stage.style.touchAction = 'pan-y';
		stage.addEventListener( 'pointerdown', onDragStart );

		// If a resize crosses the mobile breakpoint mid-session (e.g.
		// rotating a tablet, or resizing a desktop window down), bail out of
		// any in-progress drag and hand the box back to the CSS scroll-snap
		// strip; crossing back the other way re-establishes the fan.
		window.addEventListener( 'resize', function () {
			if ( isMobileTier() && isDragging ) {
				endDrag();
			}
			render();
		} );

		render();
	}

	function qeemaInitAllAppStoreProof() {
		document.querySelectorAll( '.qeema-app-store-proof' ).forEach( function ( root ) {
			if ( 'true' === root.dataset.qeemaInit ) {
				return;
			}
			root.dataset.qeemaInit = 'true';
			qeemaInitAppStoreProof( root );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', qeemaInitAllAppStoreProof );
	if ( window.elementorFrontend ) {
		jQuery( window ).on( 'elementor/frontend/init', qeemaInitAllAppStoreProof );
	}
})();
