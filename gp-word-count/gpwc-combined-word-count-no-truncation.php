<?php
/**
 * Gravity Perks // Word Count // Combined Word Count: Validate Rather than Truncate
 * https://gravitywiz.com/documentation/gravity-forms-word-count/
 *
 * This PHP snippet is designed to work in conjunction with the "Combined Word Count" snippet here:
 * https://github.com/gravitywiz/snippet-library/blob/master/gp-word-count/gpwc-combined-word-count.js
 *
 * By default, the combined words will be truncated to the max word count. This snippet will disable truncation and
 * validate the combined word count instead. If the combined word count exceeds the max word count, the source fields
 * which are combined will be marked as invalid and a validation message will be displayed.
 *
 * Instructions
 *
 * 1. Install and activate the JS snippet that handles combining the word counts.
 *    https://github.com/gravitywiz/snippet-library/blob/master/gp-word-count/gpwc-combined-word-count.js
 *
 * 2. Install and activate this snippet.
 *
 * 3. Configure this snippet for your form and fields based on the inline instructions.
 */
call_user_func( function() {

	// Update "123" to your form ID.
	$form_id = 123;

	// Update "4" and "5" to the IDs of the fields that are combined to create the combined word count.
	$source_field_ids = array( 4, 5 );

	// Update "6" to the ID of the field that will capture the combined words and generate the combined word count.
	$combined_field_id = 6;

	add_filter( 'gpwc_script_args', function( $args, $field ) use ( $form_id, $combined_field_id ) {
		if ( $field->formId == $form_id && $field->id == $combined_field_id ) {
			$args['truncate'] = false;
		}
		return $args;
	}, 10, 2 );

	add_filter( "gform_validation_{$form_id}", function( $result ) use ( $source_field_ids, $combined_field_id ) {
		if ( $result['is_valid'] ) {
			return $result;
		}
		$combined_field_id = GFAPI::get_field( $result['form'], $combined_field_id );
		if ( ! $combined_field_id->failed_validation ) {
			return $result;
		}
		foreach ( $source_field_ids as $source_field_id ) {
			$source_field                     = GFAPI::get_field( $result['form'], $source_field_id );
			$source_field->failed_validation  = true;
			$source_field->validation_message = sprintf( 'The maximum combined word count of %d has been exceeded.', $combined_field_id->gwwordcount_max_word_count );
			unset( $source_field );
		}
		return $result;
	} );

} );
