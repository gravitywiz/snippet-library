<?php
/**
 * Gravity Perks // Populate Anything // Populate Checkboxes (and Multi Selects) as Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * When populating data from a Checkbox or Multi Select field via the Gravity Forms Entry object type, all selected values
 * for the given field of each entry will be populated as a single choice. This snippet will split those values up into
 * separate choices.
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gppa_input_choices_123_4', 'gppa_populate_checkboxes_as_choices', 10, 3 );
function gppa_populate_checkboxes_as_choices( $choices, $field, $objects ) {

	$choices   = array();
	$templates = rgar( $field, 'gppa-choices-templates', array() );

	if ( empty( $objects ) ) {
		return $choices;
	}

	$source_form     = GFAPI::get_form( $objects[0]->form_id );
	$source_field_id = str_replace( 'gf_field_', '', rgar( $templates, 'value' ) );
	$source_field    = GFAPI::get_field( $source_form, $source_field_id );

	foreach ( $objects as $object ) {
		foreach ( $object as $meta_key => $meta_value ) {
			if ( absint( $meta_key ) === absint( $source_field_id ) ) {
				/**
				 * Some fields such as the multi-select store the selected values in one meta value.
				 *
				 * Other fields such as checkboxes store them as individual meta values.
				 */
				$meta_value = GFAddOn::maybe_decode_json( $meta_value );
				if ( empty( $meta_value ) ) {
					continue;
				}

				if ( ! is_array( $meta_value ) ) {
					$meta_value = array( $meta_value );
				}

				foreach ( $meta_value as $value ) {
					$source_choice = $source_field->get_selected_choice( $value );
					$choices[]     = $source_choice;
				}

			}
		}
	}

	return $choices;
}
