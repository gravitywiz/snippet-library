/**
 * Gravity Wiz // Gravity Forms // Conditionally Disable Checkboxes
 * https://gravitywiz.com/
 *
 * Disable checkboxes in one Checkbox field depending on the values checked in another.
 *
 * Instructions:
 *
 * 1. Watch this video:
 *    https://www.loom.com/share/b3ebfdc6b6b2440f917e3b431c615e21
 *
 * 2. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
// Update "1" to your first Checkbox field's ID.
var $cbs1 = $( '#input_GFFORMID_1' );
// Update "2" to your second Checkbox field's ID.
var $cbs2 = $( '#input_GFFORMID_2' );
// See video above for instructions on editing exclusions.
// Note: This uses the choice value not the choice label. When targeting Product Option fields configured as a checkbox, you must include the price (e.g. 'First Choice B|15').
var exclusions = {
	// First Checkbox field exclusions.
	'First Choice A': [ 'First Choice B' ],
	'Second Choice A': [ 'Second Choice B' ],
	'Third Choice A': [ 'Third Choice B', 'Fourth Choice B', 'Fifth Choice B' ],
	// Second Checkbox field exclusions.
	'First Choice B': [ 'First Choice A' ],
	'Second Choice B': [ 'Second Choice A' ],
	'Third Choice B': [ 'Third Choice A' ],
	'Fourth Choice B': [ 'Third Choice A' ],
	'Fifth Choice B': [ 'Third Choice A' ],
};

$cbs1.on( 'change', function() {
	gwDisableCheckboxes( $cbs1, $cbs2, exclusions )
} );

$cbs2.on( 'change', function() {
	gwDisableCheckboxes( $cbs2, $cbs1, exclusions )
} );

function gwDisableCheckboxes( $triggerField, $targetField, exclusions ) {

	var checkedValues = [];
	$.each( $triggerField.find( 'input:checked:not( .gplc-disabled, .gwlc-disabled, .gpi-disabled )' ), function() {
		checkedValues.push( $(this).val() );
	} );

	var $targetCheckboxes = $targetField.find( 'input[type="checkbox"]:not( .gplc-disabled, .gwlc-disabled, .gpi-disabled )' );
	$targetCheckboxes.prop( 'disabled', false );

	for ( const [ key, value ] of Object.entries( exclusions ) ) {
		if ( $.inArray( key, checkedValues ) !== -1 ) {
			for ( const targetValue of value ) {
				$targetCheckboxes.filter( '[value="' + targetValue + '"]' )
				                 .prop( 'checked', false )
				                 .prop( 'disabled', true );
			}
		}
	}

}
