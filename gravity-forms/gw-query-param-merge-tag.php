<?php
/**
 * Gravity Wiz // Gravity Forms // Query Param Merge Tag
 *
 * Adds {query_param:PARAM} merge tags for pulling values from query/GET params.
 *
 * Usage:
 *  Example URL: https://domain.example/sample-page/?example=13
 *  Example Merge Tag: {query_param:example}
 *
 * Plugin Name: Gravity Forms Query Param Merge Tag
 * Plugin URI: https://gravitywiz.com/
 * Description: Adds {query_param:PARAM} merge tags for pulling values from query/GET params.
 * Author: Gravity Wiz
 * Version: 0.1
 * Author URI: https://gravitywiz.com
 */
add_filter( 'gform_replace_merge_tags', function( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
	preg_match_all( '/\{query_param:(.*?)\}/', $text, $dyn_pop_matches, PREG_SET_ORDER );

	if ( ! empty( $dyn_pop_matches ) ) {
		foreach ( $dyn_pop_matches as $match ) {
			$full_tag = $match[0];
			$modifier = $match[1];

			$value = sanitize_text_field( esc_sql( gw_get_get_query_param( $modifier ) ) );

			$text = str_replace( $full_tag, $url_encode ? urlencode( $value ) : $value, $text );
		}
	}

	return $text;
}, 10, 7 );

if ( ! function_exists( 'gw_get_query_param' ) ) :
	/**
	 * Get a query parameter value from $_GET when the key may use
	 * array syntax like filters[53][foo], possibly URL-encoded.
	 *
	 * Examples:
	 *   gw_get_get_query_param( 'filters[53]' );
	 *   gw_get_get_query_param( 'filters%5B53%5D' );
	 *   gw_get_get_query_param( 'filters[53][foo]' );
	 */
	function gw_get_get_query_param( $key ) {

		// Normalize the incoming key using WP helpers.
		$key = wp_unslash( $key );      // In case it came from a request.
		$key = urldecode( $key );       // Decode URL-encoded names like filters%5B53%5D.
		$key = trim( $key, "\"'" );     // Strip surrounding quotes.

		// Work with an unslashed copy of $_GET.
		$get = wp_unslash( $_GET );

		// No array syntax – return directly.
		if ( strpos( $key, '[' ) === false ) {
			return isset( $get[ $key ] ) ? $get[ $key ] : null;
		}

		// Convert "filters[53][foo]" → [ 'filters', '53', 'foo' ].
		$parts = preg_split( '/\[|\]/', $key, -1, PREG_SPLIT_NO_EMPTY );

		$value = $get;

		foreach ( $parts as $part ) {
			if ( ! is_array( $value ) || ! array_key_exists( $part, $value ) ) {
				return null;
			}
			$value = $value[ $part ];
		}

		return $value;
	}
endif;
