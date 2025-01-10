<?php
/**
 * Gravity Perks // Populate Anything // Populate Form Fields as Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Convert a form ID populated into a choice-based field into a list of fields that belong to that form.
 *
 * This was designed for a scenario where one field is configured to populate forms from the `gf_form` table and another
 * field is configured to populate the form ID from the `gf_form_meta` table filtered by the selected in form in the first
 * field. This snippet will then take the form ID, fetch the form, and populate its fields as choices.
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gppa_input_choices_123_4', function( $choices, $field, $objects ) {

	$source_form = GFAPI::get_form( $choices[0]['value'] );
	$choices     = array();

	/**
	 * @var GF_Field $source_field
	 */
	foreach ( $source_form['fields'] as $source_field ) {
		$choices[] = array(
			'text'  => $source_field->get_field_label( true, '' ),
			'value' => $source_field->id,
		);
	}

	return $choices;
}, 10, 3 );
