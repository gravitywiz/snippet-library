/**
 * Gravity Perks // Nested Forms // Relaunch Modal After Adding Child Entry
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instructions:
 *
 * 1. Install this snippet on your parent form with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 * 2. This will automatically apply to all Nested Form fields for the parent form
 *    on which it is installed.
 */
var $addEntry;

$( '#gform_GFFORMID' ).find( '.gpnf-add-entry' ).on( 'click', function() {
	$addEntry = $( this );
} );

$( '#gform_GFFORMID' ).on( 'click', '.gpnf-row-actions .edit-button', function() {
	$addEntry = null;
} );

$( document ).on( 'gform_confirmation_loaded', function( event, formId ) {
	if ( $addEntry && formId == $addEntry.data( 'nestedformid' ) ) {
		$addEntry.click();
	}
} );
