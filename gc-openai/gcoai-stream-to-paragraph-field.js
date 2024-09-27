/**
 * Gravity Connect // OpenAI // Stream to Paragraph Field
 * https://gravitywiz.com/documentation/gravity-connect-openai/
 *
 * Stream OpenAI responses from the OpenAI Stream field to a Paragraph (or Single Line Text)
 * field instead. Useful when wanting to provide a starting point for users while allowing them
 * to edit the final result.
 * 
 * Instruction Video: https://www.loom.com/share/f793319da7e449a8b01e5a8c077e24c7
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 * 
 * 2. Update the variables to match your own field IDs.
 */
var streamFieldId = 3;
var promptFieldId = 1;
var responseFieldId = 4;
var appendButtonFieldId = responseFieldId;

var $streamFieldInput = $( `#input_GFFORMID_${streamFieldId}` );
var $streamButton     = $streamFieldInput.parents( '.gfield' ).find( '.gcoai-trigger' );

$streamFieldInput.on( 'change', function() {
	$input = $( `#input_GFFORMID_${responseFieldId}` );
	$input.val( this.value );
	if (window.tinyMCE) {
		var tiny = tinyMCE.get( $input.attr( 'id' ) );
		if (tiny) {
			tiny.setContent( this.value );
		}
	}
} );

let $newButton = $streamButton
	.clone()
	.attr( 'style', 'margin-top: var(--gf-label-space-primary, 8px);' )
	.on( 'click', function() {
		$streamButton.trigger( 'click' );
	} )
	.insertAfter( $( `#input_GFFORMID_${appendButtonFieldId}` ) );

$wpEditor = $newButton.parents( '.wp-editor-container' );
if ( $wpEditor.length ) {
	$newButton.insertAfter( $wpEditor );
}

$( `#input_GFFORMID_${promptFieldId}` ).on( 'blur', function() {
	$streamButton.trigger( 'click' );
} );
