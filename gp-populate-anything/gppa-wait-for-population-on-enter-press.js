/**
 * Gravity Perks // Populate Anything // Wait for Population Before Submitting When Enter Key Pressed
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * By default, the form is immediately submitted after the Enter key is pressed. This means if the field triggers
 * dynamic population of other fields via Populate Anything, that population will not occur before the form is submitted.
 *
 * This snippet allows to to specify trigger fields that should wait for Populate Anything to finish populating before
 * allowing the Enter-keypress-triggered submission from continuing.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 *
 * 2. Add "gppa-wait-for-pop" to the CSS Class Name setting for field that will trigger the population.
 */
$( document ).on( 'keypress', '.gppa-wait-for-pop', function( e ) {
	var code = e.keyCode || e.which;
	if ( code != 13 || $( e.target ).is( 'textarea,input[type="submit"],input[type="button"]' ) ) {
		return true;
	}
	e.preventDefault();
	$( document )
		.off( 'gppa_updated_batch_fields.gpqr' )
		.on( 'gppa_updated_batch_fields.gpqr', function( event, formId ) {
		setTimeout( function() {
			$( '#gform_{0}'.gformFormat( formId ) ).submit();
		} );
	} );
	return false;
} );
