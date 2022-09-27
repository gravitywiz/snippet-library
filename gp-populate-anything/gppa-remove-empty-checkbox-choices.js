/**
 * Gravity Perks // Populate Anything // Remove Empty Checkbox Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */

// replace 6 with ID of the targetted checkbox field
// for targetting all checkboxes on the form use '.gfield_checkbox' as the selector
var $containers = $( '#input_' + GFFORMID + '_6' );
 
// On page load.
$containers.each( function() {
	gwizRemoveEmptyCheckboxes( $( this ) );
} );

// Show/hide checkboxes when the field is manipulated.
$containers.bind( 'DOMNodeInserted DOMNodeRemoved', function() {
	gwizRemoveEmptyCheckboxes( $( this ) );
} );

function gwizRemoveEmptyCheckboxes( $elem ) {
	// replace 6 with ID of the targetted checkbox field
	// for targetting all checkboxes on the form use 'input[type="checkbox"]' as the selector
	$elem.find( 'input[id^=choice_' + GFFORMID + '_6_' ).each( function() { 
		if( ! $( this ).val() ) {
			$( this ).parent().hide();
		}
		else {
			$( this ).parent().show();
		}
	} );
}
