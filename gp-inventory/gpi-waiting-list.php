<?php
/**
 * Gravity Perks // Inventory // Waiting List for Exhausted Choices
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Instruction Video: https://www.loom.com/share/7a5e57ec14404b9080c5e9b9878e2ecc
 *
 * Replace the default available inventory message with a waiting list message when a choice's inventory is exhausted.
 * The choice will be selectable and submittable.
 */
add_filter( 'gpi_disable_choices', '__return_false' );
add_filter( 'gpi_remove_choices', '__return_false' );

add_filter( 'gpi_pre_render_choice', function( $choice, $exceeded_limit, $field, $form, $count ) {

	$limit         = (int) rgar( $choice, 'inventory_limit' );
	$how_many_left = max( $limit - $count, 0 );

	if ( $how_many_left <= 0 ) {
		$message         = '(waiting list)';
		$default_message = gp_inventory_type_choices()->replace_choice_available_inventory_merge_tags( gp_inventory_type_choices()->get_inventory_available_message( $field ), $field, $form, $choice, $how_many_left );
		if ( strpos( $choice['text'], $default_message ) === false ) {
			$choice['text'] .= ' ' . $message;
		} else {
			$choice['text'] = str_replace( $default_message, $message, $choice['text'] );
		}

	}

	return $choice;
}, 10, 5 );

add_filter( 'gform_pre_submission_filter', function( $form ) {
	foreach ( $form['fields'] as &$field ) {
		if ( ! gp_inventory_type_choices()->is_applicable_field( $field ) ) {
			continue;
		}
		$choice_counts = gp_inventory_type_choices()->get_choice_counts( $form['id'], $field );
		$choices       = $field['choices'];
		foreach( $choices as &$choice ) {
			$value        = $field->sanitize_entry_value( $choice['value'], $form['id'] );
			$choice_count = intval( rgar( $choice_counts, $value ) );
			$choice       = gf_apply_filters( array( 'gpi_pre_render_choice', $form['id'], $field->id ), $choice, null, $field, $form, $choice_count );
		}
		$field['choices'] = $choices;
	}
	return $form;
} );
