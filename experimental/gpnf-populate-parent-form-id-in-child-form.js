/**
 * Gravity Perks // Nested Forms // Populate Parent Form ID in Child Form
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
gform.addAction( 'gpnf_init_nested_form', function( childFormId, gpnf ) {
	// Update "123" to your the ID of your child form.
	var targetChildFormId  = 123; 
	// Update "4" to the ID of the child field in which the parent form ID will be populated.
	var targetChildFieldId = 4;
	if ( childFormId == targetChildFormId ) {
		$( '#input_' + targetChildFormId + '_' + targetChildFieldId ).val( gpnf.formId ).change();
	}
} );
