/**
 * Gravity Wiz // Gravity Forms // Limit Multi Selects
 * https://gravitywiz.com/
 *
 * Limit how many options may be selected in a Multi Select field. Works with
 * regular Multi Select fields as well as fields with Enhanced UI enabled.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 * 2. Configure snippet per inline instructions.
 */

// Update "1" to the ID of your Multi Select field.
var multiSelectId = '#input_GFFORMID_1';
// Update "2" to the max number of options that should be selectable.
var maxSelected = 2;
// Alternate: Set max number by the value of another field.
// var maxSelected = parseInt( $( '#input_GFFORMID_4' ).val() );

var $select = $( multiSelectId );
var lastAcceptedValue = null;

limitMultiSelect( $select, maxSelected );

$select.on( 'change', function () {
	limitMultiSelect( $( this ), maxSelected );
} );

function limitMultiSelect( $select, maxSelected ) {
	var selectedCount = $select.find( 'option:selected' ).length;

	if ( selectedCount <= maxSelected ) {
		lastAcceptedValue = $select.val();
	} else {
		$select.val( lastAcceptedValue );
		$select.blur();
	}

	// Blur selector on mobile after max number of options are selected
	if ( !!navigator.platform.match( /iPhone|iPod|iPad/ ) ) {
		if ( selectedCount >= maxSelected ) {
			$select.blur();
		}
	} else {
		// If not on iOS, disable the options as disabled options do not update live on iOS
		$select
			.find( 'option:not(:checked)' )
			.prop( 'disabled', selectedCount >= maxSelected )
			.trigger( 'chosen:updated' );
	}
}
