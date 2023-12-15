<?php
/**
 * This snippet has been superseded by:
 * https://github.com/gravitywiz/snippet-library/blob/master/gravity-forms/gw-hide-all-non-visible-fields-via-modifier.php
 */
/**
 * Gravity Perks // Preview Submission // Hide Hidden-visibility Fields from Preview (when :nohidden modifier is used)
 * https://gravitywiz.com/documentation/gravity-forms-preview-submission/
 *
 * By default the {all_fields} merge tag will display Hidden fields. With this snippet you can hide
 * Hiidden field by add addng the `:nohidden` modifer like so: `{all_fields:nohidden}`.
 *
 * Plugin Name:  GP Preview Submission — Hide Hidden-visibility Fields from Preview
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-preview-submission/
 * Description:  Hide Hidden fields from preview when using the {all_fields} merge tags.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
add_filter( 'gform_merge_tag_filter', function( $value, $merge_tag, $modifier, $field ) {

	if ( $merge_tag == 'all_fields' ) {
		$modifiers = explode( ',', $modifier );
		if ( in_array( 'nohidden', $modifiers ) && $field->visibility == 'hidden' ) {
			return false;
		}
	}

	return $value;
}, 10, 4 );
