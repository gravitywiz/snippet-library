/**
 * Gravity Perks // Inventory // Sold Out Message
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Display a "Sold Out" message when a choices inventory has been exhausted.
 */
add_filter( 'gpi_remove_choices', '__return_false' );

add_filter( 'gpi_pre_render_choice', function( $choice, $exceeded_limit, $field, $form, $count ) {

	$limit         = (int) rgar( $choice, 'inventory_limit' );
	$how_many_left = max( $limit - $count, 0 );

	if ( $how_many_left <= 0 ) {
		$message         = '(Sold Out)';
		$default_message = gp_inventory_type_choices()->replace_choice_available_inventory_merge_tags( gp_inventory_type_choices()->get_inventory_available_message( $field ), $field, $form, $choice, $how_many_left );
		if ( strpos( $choice['text'], $default_message ) === false ) {
			$choice['text'] .= ' ' . $message;
		} else {
			$choice['text'] = str_replace( $default_message, $message, $choice['text'] );
		}
		return $choice;
	}

	return $choice;
}, 10, 5 );
