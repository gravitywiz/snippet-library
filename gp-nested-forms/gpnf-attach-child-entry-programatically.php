<?php
/**
 * Gravity Perks // Nested Forms // Attach Child Entry to Parent Programmatically
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
// Update "124" to your child form ID.
add_filter( 'gform_entry_post_save_124', function( $entry ) {
	$entry = array_replace( array(
		// The ID of the parent entry.
		GPNF_Entry::ENTRY_PARENT_KEY            => 234,
		// The ID of the parent form.
		GPNF_Entry::ENTRY_PARENT_FORM_KEY       => 123,
		// The ID of the Nested Form field on the parent form.
		GPNF_Entry::ENTRY_NESTED_FORM_FIELD_KEY => 4,
	), $entry );
	GFAPI::update_entry( $entry );
	return $entry;
} );
