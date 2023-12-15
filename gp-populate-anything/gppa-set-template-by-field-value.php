<?php
/**
 * Gravity Perks // Populate Anything // Set Template by Field Value
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Instruction Video: https://www.loom.com/share/02cb5511b5ba4cf6a051ee7038f55f16
 *
 * I'll take another stab at explaining this another day. For now, watch the instructional video above. 😅
 */
// Update "123" to your form ID and "4" to the ID of the field being populated.
add_filter( 'gppa_pre_populate_field_123_4', function( $return, $field, $form, $field_values, $entry, $force_use_field_value, $include_html, $run_pre_render ) {
	// Update "5" to the ID of the field whose value should be used in the `value` template.
	$template_value_by_field_id = 5;
	static $doing_it;
	if ( ! $doing_it ) {
		$doing_it                                  = true;
		$field->{'gppa-values-templates'}['value'] = rgpost( "input_{$template_value_by_field_id}" );
		$return                                    = gp_populate_anything()->populate_field( $field, $form, $field_values, $entry, $force_use_field_value, $include_html, $run_pre_render );
		$doing_it                                  = false;
	}
	return $return;
}, 10, 8 );
