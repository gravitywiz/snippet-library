<?php
/**
 * Gravity Wizards // Gravity Forms // Filename Modifier for File Upload
 *
 * This snippet is an example of how to add filename modifiers to a Gravity Forms file upload field.
 *
 * Instructions:
 *
 * 1. Add to snippet to site. See https://gravitywiz.com/documentation/how-do-i-install-a-snippet/.
 */
add_filter( 'gform_merge_tag_filter', function ( $value, $merge_tag, $modifier, $field, $raw_value, $format ) {
	if ( $merge_tag != 'all_fields' && $field->type == 'fileupload' && ! empty( $raw_value ) && $modifier == 'filename' ) {
		if ( ! $field->multipleFiles ) {
			$value = basename( $raw_value );
		} else {
			$file_list = [];
			foreach ( json_decode( $raw_value ) as $filepath ) {
				$file_list[] = basename( $filepath );
			}
			$value = implode( '<br />', $file_list );
		}
	}
	return $value;
}, 10, 6 );

// For GP Media Library
add_filter( 'gpml_image_merge_tag_skip_modifiers', function( $skip_modifiers, $modifiers, $input_id, $image_ids ) {
	return [ 'filename' ];
}, 10, 4 );
