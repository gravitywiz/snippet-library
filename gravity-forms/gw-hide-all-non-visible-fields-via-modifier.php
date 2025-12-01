<?php
/**
 * Gravity Wiz // Gravity Forms // Hide All Non-visible Fields via Modifier
 * https://gravitywiz.com/how-to-not-show-hidden-fields-using-all-fields-merge-tag/
 *
 * By default, the {all_fields:nohidden} merge tag will only hide Hidden fields. This snippet will also hide fields that
 * have a Visibility of "Hidden" and fields that are hidden via the "gf_hidden" or "gf_invisible" CSS classes.
 *
 * Pairs well with [GP Preview Submission](https://gravitywiz.com/documentation/gravity-forms-preview-submssion/)!
 *
 * Plugin Name:  Gravity Forms — Hide All Non-visible Fields via Modifier
 * Plugin URI:   https://gravitywiz.com
 * Description:  This automatically hides all non-visible fields when the :nohidden modifier is used with the {all_fields} merge tag.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gform_merge_tag_filter', function( $value, $merge_tag, $modifier, $field ) {

	if ( $merge_tag === 'all_fields' ) {

		$modifiers = array_map( 'trim', explode( ',', $modifier ) );
		if ( ! in_array( 'nohidden', $modifiers, true ) ) {
			return $value;
		}

		// Hide hidden-visibility fields.
		if ( $field->visibility === 'hidden' ) {
			return false;
		}

		// Hide fields hidden via CSS classes.
		$css_classes      = explode( ' ', $field->cssClass );
		$matching_classes = array_intersect( array( 'gf_hidden', 'gf_invisible' ), $css_classes );
		if ( ! empty( $matching_classes ) ) {
			return false;
		}
	}

	return $value;
}, 10, 4 );
