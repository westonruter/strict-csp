<?php
/**
 * Strict CSP Plugin for WordPress
 *
 * @package   StrictCSP
 * @author    Weston Ruter, Google
 * @license   GPL-2.0-or-later
 * @copyright 2023 Google Inc.
 *
 * @wordpress-plugin
 * Plugin Name: Strict CSP
 * Plugin URI: https://github.com/westonruter/strict-csp
 * Description: ...
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Version: 0.1.0
 * Author: Weston Ruter
 * Author URI: https://weston.ruter.net/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Update URI: https://github.com/westonruter/strict-csp
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
const VERSION = '0.1.0';

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

add_filter( 'embed_oembed_html', __NAMESPACE__ . '\filter_oembed_html' );

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

// Send the header on the frontend and in the login screen.
add_filter(
	'wp_headers',
	static function ( $headers ) {
		$headers['Content-Security-Policy'] = get_csp_header_value();
		return $headers;
	}
);
add_action( 'login_init', __NAMESPACE__ . '\send_csp_header' );

// Add the nonce attribute to scripts.
add_filter(
	'wp_script_attributes',
	__NAMESPACE__ . '\add_nonce_to_script_attributes'
);
add_filter(
	'wp_inline_script_attributes',
	__NAMESPACE__ . '\add_nonce_to_script_attributes'
);
