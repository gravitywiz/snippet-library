/**
 * Gravity Perks // Populate Anything // Remove Empty Checkbox Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */

 var $containers = $( '.gfield_checkbox' );
 
// On page load.
$containers.each( function() {
	gwizRemoveEmptyCheckboxes( $( this ) );
} );

// Show/hide checkboxes when the field is manipulated.
$containers.bind( 'DOMNodeInserted DOMNodeRemoved', function() {
	gwizRemoveEmptyCheckboxes( $( this ) );
} );

function gwizRemoveEmptyCheckboxes( $elem ) {
	$elem.find( 'input[type="checkbox"]' ).each( function() { 
		if( ! $( this ).val() ) {
			$( this ).parent().hide();
		}
		else {
			$( this ).parent().show();
		}
	} );
}
