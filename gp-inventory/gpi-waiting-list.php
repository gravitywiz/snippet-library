<?php
/**
 * Gravity Perks // Inventory // Waiting List for Exhausted Choices
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Replace the default available inventory message with a waiting list message when a choice's inventory is exhausted.
 * The choice will be selectable and submittable.
 */
add_filter( 'gpi_disable_choices', '__return_false' );
add_filter( 'gpi_remove_choices', '__return_false' );

add_filter( 'gpi_pre_render_choice', function( $choice, $exceeded_limit, $field, $form, $count ) {

	$limit         = rgar( $choice, 'inventory_limit' );
	$how_many_left = max( $limit - $count, 0 );

	if ( $how_many_left <= 0 ) {
		$message         = '(waiting list)';
		$default_message = gp_inventory_type_choices()->replace_choice_available_inventory_merge_tags( gp_inventory_type_choices()->get_inventory_available_message( $field ), $field, $form, $choice, $how_many_left );
		$choice['text']  = str_replace( $default_message, $message, $choice['text'] );
	}

	return $choice;
}, 10, 5 );
