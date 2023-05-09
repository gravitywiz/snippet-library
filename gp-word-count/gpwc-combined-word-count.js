/**
 * Gravity Perks // Word Count // Combine Word Counts Across Multiple Fields
 * forms-https://gravitywiz.com/documentation/gravity-forms-word-count/
 *
 * Combine the words from multiple Paragraph Text fields and insert them into a single 
 * Paragraph Text field. Apply your Word Count minimum/maximum to this field as a way to
 * enforce a total word count limit across multiple fields.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 *
 * 2. Configure based on the inline instructions.
 */
// Update "1" and "2" to the IDs of Paragraph Text fields from which words should be combined.
var $textareas = $( '#input_GFFORMID_1, #input_GFFORMID_2' );

// Update "3" to the ID of the Paragraph Text field in which the combined words should be inserted.
var $combined = $( '#input_GFFORMID_3' );
 
$textareas.on( 'input propertychange', function() {
	var words = '';
	$textareas.each( function() {
		words += ' ' + $( this ).val();
	} );
	$combined.val( words ).trigger( 'input' );
} );
 
$combined.trigger( 'input' );
