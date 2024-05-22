<?php
/**
 * Gravity Wiz // Gravity Forms // Shortcodes as Merge Tags
 * https://gravitywiz.com/
 *
 * Convert shortcodes into merge tags on the fly! See usage instructions below.
 *
 * Requires Gravity Forms 2.4.17.2+.
 *
 * Instructions:
 *
 * 1. First convert your shortcode into a merge tag.
 *
 *     Shortcode: `[gravityforms action="remaining" id="123" input_id="4.3" limit="100"]` \
 *     Merge Tag: `{shortcode:gravityforms&action=remaining&id=123&input_id=4.3&limit=100}`
 *
 * 2. Use your merge tag in as the default value in a field - or - in the GF conditional shortcode `merge_tag` parameter.
 *
 *     ```
 *     [gravityforms action="conditional" merge_tag="{shortcode:gravityforms&action=remaining&id=123&input_id=4.3&limit=100}" condition="less_than" value="50"]
 *         Hurry! Only [gravityforms action="remaining" id="661" input_id="1.3" limit="100"] tickets left!
 *     [/gravityforms]
 *     ```
 */
add_filter( 'gform_replace_merge_tags', function( $text ) {

	// Update to a list of shortcodes that should be allowed as merge tags.
	$white_listed_shortcodes = array( 'gravityforms', 'gpls' );

	if ( strpos( $text, '{shortcode:' ) === false ) {
		return $text;
	}

	preg_match( '/{shortcode:(.+?)}/', htmlspecialchars_decode( $text ), $matches );
	if ( empty( $matches ) ) {
		return $text;
	}

	parse_str( $matches[1], $atts );
	$shortcode = array( array_key_first( $atts ) );
	if ( ! in_array( $shortcode[0], $white_listed_shortcodes ) ) {
		return $text;
	}

	array_shift( $atts );

	foreach ( $atts as $prop => $value ) {
		$shortcode[] = sprintf( '%s="%s"', $prop, $value );
	}

	$shortcode = sprintf( '[%s]', implode( ' ', $shortcode ) );
	$text      = str_replace( $matches[0], do_shortcode( $shortcode ), $text );

	return $text;
} );

// @todo: Refactor to remove duplicated code.
add_filter( 'shortcode_atts_gravityforms_conditional', function( $out, $pairs, $atts ) {

	if ( isset( $out['merge_tag'] ) && strpos( $out['merge_tag'], '{shortcode:' ) !== false ) {
		preg_match( '/{shortcode:(.+?)}/', htmlspecialchars_decode( $out['merge_tag'] ), $matches );
		parse_str( $matches[1], $_atts );
		$shortcode = array( array_key_first( $_atts ) );
		array_shift( $_atts );
		foreach ( $_atts as $prop => $value ) {
			$shortcode[] = sprintf( '%s="%s"', $prop, $value );
		}
		$shortcode        = sprintf( '[%s]', implode( ' ', $shortcode ) );
		$out['merge_tag'] = do_shortcode( $shortcode );
	}

	return $out;
}, 10, 3 );
