( function( $ ) {
	var formId      = 560; // Change this to the form ID
	var fieldId     = 2;   // Change this to the field ID
	var maxSelected = 2;   // Number to limit maximum selected options to

	var selector                  = '#input_' + formId + '_' + fieldId;
	var triggerMultiSelectOptions = function( $select ) {
		var disable = $select.find( 'option:checked' ).length === maxSelected;
		$select.find( 'option:not(:checked)' ).prop( 'disabled', disable );
		$select.trigger( 'chosen:updated' );
	};
	// Standard multi-select
	$( selector + ' option' ).on('click change keyup blur', function () {
		triggerMultiSelectOptions( $( this ).parent() );
	});
	// Enhanced-UI multi-select
	$( selector ).on('change', function () {
		triggerMultiSelectOptions( $( this ) );
	});
})( jQuery );
