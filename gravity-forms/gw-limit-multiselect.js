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
var max = 2;
// Alternate: Set max number by the value of another field. Update "3" to the field ID.
// var maxFieldId = $( '#input_GFFORMID_3' );

var $select = $( multiSelectId );
var lastAcceptedValue = null;

limitMultiSelect( $select, getMax() );

$select.on( 'change', function () {
	limitMultiSelect( $( this ), getMax() );
} );

if ( typeof maxFieldId !== 'undefined' ) {
	$( maxFieldId ).on( 'change', function() {
		limitMultiSelect( $select, $( this ).val() );
	} );
}

function limitMultiSelect( $select, max ) {
	var selectedCount = $select.find( 'option:selected' ).length;

	if ( selectedCount <= max ) {
		lastAcceptedValue = $select.val();
	} else {
		if ( lastAcceptedValue.length > max ) {
			// Remove elements from array until it is less than the max variable using array.splice.
			lastAcceptedValue.splice( max, lastAcceptedValue.length - max );
		}
		$select.val( lastAcceptedValue );
		$select.blur();
	}

	// Blur selector on mobile after max number of options are selected
	if ( !! navigator.platform.match( /iPhone|iPod|iPad/ ) || !! navigator.userAgent.match( /android/i ) ) {
		if ( selectedCount >= max ) {
			$select.blur();
		}

		if ( selectedCount > max ) {
			alert('Please select ' + max + ' choices or fewer.');
		}
	} else {
		// If not on iOS, disable the options as disabled options do not update live on iOS
		$select
			.find( 'option:not(:checked)' )
			.prop( 'disabled', selectedCount >= max )
			.trigger( 'chosen:updated' );

		// For Tom Select (GP Advanced Select)
		// Update "1" to the ID of your Multi Select field.
		if ( $( '#input_GFFORMID_1-ts-dropdown' ) ) {
			$( '#input_GFFORMID_1-ts-dropdown .option' ).removeAttr( 'data-selectable' );
		}
	}
}

function getMax() {
	return typeof maxFieldId !== 'undefined' ? $( maxFieldId ).val() : max;
}
