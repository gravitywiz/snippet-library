<?php
/**
 * Re-calculate a GPDTC calculated field dynamically every time it's viewed.
 */
add_action( 'wp_loaded', function() {
	$form_id  = 13;  // Change this to the form's ID
	$field_id = 132; // Change this to the Age's field ID
	if ( class_exists( 'GP_Date_Time_Calculator' ) ) {
		$values = array();
		add_filter( sprintf( 'gform_get_input_value_%s', $form_id ), function( $value, $entry, $field, $input_id ) use ( $field_id, &$values ) {
			if ( $field['id'] !== $field_id ) {
				$values[ $field['id'] ] = $value;
				return $value;
			}
			$form = GFAPI::get_form( $entry['form_id'] );
			$lead = $entry + $values;
			return GFCommon::calculate( $field, $form, $lead );
		}, 10, 4 );
	}
} );
