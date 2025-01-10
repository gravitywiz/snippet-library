/**
 * Gravity Perks // Copy Cat // Copy List Field Rows as New Lines
 * https://gravitywiz.com/documentation/gravity-forms-copy-cat/
 *
 * Use this snippet to copy List field rows as new lines into a Paragraph field.
 *
 * Screenshot: https://gwiz.io/3wWlUts
 *
 * Instructions:
 * 
 * 1. Install this snippet with our free Code Chest plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
gform.addFilter( 'gpcc_copied_value', function( value, $targetElem, field, sourceValues ) {
	// Update "3" to your Paragraph field ID.
	if ( field.sourceFormId != GFFORMID || field.target != 3 ) {
		return value;
	}
	sourceValues = structuredClone( sourceValues );
	// Update "3" to the number of columns in your List field.
	var columnCount = 3;
	var lines = [];
	var line  = [];
	while ( sourceValues.length ) {
		line.push( sourceValues.shift() );
		if ( line.length == columnCount ) {
			lines.push( line.join( ' ' ) );
			line = [];
		}
	}
	value = lines.join( "\n" );
	return value;
} );
