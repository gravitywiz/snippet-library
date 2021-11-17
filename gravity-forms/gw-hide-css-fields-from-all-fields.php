<?php
/**
 * This snippet has been superseded by:
 * https://github.com/gravitywiz/snippet-library/blob/master/gravity-forms/gw-hide-all-non-visible-fields-via-modifier.php
 */
/**
 * Gravity Wiz // Gravity Forms // Hide Fields Hidden via CSS Classes from {all_fields} Merge Tag
 * https://gravitywiz.com
 *
 * By default the {all_fields:nohidden} Merge Tag will only hide Hidden fields. This snippet also hides fields
 * that have been hidden with CSS classes gf_hidden or gf_invisible when using {all_fields:nohidden}.
 *
 * Plugin Name:  Gravity Forms — Hide Fields Hidden via CSS Classes from {all_fields} Merge Tag
 * Plugin URI:   https://gravitywiz.com
 * Description:  This automatically hides fields that have been hidden with CSS classes gf_hidden or gf_invisible when using {all_fields:nohidden}.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gform_merge_tag_filter', function( $value, $merge_tag, $modifier, $field ) {

	if ( $merge_tag == 'all_fields' && $modifier == 'nohidden' ) {
		$css_classes = explode( ' ', $field->cssClass );
		$matching_classes = array_intersect( array( 'gf_hidden', 'gf_invisible' ), $css_classes );
		if( ! empty( $matching_classes ) ) {
			return false;
		}
	}

	return $value;
}, 10, 4 );
