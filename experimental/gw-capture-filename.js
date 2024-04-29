/**
 * Gravity Wiz // Gravity Forms // Capture Filename from Single File Upload Field
 * https://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/294728eabe244fe8aba72b051155b4d7
 *
 *  * Installation:
 *    1. Install and Activate https://gravitywiz.com/gravity-forms-code-chest/
 *    2. Navigate to Form Settings > Custom JavaScript and add this snippet.
 */
var uploadFieldId = 4;
var targetFieldId = 5;
var template = '{filename}';

var $uploadField = $( '#input_GFFORMID_{0}'.gformFormat( uploadFieldId ) );
var $targetField = $( '#input_GFFORMID_{0}'.gformFormat( targetFieldId ) );

$uploadField.on( 'change', function() {
	var filename = $( this ).val().split("\\").pop();
	$targetField.val( template.replace( '{filename}', filename ) ).change();
} );
