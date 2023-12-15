<?php
/**
 * Gravity Perks // Populate Anything // Force Rehydration When Editing Entry
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gform_get_field_value', function( $value, $entry, $field ) {
	static $_gppa_forcing_hydration;
	if ( GFForms::get_page() === 'entry_detail_edit' && ! $_gppa_forcing_hydration && gp_populate_anything()->is_field_dynamically_populated( $field ) ) {
		$_gppa_forcing_hydration = true;
		$hydrated_field          = gp_populate_anything()->hydrate_field( $field, GFAPI::get_form( $field->formId ), $entry, $entry );
		$value                   = $hydrated_field['field_value'];
		$_gppa_forcing_hydration = false;
	}
	return $value;
}, 10, 3 );
