<?php
/**
 * Gravity Perks // Reload From // Clear Specific Field Values on Reload
 * https://gravitywiz.com/documentation/gravity-forms-reload-form/
 *
 * When reloading a form, you may want to preserve the submitted values in some values and remove them in others. This
 * snippet provides a basic mechanism for achieving this. Be sure to enable the "Preserve values from previous submission"
 * form setting in the "Reload Form" section.
 */
// Update "123" to your form ID.
add_filter( 'gprf_disable_dynamic_reload_123', function( $return ) {
	function gprf_clear_specific_field_values( $return ) {
		// Update "3" to the ID of the field for whose value you would like to clear.
		$_POST['input_3'] = '';
		remove_filter( 'gform_pre_render', 'gprf_clear_specific_field_values' );
		return $return;
	}
	add_filter( 'gform_pre_render', 'gprf_clear_specific_field_values' );
	return $return;
} );
