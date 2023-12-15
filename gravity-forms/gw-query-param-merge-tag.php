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
 * Author URI: http://gravitywiz.com
 */
add_filter( 'gform_replace_merge_tags', function( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
	preg_match_all( '/\{query_param:(.*?)\}/', $text, $dyn_pop_matches, PREG_SET_ORDER );

	if ( ! empty( $dyn_pop_matches ) ) {
		foreach ( $dyn_pop_matches as $match ) {
			$full_tag = $match[0];
			$modifier = $match[1];

			$value = sanitize_text_field( esc_sql( rgget( $modifier ) ) );

			$text = str_replace( $full_tag, $url_encode ? urlencode( $value ) : $value, $text );
		}
	}

	return $text;
}, 10, 7 );
