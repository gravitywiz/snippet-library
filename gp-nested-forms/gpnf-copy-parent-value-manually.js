/**
 * Gravity Perks // Nested Forms // Copy Parent Value Manually
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Manually copy a parent value into a child form field. The {Parent} merge tag would typically be used
 * for this but when you are embedding a child form into multiple parent forms, the {Parent} merge tag
 * cannot differentiate between them and will populate incorrect values.
 */
gform.addAction( 'gpnf_init_nested_form', function( childFormId, gpnf ) {

	// Update "123" to your the ID of your child form.
	var targetChildFormId = 123;

	// Update "4" to the ID of the child field in which the parent form ID will be populated.
	var childFieldId = 4;

	// Update "5" to the ID of the parent field whose value will be copied to the child field.
	var parentFieldId = 5;

	if ( childFormId == targetChildFormId ) {
		copyParentFormValue( GFFORMID, parentFieldId, childFormId, childFieldId );
	}

} );

function copyParentFormValue( parentFormId, parentFieldId, childFormId, childFieldId ) {

	var value = jQuery ( '#input_{0}_{1}'.gformFormat( parentFormId, parentFieldId ) ).val();

	// Delaying setting value so Populate Anything can pick up the change event.
	setTimeout( function() {
		$( '#input_' + childFormId + '_' + childFieldId ).val( value ).change();
	} );

}
