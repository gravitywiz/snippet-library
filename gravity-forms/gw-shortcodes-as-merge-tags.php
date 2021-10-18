<?php
/**
 * Gravity Wiz // Gravity Forms // Shortcodes as Merge Tags for Gravity Forms Conditional Shortcode
 * https://gravitywiz.com/
 *
 * Requires Gravity Forms 2.4.17.2+
 *
 * Usage:
 *
 * 1. First convert your shortcode into a merge tag.
 *
 * Shortcode: [gravityforms action="remaining" id="123" input_id="4.3" limit="100"]
 * Merge Tag: {shortcode:gravityforms&action=remaining&id=123&input_id=4.3&limit=100}
 *
 * 2. Use your merge tag in the GF conditional shortcode `merge_tag` parameter.
 *
 * [gravityforms action="conditional" merge_tag="{shortcode:gravityforms&action=remaining&id=123&input_id=4.3&limit=100}" condition="less_than" value="50"]
 *     Hurry! Only [gravityforms action="remaining" id="661" input_id="1.3" limit="100"] tickets left!
 * [/gravityforms]
 */
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
