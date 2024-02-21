/**
 * Gravity Wiz // Gravity Forms // Submit from Any Page
 * https://gravitywiz.com/
 *
 * Allow submission of a form from any page by adding a "Submit Now" button to each page.
 * This can be particularly useful when editing an existing entry and wanting to allow the
 * user to submit those edits from whichever page they've edited.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
$( '.gform_next_button' ).each( function() {
	var fieldId = gf_get_input_id_by_html_id( $( this ).attr( 'id' ) );
	$( this ).after( '<input type="button" id="gform_submit_button_GFFORMID_' + fieldId + '" class="gform_submit_button button" value="Submit Now" onclick="jQuery(\'#gform_target_page_number_GFFORMID\').val(\'0\');  jQuery(\'#gform_GFFORMID\').trigger(\'submit\',[true]); " onkeypress="if( event.keyCode == 13 ){ jQuery(\'#gform_target_page_number_GFFORMID\').val(\'0\');  jQuery(\'#gform_GFFORMID\').trigger(\'submit\',[true]); } ">' );
} );
