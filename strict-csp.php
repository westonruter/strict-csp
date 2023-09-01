<?php
/**
 * Strict CSP Plugin for WordPress 6.4-alpha.
 *
 * @package   StrictCSP
 * @author    Weston Ruter, Google
 * @license   GPL-2.0-or-later
 * @copyright 2023 Google Inc.
 *
 * @wordpress-plugin
 * Plugin Name: Strict CSP
 * Description: Proof of concept for enabling a <a href="https://csp.withgoogle.com/docs/strict-csp.html">Strict Content Security Policy</a> when the patch from WordPress core Trac <a href="https://core.trac.wordpress.org/ticket/58664">#58664</a> is applied.
 * Plugin URI: https://gist.github.com/westonruter/c8b49406391a8d86a5864fb41a523ae9
 * Version: 0.1.0
 * Author: Weston Ruter
 * Author URI: https://weston.ruter.net/
 * License: GNU General Public License v2 (or later)
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://gist.github.com/westonruter/c8b49406391a8d86a5864fb41a523ae9
 */

namespace StrictCSP;

/**
 * Get CSP nonce.
 *
 * @return string
 */
function get_nonce(): string {
	static $nonce = null;
	if ( null === $nonce ) {
		$nonce = wp_create_nonce( 'csp' );
	}
	return $nonce;
}

add_filter(
	'wp_headers',
	static function ( $headers ) {
		$headers['Content-Security-Policy'] = join(
			'; ',
			array(
				"object-src 'none'",
				sprintf( "script-src 'nonce-%s' 'unsafe-inline' 'strict-dynamic' https: http:", get_nonce() ),
				"base-uri 'none'"
			)
		);
		return $headers;
	}
);

add_filter(
	'wp_script_attributes',
	function ( $attributes ) {
		$attributes['nonce'] = get_nonce();
		return $attributes;
	}
);

add_filter(
	'wp_inline_script_attributes',
	function ( $attributes ) {
		$attributes['nonce'] = get_nonce();
		return $attributes;
	}
);
