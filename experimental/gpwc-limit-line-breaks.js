// Limit the number of line breaks allowed in a GPWC field
var form_id     = 553; // Update this to the form ID
var field_id    = 1;   // Update this to the field ID
var lines_limit = 10;  // Update this to the maximum number of lines allowed
$( '#input_' + form_id + '_' + field_id ).on('keyup change input', function () {
	var lines = $( this ).val().split( /\r\n|\r|\n/ );
	while ( lines.length > lines_limit ) {
		var extraLine              = lines.pop();
		lines[ lines.length - 1 ] += extraLine;
	}
	$( this ).val( lines.join( '\n' ) );
} );
