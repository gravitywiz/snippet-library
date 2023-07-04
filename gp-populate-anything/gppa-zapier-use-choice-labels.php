<?php
/**
 * Gravity Perks // Populate Anything // Use Choice Labels for Zapier
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Use choice labels instead of choice values for data sent to Zapier when field's choices are populated by Populate Anything.
 */
add_filter( 'gform_zapier_field_value', function( $field_value, $form_id, $field_id, $entry ) {
	$field = GFAPI::get_field( $form_id, $field_id );
	if ( is_callable( 'gp_populate_anything' ) && $field->{'gppa-choices-enabled'} ) {
		$field_value = gp_populate_anything()->get_submitted_choice_label( $field_value, $field, $entry['id'] );
	}
	return $field_value;
}, 10, 4 );
