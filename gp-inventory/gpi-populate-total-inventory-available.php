<?php
/**
 * Gravity Perks // Inventory // Populate Total Available Inventory Across Choices
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Use this snippet to populate the total available inventory across all choices for a given field.
 *
 * Instructions:
 *
 * 1. Install the snippet.
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 * 2. Add a Hidden field to your form.
 * 3. Enable the "Allow field to be populated dynamically" setting on the Hidden field.
 * 4. Set the "Parameter Name" to "gpi_total_inventory_{fieldId}" (no quotes).
 * 5. Replace {fieldId} with the ID of the field you would like to track inventory for.
 */
add_filter( 'gform_field_value', function( $value, $field, $name ) {
	if ( strpos( $name, 'gpi_total_inventory' ) === false ) {
		return $value;
	}

	$bits            = explode( '_', $name );
	$target_field_id = array_pop( $bits );

	$target_field    = GFAPI::get_field( $field->formId, $target_field_id );
	$choice_counts   = gp_inventory_type_choices()->get_choice_counts( $field->formId, $target_field );
	$total_available = 0;

	foreach ( $target_field->choices as $choice ) {
		$limit            = (int) $choice['inventory_limit'];
		$count            = (int) rgar( $choice_counts, $choice['value'] );
		$available        = $limit - $count;
		$total_available += $available;;
	}

	return $total_available;
}, 10, 3 );
