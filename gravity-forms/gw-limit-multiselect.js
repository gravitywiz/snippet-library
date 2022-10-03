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

limitMultiSelect( $select, maxSelected );

$select.on( 'change', function () {
	limitMultiSelect( $( this ), maxSelected );
} );

function limitMultiSelect( $select, maxSelected ) {
	var disable = $select.find( 'option:checked' ).length === maxSelected;
	$select
		.find( 'option:not(:checked)' )
		.prop( 'disabled', disable )
		.trigger( 'chosen:updated' );
}
