<?php
/**
 * Gravity Perks // Populate Anything // Support for Consent Field Description
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gform_entry_field_value', function( $display_value, $field, $entry, $form ) {
	if ( $field->type === 'consent' ) {
		$display_value = gp_populate_anything()->live_merge_tags->replace_live_merge_tags_static( $display_value, $form, $entry );
	}
	return $display_value;
}, 10, 4 );
