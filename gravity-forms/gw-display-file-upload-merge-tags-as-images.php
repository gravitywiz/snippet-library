<?php
/**
 * Gravity Wiz // Gravity Forms // Display File Upload Merge Tags as Images
 * https://gravitywiz.com/
 *
 * This snippet simplifies the logic in both [Preview Submission][1] and the [Multi-file Merge Tags][2]
 * snippet to create a very basic solution for outputting images for your File Upload fields.
 *
 * Works both with the field-specific merge tags as well as within the context of the {all_fields}
 * merge tag.
 *
 * [1]: https://gravitywiz.com/documentation/gravity-forms-preview-submission/
 * [2]: https://gravitywiz.com/customizing-multi-file-merge-tag/
 */
add_filter( 'gform_merge_tag_filter', function ( $value, $merge_tag, $modifier, $field, $raw_value, $format ) {
	// Update "123" to your form ID.
	if ( $field->formId == 123 && $field->type == 'fileupload' ) {
		$values = $raw_value;
		if ( $field->multipleFiles ) {
			$values = json_decode( $values );
		}
		if ( ! is_array( $values ) ) {
			$values = array( $values );
		}
		$output = array();
		foreach ( $values as $_value ) {
			$output[] = '<img src="' . $_value . '" style="max-width:100%;">';
		}
		$value = implode( '', $output );
	}
	return $value;
}, 10, 6 );
