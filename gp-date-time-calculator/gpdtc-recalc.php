<?php
/**
 * Gravity Perks // Date Time Calculator // Recalculate Field Value on View
 * https://gravitywiz.com/documentation/gravity-forms-date-time-calculator/
 *
 * Experimental Snippet 🧪
 *
 * Recalculate a calculated field's value every time it's viewed.
 *
 * Works great with Date Time Calculator's [:age modifier][1].
 *
 * [1]: https://gravitywiz.com/documentation/gravity-forms-date-time-calculator/#calculating-age
 */
add_action( 'wp_loaded', function() {

	$form_id  = 123;  // Change this to the form's ID
	$field_id = 4; // Change this to the Calculation field's ID.

	$values = array();

	add_filter( sprintf( 'gform_get_input_value_%s', $form_id ), function( $value, $entry, $field, $input_id ) use ( $field_id, &$values ) {
		if ( $field['id'] !== $field_id ) {
			$values[ $field['id'] ] = $value;
			return $value;
		}
		$form   = GFAPI::get_form( $entry['form_id'] );
		$_entry = $entry + $values;
		return GFCommon::calculate( $field, $form, $_entry );
	}, 10, 4 );

} );
