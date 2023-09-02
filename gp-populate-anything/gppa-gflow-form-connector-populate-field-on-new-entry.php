<?php
/**
 * Gravity Perks // Populate Anything // Gravity Flow Form Connector: Populate Field on New Entry
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Scenario: Form B creates a new entry in Form A via the Gravity Flow Form Connector. This snippet will populate a field
 * in Form B based on its Populate Anything configuration.
 */
add_filter( 'gravityflowformconnector_new_entry_form', function( $form ) {

	// Update "123" to the ID of the form that triggers a new entry via the Gravity Flow Form Connector.
	$trigger_form_id = 123;

	// Update "4" to the ID of the field that should be populated via Populate Anything when the new entry is created.
	$target_field_id = 4;

	if ( is_callable( 'gp_populate_anything' ) && $form['id'] == $trigger_form_id ) {
		add_filter( 'gform_post_add_entry', function( $entry, $form ) use ( $target_field_id ) {
			$hydrated_field            = gp_populate_anything()->populate_field( GFAPI::get_field( $form, $target_field_id ), $form, $entry, $entry );
			$entry[ $target_field_id ] = $hydrated_field['field_value'];
			GFAPI::update_entry_field( $entry['id'], $target_field_id, $entry[ $target_field_id ] );
			return $entry;
		}, 10, 2 );
	}

	return $form;
} );
