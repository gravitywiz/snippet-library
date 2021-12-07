/**
 * Gravity Perks // Nested Forms // Unsaved Child Entries Warning for Gravity View
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * If you add child entries to a Nested Form field when editing a parent entry in GravityView, these new child entries
 * will not be saved to the parent entry unless the user updates the parent entry.
 *
 * This snippet will show a warning that there are unsaved changes if the user attempts to exit the page without
 * updating the parent entry.
 */
$( document ).ready( function() {

	// Update "123" to your parent form ID and "4" to your Nested Form field ID.
	var $field        = $( '#input_123_4' );
	var origValue     = $field.val();
	var unloadWarning = true;

	window.onbeforeunload = function ( e) {
		if ( unloadWarning && $field.val() !== origValue ) {
			return true;
		}
	};

	$( '.gv-button-update, .gv-button-delete' ).on( 'click', function() {
		unloadWarning = false;
	} );

} );
