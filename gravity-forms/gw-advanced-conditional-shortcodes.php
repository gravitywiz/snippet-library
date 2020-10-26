<?php
/**
 * Gravity Wiz // Gravity Forms // Advanced Conditional Shortcodes
 *
 * Allows multiple conditions in a single Gravity Forms shortcode.
 *
 * @version 0.1
 * @author  David Smith <david@gravitywiz.com>
 * @license GPL-2.0+
 * @link    https://gravitywiz.com
 *
 * Plugin Name: Gravity Forms Advanced Conditional Shortcodes
 * Plugin URI: http://gravitywiz.com
 * Description: Allows multiple conditions in a single Gravity Forms shortcode.
 * Author: Gravity Wiz
 * Version: 0.1
 * Author URI: http://gravitywiz.com
 *
 */
add_filter( 'gform_shortcode_conditional', function( $result, $atts, $content ) {

	if( ! isset( $atts['value'] ) || isset( $atts['merge_tag'] ) ) {
		return $result;
	}

	$relation   = strtolower( rgar( $atts, 'relation', 'all' ) ); // or 'any'
	$conditions = array();

	foreach( $atts as $key => $prop ) {

		preg_match( '|value(\d*)$|', $key, $match );
		if( ! empty( $match ) ) {
			list( , $index ) = $match;
			$conditions[] = array(
				'value'    => rgar( $atts, sprintf( 'value%s', $index ) ),
				'operator' => rgar( $atts, sprintf( 'operator%s', $index ) ),
				'compare'  => rgar( $atts, sprintf( 'compare%s', $index ) ),
			);
		}

	}

	$conditional_met = $relation == 'all';

	foreach( $conditions as $condition ) {
		$is_match = GFFormsModel::matches_operation( $condition['value'], $condition['compare'], $condition['operator'] );
		if( $relation == 'any' && $is_match ) {
			$conditional_met = true;
			break;
		} else if( $relation == 'all' && ! $is_match ) {
			$conditional_met = false;
		}
	}

	if( ! $conditional_met ) {
		return '';
	}

	// find and remove any starting/closing <br> tags
	if( rgar( $atts, 'format' ) != 'raw' ) {
		$content = preg_replace( '/^<br(?: *\/)?>|<br(?: *\/)?>$/', '', $content );
	}

	return do_shortcode( $content );
}, 10, 3 );
