<?php
/**
 * Gravity Perks // Populate Anything // Use Checkbox Field as Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Plugin Name:  GPPA Use Checkbox Field as Choices
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * Description:  Allows you to use a checkbox field as choices.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 *
 * Instruction Video: https://www.loom.com/share/27416c01037949a1bd600b27bb5a834c
 *
 * The snippet allows you to use a checkbox field as choices. This will use whatever property
 * is selected in the "Value Template" for the choices.
 */
// Update "123" to your form ID where you are using Populate Anything and "4" to your field ID
add_filter( 'gppa_input_choices_123_4', 'gppa_populate_checkboxes_as_choices', 10, 3 );
function gppa_populate_checkboxes_as_choices( $choices, $field, $objects ) {
	$choices   = array();
	$templates = rgar( $field, 'gppa-choices-templates', array() );
	foreach ( $objects as $object ) {
		$field_id = str_replace( 'gf_field_', '', rgar( $templates, 'value' ) );
		foreach ( $object as $meta_key => $meta_value ) {
			if ( absint( $meta_key ) === absint( $field_id ) ) {
				/**
				 * Some fields such as the multi-select store the selected values in one meta value.
				 *
				 * Other fields such as checkboxes store them as individual meta values.
				 */
				$meta_value = GFAddOn::maybe_decode_json( $meta_value );
				if ( empty( $meta_value ) ) {
					continue;
				}
				if ( is_array( $meta_value ) ) {
					foreach ( $meta_value as $value ) {
						$choices[] = array(
							'value' => $value,
							'text'  => $value,
						);
					}
				} else {
					$choices[] = array(
						'value' => $meta_value,
						'text'  => $meta_value,
					);
				}
			}
		}
	}
	return $choices;
}
