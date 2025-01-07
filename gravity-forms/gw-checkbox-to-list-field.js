/**
 * Gravity Wiz // Gravity Forms // Checkbox to List Field
 * https://gravitywiz.com/
 *
 * Experimental Snippet 🧪
 *
 * When a Checkbox is clicked, add a new row to a List field with the checkbox value as the value in the first column 
 * of the newly added row.
 */
// Update "1" to your Checkbox field ID.
var $checkboxField = $( '#field_GFFORMID_1' );

// // Update "2" to your List field ID.
var $listField = $( '#field_GFFORMID_2' );

$checkboxField.find( 'input' ).on( 'click', function() {
	if ( $( this ).is( ':checked' ) ) {
		addRow( $listField, $( this ).val() );
	} else {
		removeRow( $listField, $( this ).val() );
	}
} );

function removeRow( $listField, value ) {
	var rowCount = $listField.find( '.gfield_list_group' ).length;
	if ( rowCount === 1 ) {
		$listField.find( 'input' ).val( '' );
	} else {
		$listField
			.find( '.gfield_list_group_item:first-child' )
			.find( 'input' )
			.each( function() {
				if ( $( this ).val() === value ) {
					$( this )
						.parents( '.gfield_list_group' )
						.find( '.delete_list_item' )
						.click();
				}
			} );	
	}
}

function addRow( $listField, value ) {
	var rowCount = $listField.find( '.gfield_list_group' ).length;
	var $singleRowNoValue = rowCount && $listField.find( 'input' ).first().val() === '';
	if ( ! $singleRowNoValue ) {
		$listField	
			.find( '.gfield_list_group:last-child' )
			.find( '.add_list_item' )
			.click();	
	}
	$listField
		.find( '.gfield_list_group:last-child' )
		.find( '.gfield_list_group_item:first-child input' )
		.val( value );
}
