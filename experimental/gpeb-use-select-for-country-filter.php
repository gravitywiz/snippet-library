<?php
/**
 * Gravity Perks // Entry Blocks // Filters Block: Use Select for Country Input Filter
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * All input fields in the Entry Blocks filter are text inputs by default. This snippet changes the input type to a
 * select for the Address field's Country input.
 */
add_filter( 'gpeb_filter_form_field', function ( $field ) {
	if ( ! rgar( $field, 'gpebFilterInputField' ) ) {
		return $field;
	}

	$input_field = $field['gpebFilterInputField'];

	// If we're not working with an Address field, return the field as-is.
	if ( $input_field['type'] !== 'address' ) {
		return $field;
	}

	// Get the input ID.
	$input_id = explode( '.', rgar( $field, 'gpebFilterSearch' ) )[1];

	switch ( $input_id ) {
		// Country
		case 6:
			$country_choices = array_map( function ( $country ) {
				return array(
					'text'  => $country,
					'value' => $country
				);
			}, $field['gpebFilterInputField']->get_countries() );

			// Change the field to a select and populate it with countries from the address field.
			$new_field = new \GF_Field_Select( array(
				'id'               => $field['id'],
				'label'            => $field['label'],
				'gpebFilterSearch' => $field['gpebFilterSearch'],
				'gpebFilterInputField' => $field['gpebFilterInputField'],
				'size'             => $field['size'],
				'inputs'           => $field['inputs'],
				'formId'           => $field['formId'],
				'type'    => 'select',
				'choices' => $country_choices
			) );
			return $new_field;
	}

	return $field;
} );
