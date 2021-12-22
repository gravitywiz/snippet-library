/**
 * Gravity Wiz // Gravity Forms // Capture Filename from Single File Upload Field
 * https://gravitywiz.com/
 */
var uploadFieldId = 4;
var targetFieldId = 5;
var template = '{filename}';

var $uploadField = $( '#input_GFFORMID_{0}'.format( uploadFieldId ) );
var $targetField = $( '#input_GFFORMID_{0}'.format( targetFieldId ) );

$uploadField.on( 'change', function() {
	var filename = $( this ).val().split("\\").pop();
	$targetField.val( template.replace( '{filename}', filename ) ).change();
} );
