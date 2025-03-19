<?php
/**
 * Gravity Perks // Terms of Service // Add a `:terms_only` modifier.
 * https://gravitywiz.com/documentation/gravity-forms-terms-of-service/
 *
 * Add a `:terms_only` modifier to the {all_fields} merge tag to only display the terms.
 * 
 * Instruction Video: https://www.loom.com/share/d69c48bea2d1429ab019310d2bc6c1e6
 */
add_filter( 'gform_merge_tag_filter', function ( $value, $merge_tag, $options, $field ) {
	if ( $field['type'] != 'tos' ) {
		return $value;
	}

	$options = explode( ',', $options );
	if ( ! in_array( 'terms_only', $options ) ) {
		return $value;
	}

	if ( $merge_tag != 'all_fields' ) {
		$value = '<ul><li>' . $value . '</li></ul>';
	}

	$value = wpautop( $field->get_terms( GFAPI::get_form( $field->formId ) ) );

	return $value;
}, 11, 4 );
