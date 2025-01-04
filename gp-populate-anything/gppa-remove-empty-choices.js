/**
 * Gravity Perks // Populate Anything // Remove Empty Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
var $containers = $( '.gfield--type-choice' );

// On page load.
$containers.each( function() {
	gwizRemoveEmptyChoices( $( this ) );
} );

// Show/hide checkboxes when the field is manipulated.
$containers.bind( 'DOMNodeInserted DOMNodeRemoved change.gppa', function() {
	gwizRemoveEmptyChoices( $( this ) );
} );

function gwizRemoveEmptyChoices( $elem ) {
	$elem.find( 'input[type="checkbox"], input[type="radio"]' ).each( function() {
		if( ! $( this ).val() ) {
			$( this ).parent().hide();
		}
		else {
			$( this ).parent().show();
		}
	} );
}
