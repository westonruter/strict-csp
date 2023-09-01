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
 * Gets CSP nonce.
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

/**
 * Adds nonce attribute to script attributes.
 *
 * @param string[] $attributes Script attributes.
 * @return string[] Amended attributes.
 */
function add_nonce_to_script_attributes( array $attributes ): array {
	$attributes['nonce'] = get_nonce();
	return $attributes;
}

/**
 * Gets Strict CSP header value.
 *
 * @return string Header value.
 */
function get_csp_header_value(): string {

	$script_src_sources = array(
		sprintf( "'nonce-%s'", get_nonce() ),
		"'unsafe-inline'",
		"'strict-dynamic'",
		'https:',
		'http:'
	);

	// Needed for templating with Underscores/Lodash.
	if ( is_admin() ) {
		$script_src_sources[] = "'unsafe-eval'";
	}

	return join(
		'; ',
		array(
			"object-src 'none'",
			sprintf( 'script-src %s', join( ' ', $script_src_sources ) ),
			"base-uri 'none'" // Note: jQuery can violate this in jQuery.parseHTML() due to <https://github.com/jquery/jquery/issues/2965>.
		)
	);
}

/**
 * Sends Strict CSP header.
 */
function send_csp_header() {
	header( sprintf( 'Content-Security-Policy: %s', get_csp_header_value() ) );
}

// Send the header on the frontend and in the admin.
add_filter(
	'wp_headers',
	static function ( $headers ) {
		$headers['Content-Security-Policy'] = get_csp_header_value();
		return $headers;
	}
);
add_action( 'login_init', __NAMESPACE__ . '\send_csp_header' );

add_action(
	'admin_init',
	static function () {
		global $pagenow;

		/*
		 * Not compatible with the block editor since the introduction of iframing. See:
		 * - https://github.com/WordPress/gutenberg/blob/e2941e9741bb8e21f0d7965d6bf70d43113bade2/packages/block-editor/src/components/iframe/index.js#L198
		 * - https://github.com/WordPress/gutenberg/blob/e2941e9741bb8e21f0d7965d6bf70d43113bade2/packages/block-editor/src/components/iframe/index.js#L204
		 */
		if ( 'post-new.php' === $pagenow || 'post.php' === $pagenow ) {
			return;
		}

		send_csp_header();
	}
);

// Add the nonce attribute to scripts.
add_filter(
	'wp_script_attributes',
	__NAMESPACE__ . '\add_nonce_to_script_attributes'
);
add_filter(
	'wp_inline_script_attributes',
	__NAMESPACE__ . '\add_nonce_to_script_attributes'
);
