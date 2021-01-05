<?php
/**
 * Gravity Perks // GP Nested Forms // Pass Nested Form Entries to ACF Repeater Field when using GF APC
 * http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */


// Update 123 to the ID of the Parent form
add_action( 'gform_after_submission_123', 'nested_to_repeater', 10, 2 );
function nested_to_repeater( $entry, $form ) {

	$parent_entry = new GPNF_Entry( $entry );

	// Update 10 to the ID of the Nested Form Field
	$nested_form_field_id = '10';

	$child_entries = $parent_entry->get_child_entries( $nested_form_field_id );
	$repeat_value  = array();
	$created_posts = gform_get_meta( $entry['id'], 'gravityformsadvancedpostcreation_post_id' );
	foreach ( $created_posts as $post ) {
		$post_id = $post['post_id'];
		foreach ( $child_entries as $child_entry ) {
			// Array contains field key => value pairs for the fields. The Key is the name of the fields in the within the Repeater field
			// Update the child_entry index with the Ids of the fields in the child from and map them with the respective ACF field name.
			$value = array(
				'custom_field_one' => $child_entry['12'],
				'custom_field_two' => $child_entry['10'],
			);
			array_push( $repeat_value, $value );
		}
		// Update test_repeat to the field name of the ACF repeater field.
		update_field( 'test_repeat', $repeat_value, $post_id );
	}
}
