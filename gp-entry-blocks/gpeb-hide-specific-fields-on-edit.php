<?php
/**
 * Gravity Perks // Entry Blocks // Hide Specific Fields on Edit
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 */
// Update "123" to your form ID.
add_filter( 'gform_pre_render_123', function ( $form ) {
	// Update "4", "5", and "6" to the IDs of the fields you'd like to hide.
	$fields_to_hide = array( 4, 5, 6 );
	if ( ! class_exists( 'WP_Block_Supports' ) || rgar( WP_Block_Supports::$block_to_render, 'blockName' ) !== 'gp-entry-blocks/edit-form' ) {
		return $form;
	}
	$filtered_fields = array();
	foreach ( $form['fields'] as &$field ) {
		if ( ! in_array( $field->id, $fields_to_hide ) ) {
			$filtered_fields[] = $field;
		}
	}
	$form['fields'] = $filtered_fields;
	return $form;
} );
