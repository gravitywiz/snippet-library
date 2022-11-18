/**
 * Gravity Wiz // Gravity Forms // Prevent Duplicate Selections
 * https://gravitywiz.com/
 * 
 * Prevent duplicate selections in choice-based fields. Currently limited to Checkbox fields with plans
 * to support all choice-based fields (e.g. Radio Buttons, Drop Downs & Multi Selects).
 * 
 * For Drop Down field support, use this snippet: 
 * https://github.com/gravitywiz/snippet-library/blob/master/experimental/gw-prevent-duplicate-drop-down-selections.js
 * 
 * Instructions:
 * 
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 *
 * 2. Add 'gw-prevent-duplicates' to the CSS Class Name setting for any field in which duplicate selections
 *    should be prevented.
 *
$checkboxes = $( '.gw-prevent-duplicates' ).find( 'input' );

$checkboxes.click( function() {
	gwDisableDuplicates( $( this ), $checkboxes );
} );

$checkboxes.each( function() {
	gwDisableDuplicates( $( this ), $checkboxes );
} );

function gwDisableDuplicates( $elem, $group ) {
	let value = $elem.val();
	$group
		.not( $elem )
		.filter( '[value="{0}"]'.format( value ) )
		.prop( 'disabled', $elem.is( ':checked' ) );
}
