<?php
/**
 * Strict CSP Plugin for WordPress
 *
 * @package StrictCSP
 * @author  Weston Ruter
 * @license GPL-2.0-or-later
 * @link    https://gist.github.com/westonruter/c8b49406391a8d86a5864fb41a523ae9 Originally developed in a Gist when the author was at Google.
 *
 * @wordpress-plugin
 * Plugin Name: Strict CSP
 * Plugin URI: https://github.com/westonruter/strict-csp
 * Description: Enforces a <a href="https://web.dev/articles/strict-csp">Strict Content Security Policy</a> on the frontend and login screen to help mitigate any XSS vulnerabilities; the policy cannot yet be applied to the WP Admin (see <a href="https://core.trac.wordpress.org/ticket/59446">#59446</a>).
 * Requires at least: 6.4
 * Requires PHP: 7.2
 * Version: 0.3.2
 * Author: Weston Ruter
 * Author URI: https://weston.ruter.net/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * GitHub Plugin URI: https://github.com/westonruter/strict-csp
 * Primary Branch: main
 */

namespace StrictCSP;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // @codeCoverageIgnore
}

use WP_HTML_Tag_Processor;

/**
 * Plugin version.
 *
 * @var string
 */
const VERSION = '0.3.2';

/**
 * Gets CSP nonce.
 *
 * @return non-empty-string Nonce.
 */
function get_nonce(): string {
	static $nonce = null;
	if ( null === $nonce ) {
		$nonce = wp_generate_password( 22, false, false );
	}
	/**
	 * Nonce.
	 *
	 * @var non-empty-string $nonce
	 */
	return $nonce;
}

/**
 * Adds nonce attribute to script attributes.
 *
 * @param array<string, string>|mixed $attributes Script attributes.
 * @return array<string, string> Amended attributes.
 */
function add_nonce_to_script_attributes( $attributes ): array {
	if ( ! is_array( $attributes ) ) {
		$attributes = array();
	}
	/**
	 * Because plugins do bad things.
	 *
	 * @var array<string, string> $attributes
	 */
	$attributes['nonce'] = get_nonce();
	return $attributes;
}

/**
 * Adds nonce attribute to scripts in embeds.
 *
 * @param string|mixed $html Embed markup.
 * @return string Embed markup.
 */
function filter_oembed_html( $html ): string {
	if ( ! is_string( $html ) ) {
		$html = '';
	}
	$processor = new WP_HTML_Tag_Processor( $html );
	while ( $processor->next_tag( array( 'tag_name' => 'SCRIPT' ) ) ) {
		$processor->set_attribute( 'nonce', get_nonce() );
	}
	return $processor->get_updated_html();
}

/**
 * Gets Strict CSP header value.
 *
 * @return non-empty-string Header value.
 */
function get_csp_header_value(): string {

	$script_src_sources = array(
		sprintf( "'nonce-%s'", get_nonce() ),
		"'unsafe-inline'",
		"'strict-dynamic'",
		'https:',
		'http:',
	);

	return implode(
		'; ',
		array(
			"object-src 'none'",
			sprintf( 'script-src %s', join( ' ', $script_src_sources ) ),
			"base-uri 'none'", // Note: jQuery can violate this in jQuery.parseHTML() due to <https://github.com/jquery/jquery/issues/2965>.
		)
	);
}

/**
 * Sends Strict CSP header.
 */
function send_csp_header(): void {
	header( sprintf( 'Content-Security-Policy: %s', get_csp_header_value() ) );
}

/**
 * Sends the header on the frontend and in the login screen.
 *
 * @param array<string, string>|mixed $headers Headers.
 * @return array<string, string> Headers.
 */
function filter_wp_headers( $headers ): array {
	if ( ! is_array( $headers ) ) {
		$headers = array();
	}
	/**
	 * Because plugins do bad things.
	 *
	 * @var array<string, string> $headers
	 */
	$headers['Content-Security-Policy'] = get_csp_header_value();
	return $headers;
}

if ( ! is_admin() ) {
	add_filter( 'wp_headers', __NAMESPACE__ . '\filter_wp_headers' );

	// Add the nonce attribute to scripts.
	add_filter(
		'wp_script_attributes',
		__NAMESPACE__ . '\add_nonce_to_script_attributes'
	);
	add_filter(
		'wp_inline_script_attributes',
		__NAMESPACE__ . '\add_nonce_to_script_attributes'
	);

	add_filter( 'embed_oembed_html', __NAMESPACE__ . '\filter_oembed_html' );
}
add_action( 'login_init', __NAMESPACE__ . '\send_csp_header' );
