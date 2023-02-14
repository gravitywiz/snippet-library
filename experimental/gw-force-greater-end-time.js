/**
 * Gravity Wiz // Gravity Forms // Force Greater End Time.
 * https://gravitywiz.com/
 *
 * Force the user to enter an end time greater than the start time.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 * 2. Configure based on the inline instructions.
 */

// Update "3" to the Start Date Field ID.
var startDateId = 1;
// Update "4" to the End Date Field ID.
var endDateId = 3;
// Update "5" to the Start Time Field ID.
var startTimeFieldId = 4;
// Update "6" to the End Date Field ID.
var endTimeFieldId = 5;

var selectors = [
	'#input_'+ GFFORMID + '_' + startDateId,
	'#input_'+ GFFORMID + '_' + endDateId,
	'#field_' + GFFORMID + '_' + startTimeFieldId,
	'#field_' + GFFORMID + '_' + endTimeFieldId
];

$( selectors.join( ', ' ) ).change( function(){
	evaluateTimes();
} );

function evaluateTimes() {

	var startTimestamp = getTimestamp( startDateId, startTimeFieldId );
	var endTimestamp   = getTimestamp( endDateId, endTimeFieldId );

	if ( ! startTimestamp || ! endTimestamp ) {
		return false;
	}

	var difference = endTimestamp - startTimestamp;
	if ( difference <= 60 * 60 * 1000 ) {
		setEndTime( startTimestamp );
	}

}

function getTimestamp ( dateFieldId, timeFieldId ){

	var inputs   = $( '#field_' + GFFORMID + '_' + timeFieldId ).find('input, select' );
	var hour     = inputs.eq( 0 ).val();
	var min      = inputs.eq( 1 ).val();
	var	ampm     = inputs.eq( 2 ).val();
	// @todo This only supports datepickers.
	var	datetime = new Date( $( '#input_'+ GFFORMID + '_' + dateFieldId ).val() );

	if ( inputs.eq( 0 ).val() =='' ||  inputs.eq( 1 ).val() == '' ){
		return false
	}

	if ( inputs.eq( 2 ).length ) {
		if ( ampm.toLowerCase() === 'pm' ) {
			datetime.setHours( parseInt( hour ) + ( hour === '12' ? 0 : 12 ) );
		}else if ( ampm.toLowerCase() === 'am') {
			datetime.setHours( parseInt( hour ) - ( hour === '12' ? 12 : 0 ) );
		}
		else{
			datetime.setHours( parseInt( hour ) );
		}
	} else {
		datetime.setHours( parseInt( hour ) );
	}

	datetime.setMinutes( min );

	return datetime.getTime();
}

function setEndTime ( startTimestamp ) {

	var endDateTime = new Date( startTimestamp );
	var endInputs    = $( '#field_' + GFFORMID + '_' + endTimeFieldId ).find( 'input, select' );

	var hours   = isNaN( endDateTime.getHours() ) ? '' : endDateTime.getHours() + 1,
		minutes = isNaN( endDateTime.getMinutes() )  ? '' : endDateTime.getMinutes(),
		hasAMPM = endInputs.length === 3,
		isPM    = false;

	if ( hasAMPM ) {
		if ( hours === 0 ) {
			hours = 12;
		} else if ( hours > 12 ) {
			hours -= 12;
			isPM   = true;
		} else if ( hours == 12 ) {
			// for 12 PM, the PM display should update
			isPM = true;
		}

	}

	endInputs.eq( 0 ).val( ( '0' + hours ).slice( -2 ) );
	endInputs.eq( 1 ).val( ( '0' + minutes ).slice( -2 ) );

	if ( hasAMPM ) {
		if ( isPM ) {
			endInputs.eq( 2 ).find( 'option:last' ).prop( 'selected', true );
		} else {
			endInputs.eq( 2 ).find( 'option:first' ).prop( 'selected', true );
		}
	}
}
