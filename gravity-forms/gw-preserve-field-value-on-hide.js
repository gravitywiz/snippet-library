/**
 * Gravity Wiz // Gravity Forms // Preserve Field Value on Conditional Logic Hide
 * https://gravitywiz.com/
 *
 * Instructions:
 * 1. Install our free Gravity Forms Custom Javascript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 * 2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 * 3. Update '4' to the ID of the field you want to target.
 */
gform.addFilter( 'gform_reset_pre_conditional_logic_field_action', function( shouldReset, formId, targetId, defaultValues, isInit ) {
	if ( formId == GFFORMID && gf_get_input_id_by_html_id( targetId ) == 4 ) {
		shouldReset = false;
	}
	return shouldReset;
} );
