/**
 * Gravity Perks // Copy Cat // Copy Time-like Value to Time Field
 * https://gravitywiz.com/documentation/gravity-forms-copy-cat/
 *
 * Instruction Video: https://www.loom.com/share/461e303e913446d89f07de7064ca20cc
 */
var sourceFieldId = 1;
var timeFieldId = 2;

gform.addFilter( 'gpcc_copied_value', function( value, $elem, data, sourceValues ) {
	// Generate the value fresh as GPCC will try to convert the value to a number field beforehand if it is going to be populated into a numeric input (like a Time input).
	value = sourceValues.join( ' ' );
	if( data.source == sourceFieldId && value ) {
	    var date = parseTime( value );
		if ( ! date ) {
			return value;
		}
		switch( data.target ) {
			case timeFieldId + '.1':
				var hours = date.getHours();
				value = hours + ( hours > 12 ? -12 : 0 );
				break;
			case timeFieldId + '.2':
				value = date.getMinutes();
				break;
			case timeFieldId + '.3':
				value = date.getHours() > 12 ? 'pm' : 'am';
				break;
		}
		
   }
    return value;
} );

function parseTime( timeString ) {

	if ( timeString == '' ) {
		return null;
	}

	var date = new Date();
	var time = timeString.match( /(\d+)(:(\d\d))?\s*(p?)/i );

	date.setHours( parseInt( time[1], 10 ) + ( ( parseInt( time[1], 10 ) < 12 && time[4] ) ? 12 : 0 ) );
	date.setMinutes( parseInt( time[3], 10 ) || 0 );
	date.setSeconds( 0, 0 );

	return date;
}

// Hack as GPCC fires first copy before filter is bound.
$( '#input_GFFORMID_' + sourceFieldId ).change();
