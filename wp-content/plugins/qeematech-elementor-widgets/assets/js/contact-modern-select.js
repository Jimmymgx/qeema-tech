/**
 * Replaces the plain OS-native open-dropdown look (which CSS can't restyle
 * for a real <select>) with a custom, fully-styled listbox — the real
 * <select> stays in the DOM as the actual form control (so submission and
 * any other script that sets its value, e.g. the country IP-detect script,
 * keeps working), just visually hidden and pulled out of the tab order; the
 * custom trigger + panel becomes the sole interactive/keyboard control.
 */
(function () {
	'use strict';

	function buildCustomSelect( select ) {
		var wrapper = select.closest( '.elementor-select-wrapper' );
		if ( ! wrapper || wrapper.qeemaEnhanced ) {
			return;
		}
		wrapper.qeemaEnhanced = true;
		wrapper.classList.add( 'qeema-select' );
		select.setAttribute( 'tabindex', '-1' );
		select.setAttribute( 'aria-hidden', 'true' );

		var trigger = document.createElement( 'div' );
		trigger.className = 'qeema-select__trigger';
		trigger.setAttribute( 'tabindex', '0' );
		trigger.setAttribute( 'role', 'button' );
		trigger.setAttribute( 'aria-haspopup', 'listbox' );
		trigger.setAttribute( 'aria-expanded', 'false' );

		var panel = document.createElement( 'div' );
		panel.className = 'qeema-select__panel';
		panel.setAttribute( 'role', 'listbox' );
		panel.tabIndex = -1;

		wrapper.appendChild( trigger );
		wrapper.appendChild( panel );

		var highlighted = -1;

		function renderTrigger() {
			var opt = select.options[ select.selectedIndex ];
			trigger.textContent = opt ? opt.text : '';
			trigger.classList.toggle( 'is-placeholder', 0 === select.selectedIndex );
		}

		function renderPanel() {
			panel.innerHTML = '';
			Array.prototype.forEach.call( select.options, function ( opt, i ) {
				var item = document.createElement( 'div' );
				item.className = 'qeema-select__option';
				item.setAttribute( 'role', 'option' );
				item.dataset.index = i;
				item.textContent = opt.text;
				if ( i === select.selectedIndex ) {
					item.classList.add( 'is-selected' );
					item.setAttribute( 'aria-selected', 'true' );
				}
				panel.appendChild( item );
			} );
		}

		function open() {
			if ( wrapper.classList.contains( 'is-open' ) ) {
				return;
			}
			renderPanel();
			wrapper.classList.add( 'is-open' );
			trigger.setAttribute( 'aria-expanded', 'true' );
			highlighted = select.selectedIndex;
			setHighlighted( highlighted );
			var selectedEl = panel.querySelector( '.is-selected' );
			if ( selectedEl ) {
				selectedEl.scrollIntoView( { block: 'nearest' } );
			}
		}

		function close( refocus ) {
			if ( ! wrapper.classList.contains( 'is-open' ) ) {
				return;
			}
			wrapper.classList.remove( 'is-open' );
			trigger.setAttribute( 'aria-expanded', 'false' );
			if ( refocus ) {
				trigger.focus();
			}
		}

		function setHighlighted( index ) {
			var items = panel.querySelectorAll( '.qeema-select__option' );
			items.forEach( function ( el ) { el.classList.remove( 'is-highlighted' ); } );
			if ( items[ index ] ) {
				items[ index ].classList.add( 'is-highlighted' );
				items[ index ].scrollIntoView( { block: 'nearest' } );
			}
			highlighted = index;
		}

		function selectIndex( index ) {
			if ( index < 0 || index >= select.options.length ) {
				return;
			}
			select.selectedIndex = index;
			select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		}

		trigger.addEventListener( 'click', function () {
			wrapper.classList.contains( 'is-open' ) ? close( false ) : open();
		} );

		trigger.addEventListener( 'keydown', function ( e ) {
			if ( 'Enter' === e.key || ' ' === e.key || 'ArrowDown' === e.key || 'ArrowUp' === e.key ) {
				e.preventDefault();
				if ( ! wrapper.classList.contains( 'is-open' ) ) {
					open();
				} else {
					selectIndex( highlighted );
					close( true );
				}
			} else if ( 'Escape' === e.key ) {
				close( true );
			}
		} );

		panel.addEventListener( 'click', function ( e ) {
			var item = e.target.closest( '.qeema-select__option' );
			if ( ! item ) {
				return;
			}
			selectIndex( parseInt( item.dataset.index, 10 ) );
			close( true );
		} );

		panel.addEventListener( 'mousemove', function ( e ) {
			var item = e.target.closest( '.qeema-select__option' );
			if ( item ) {
				setHighlighted( parseInt( item.dataset.index, 10 ) );
			}
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( ! wrapper.classList.contains( 'is-open' ) ) {
				return;
			}
			if ( 'ArrowDown' === e.key ) {
				e.preventDefault();
				setHighlighted( Math.min( highlighted + 1, select.options.length - 1 ) );
			} else if ( 'ArrowUp' === e.key ) {
				e.preventDefault();
				setHighlighted( Math.max( highlighted - 1, 0 ) );
			} else if ( 'Enter' === e.key || ' ' === e.key ) {
				e.preventDefault();
				selectIndex( highlighted );
				close( true );
			} else if ( 'Escape' === e.key ) {
				close( true );
			} else if ( 'Home' === e.key ) {
				e.preventDefault();
				setHighlighted( 0 );
			} else if ( 'End' === e.key ) {
				e.preventDefault();
				setHighlighted( select.options.length - 1 );
			} else if ( 1 === e.key.length ) {
				var letter = e.key.toLowerCase();
				for ( var i = 1; i <= select.options.length; i++ ) {
					var idx = ( highlighted + i ) % select.options.length;
					if ( select.options[ idx ].text.trim().toLowerCase().indexOf( letter ) === 0
						|| select.options[ idx ].text.replace( /^[^\p{L}\p{N}]+/u, '' ).toLowerCase().indexOf( letter ) === 0 ) {
						setHighlighted( idx );
						break;
					}
				}
			}
		} );

		document.addEventListener( 'click', function ( e ) {
			if ( ! wrapper.contains( e.target ) ) {
				close( false );
			}
		} );

		// Stay in sync if another script (e.g. the country IP-detector) sets
		// the underlying select's value directly.
		select.addEventListener( 'change', renderTrigger );

		renderTrigger();
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '.qeema-contact-form-col .elementor-select-wrapper select' ).forEach( buildCustomSelect );
	} );
})();
