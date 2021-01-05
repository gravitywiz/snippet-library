<?php
/**
 * Gravity Perks // GP Nested Forms // Pass Nested Form Entries to ACF Repeater Field when using GF APC Add-on
 * http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
// Update "123" to the ID of the Parent form.
add_action( 'gform_after_submission_123', 'gw_child_entries_to_repeater', 10, 2 );
function gw_child_entries_to_repeater( $entry, $form ) {

	// Update "4" to the ID of the Nested Form field.
	$nested_form_field_id = 4;
	// The field map contains "field_name" => "child_entry_field_id" pairs. The field name is the name of the fields in
	// the ACF Repeater field. The child entry field ID is the field ID from the child form.
	$field_map = array(
		'custom_field_one' => 5,
		'custom_field_two' => 6,
	);
	// Update test_repeat to the field name of the ACF repeater field.
	$acf_repeater_field_name = 'test_repeat';
	/* STOP! You don't need to edit below this line. */

	$parent_entry  = new GPNF_Entry( $entry );
	$child_entries = $parent_entry->get_child_entries( $nested_form_field_id );
	$repeat_value  = array();
	$created_posts = gform_get_meta( $entry['id'], 'gravityformsadvancedpostcreation_post_id' );
	foreach ( $created_posts as $post ) {
		$post_id = $post['post_id'];
		foreach ( $child_entries as $child_entry ) {
			$value = array();
			foreach ( $field_map as $acf_field_name => $child_entry_field_id ) {
				$value[ $acf_field_name ] = rgar( $child_entry, $child_entry_field_id );
			}
			array_push( $repeat_value, $value );
		}
		update_field( $acf_repeater_field_name, $repeat_value, $post_id );
	}

}
