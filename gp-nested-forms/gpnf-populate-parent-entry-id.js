/**
 * Gravity Perks // Nested Forms // Populate Parent Form ID in Child Form
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * This is useful if you want to apply conditional to your child form based on the parent form from which the child form is being loaded.
 *
 * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
gform.addAction( 'gpnf_init_nested_form', function( childFormId, gpnf ) {

	// Update "123" to your the ID of your child form.
	var targetChildFormId  = 123;

	// Update "4" to the ID of the child field in which the parent form ID will be populated.
	var targetChildFieldId = 4;

	if ( childFormId == targetChildFormId ) {
		// Delaying setting value so Populate Anything can pick up the change event.
		// Internal: HS#32287.
		setTimeout( function() {
			$( '#input_' + targetChildFormId + '_' + targetChildFieldId ).val( gpnf.formId ).change();
		} );
	}
} );
