/**
 * Gravity Perks // Auto List Field // Dynamic Row Labels for List Fields
 * https://gravitywiz.com/documentation/gravity-forms-auto-list-field/
 *
 * Dynamically populate the first column of a List field with a dynamic value that includes the row number.
 * For example, if your List field represents attendees to an event, you could label each row, "Attendee #1,
 * Attendee #2, etc).

 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 * 2. Update variables to match your form by following the inline instructions.
 */
// Update to your List field's ID.
let listFieldId = 1;

// Update to the value you would like populated; {0} is replaced with the row number.
let template    = 'Day {0}';

function gw_apply_list_field_value_template( $container, $row ) {
	let $rows;
	if ( $row ) {
		$rows = $row;
	} else {
		$rows = $container.find( '.gfield_list_group' );
	}
	$rows.each( function() {
		let rowIndex = $container.find( '.gfield_list_group' ).index( $( this ) );
		$( this )
			.find( 'input' )
			.eq( 0 )
			.val( template.gformFormat( rowIndex + 1 ) );
	} );
}

gw_apply_list_field_value_template( $( '#field_GFFORMID_{0}'.gformFormat( listFieldId ) ) );

gform.addAction( 'gform_list_post_item_add', function ( $row, $container ) {
	if ( gf_get_input_id_by_html_id( $container.parents( '.gfield' ).attr( 'id' ) ) == listFieldId ) {
		gw_apply_list_field_value_template( $container );
	}
} );

gform.addAction( 'gform_list_post_item_delete', function ( $container ) {
	if ( gf_get_input_id_by_html_id( $container.parents( '.gfield' ).attr( 'id' ) ) == listFieldId ) {
		gw_apply_list_field_value_template( $container );
	}
} );
