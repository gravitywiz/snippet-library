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
var $streamButton     = $streamFieldInput.closest( '.gfield' ).find( '.gcoai-trigger' );

$streamFieldInput.on( 'change', function() {
	$input = $( `#input_GFFORMID_${responseFieldId}` );
	var inputValue = this.value;

	// Check if the response field has TinyMCE enabled.
	var tiny = window.tinyMCE && tinyMCE.get( $input.attr( 'id' ) );

	// Get HTML for the response field if TinyMCE is available.
	if (tiny) {
		var html = $streamFieldInput.closest( '.gfield' ).find('.gcoai-output').html();
		
		// Set HTML content in TinyMCE.
		tiny.setContent( html );
	} else {
		// If TinyMCE is not available, use plain text.
		$input.val( inputValue );
	}
});

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
});
