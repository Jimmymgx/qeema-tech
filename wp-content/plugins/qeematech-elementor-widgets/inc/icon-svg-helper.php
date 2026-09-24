<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PERF-2: Elementor's bundled Font Awesome webfonts (160KB) were found ~98%
 * unused — only ~30 real icons render anywhere on the site. This returns an
 * inline <svg> for any of those converted icons instead of relying on the
 * icon font, so the font/CSS can be dropped from pages that don't need it.
 *
 * Path data lives in icon-svg-data.php, extracted directly from Elementor's
 * bundled Font Awesome 5.15.3 Free webfont glyph outlines
 * (assets/lib/font-awesome/webfonts/fa-{solid,brands}-*.svg) for
 * pixel-accurate shapes — see that file's header comment for the exact
 * extraction/flip method.
 *
 * @param string $icon_class  A Font Awesome class string, e.g. "fas fa-star",
 *                             "fab fa-whatsapp". Legacy "fa fa-x" / "far fa-x"
 *                             / "fa-solid fa-x" / "fa-brands fa-x" prefixes
 *                             are also accepted.
 * @param string $extra_class Extra class(es) added to the <svg> for existing
 *                             CSS selectors that already size/color icons via
 *                             a class (e.g. "qt-services-icon").
 * @return string|null Inline <svg>...</svg> markup, or null if $icon_class
 *                      isn't one of the converted icons. Callers that accept
 *                      editor-typed icon classes (Elementor TEXT controls)
 *                      MUST fall back to rendering the original
 *                      <i class="..."></i> when this returns null, since an
 *                      editor can type any Font Awesome class not in this
 *                      converted set.
 */
function qeema_fa_svg( $icon_class, $extra_class = '' ) {
	static $icons = null;
	if ( null === $icons ) {
		$icons = require __DIR__ . '/icon-svg-data.php';
	}

	$icon_class = trim( (string) $icon_class );
	if ( '' === $icon_class ) {
		return null;
	}

	$parts = preg_split( '/\s+/', $icon_class );
	$name  = '';
	$style = 'fas';
	foreach ( $parts as $part ) {
		if ( in_array( $part, array( 'fab', 'fa-brands' ), true ) ) {
			$style = 'fab';
		} elseif ( in_array( $part, array( 'fas', 'far', 'fa', 'fa-solid', 'fa-regular' ), true ) ) {
			$style = 'fas';
		} elseif ( 0 === strpos( $part, 'fa-' ) ) {
			$name = $part;
		}
	}
	if ( '' === $name ) {
		return null;
	}

	$key = $style . ' ' . $name;
	if ( ! isset( $icons[ $key ] ) ) {
		// Markup sometimes mismatches the fas/fab prefix vs. the icon's
		// actual style; try the other table before giving up.
		$alt_key = ( 'fab' === $style ? 'fas' : 'fab' ) . ' ' . $name;
		if ( ! isset( $icons[ $alt_key ] ) ) {
			return null;
		}
		$key = $alt_key;
	}

	$icon       = $icons[ $key ];
	$class_attr = 'qeema-svg-icon' . ( $extra_class ? ' ' . $extra_class : '' );

	return sprintf(
		'<svg class="%1$s" viewBox="0 0 %2$d 512" aria-hidden="true" focusable="false" role="img"><g transform="translate(0,448) scale(1,-1)"><path d="%3$s"></path></g></svg>',
		esc_attr( $class_attr ),
		(int) $icon['w'],
		esc_attr( $icon['d'] )
	);
}

/**
 * Convenience wrapper for the very common pattern of "output the SVG for a
 * hardcoded, non-editor-controlled icon class, or nothing at all if for some
 * reason it's not in the converted set" (there's no legitimate fallback in
 * that case — the class is baked into this codebase, not editor-typed).
 */
function qeema_fa_svg_e( $icon_class, $extra_class = '' ) {
	echo qeema_fa_svg( $icon_class, $extra_class ); // phpcs:ignore WordPress.Security.EscapeOutput -- qeema_fa_svg() escapes internally.
}
