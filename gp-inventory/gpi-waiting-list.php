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

function gpiw_apply_waitlist_message( $choice, $field, $form, $how_many_left = false ) {
	$message         = '(waiting list)';
	$default_message = gp_inventory_type_choices()->replace_choice_available_inventory_merge_tags( gp_inventory_type_choices()->get_inventory_available_message( $field ), $field, $form, $choice, $how_many_left );
	if ( strpos( $choice['text'], $default_message ) === false ) {
		$choice['text'] .= ' ' . $message;
	} else {
		$choice['text'] = str_replace( $default_message, $message, $choice['text'] );
	}
	return $choice;
}

add_filter( 'gpi_pre_render_choice', function( $choice, $exceeded_limit, $field, $form, $count ) {

	$limit         = (int) rgar( $choice, 'inventory_limit' );
	$how_many_left = max( $limit - $count, 0 );

	if ( $how_many_left <= 0 ) {
		$choice = gpiw_apply_waitlist_message( $choice, $field, $form, $how_many_left );
		$choice['isWaitlisted'] = true;
	}

	return $choice;
}, 10, 5 );

// Add support for showing waiting list message in confirmations and notifications.
add_filter( 'gform_pre_submission_filter', function( $form ) {
	foreach ( $form['fields'] as &$field ) {
		if ( ! gp_inventory_type_choices()->is_applicable_field( $field ) ) {
			continue;
		}
		$choice_counts = gp_inventory_type_choices()->get_choice_counts( $form['id'], $field );
		$choices       = $field['choices'];
		foreach ( $choices as &$choice ) {
			$value        = $field->sanitize_entry_value( $choice['value'], $form['id'] );
			$choice_count = intval( rgar( $choice_counts, $value ) );
			$choice       = gf_apply_filters( array( 'gpi_pre_render_choice', $form['id'], $field->id ), $choice, null, $field, $form, $choice_count );
		}
		$field['choices'] = $choices;
	}
	return $form;
} );

add_filter( 'gform_entry_post_save', function( $entry, $form ) {
	foreach ( $form['fields'] as $field ) {
		if ( ! gp_inventory_type_choices()->is_applicable_field( $field ) ) {
			continue;
		}
		foreach ( $field->choices as $choice ) {
			if ( rgar( $choice, 'isWaitlisted' ) ) {
				gform_add_meta( $entry['id'], sprintf( 'gpi_is_waitlisted_%d_%s', $field->id, sanitize_title( $choice['value'] ) ), true );
			}
		}
	}
	return $entry;
}, 10, 2 );

add_filter( 'gform_entries_field_value', function( $value, $form_id, $field_id, $entry ) {

	$form  = GFAPI::get_form( $form_id );
	$field = GFAPI::get_field( $form, $field_id );

	$value = gpiw_add_waitlist_message_to_entry_value( $value, $entry, $field, $form );

	return $value;
}, 10, 4 );

add_filter( 'gform_entry_field_value', function ( $value, $field, $entry, $form ) {
	return gpiw_add_waitlist_message_to_entry_value( $value, $entry, $field, $form );
}, 10, 4 );

function gpiw_add_waitlist_message_to_entry_value( $value, $entry, $field, $form ) {

	if ( ! gp_inventory_type_choices()->is_applicable_field( $field ) ) {
		return $value;
	}

	foreach ( $field->choices as $choice ) {
		if( $choice['text'] != $value ) {
			continue;
		}
		$is_waitlisted = gform_get_meta( $entry['id'], sprintf( 'gpi_is_waitlisted_%d_%s', $field->id, sanitize_title( $choice['value'] ) ) );
		if ( $is_waitlisted ) {
			$choice = gpiw_apply_waitlist_message( $choice, $field, $form );
			$value  = $choice['text'];
		}
	}

	return $value;
}

/**
 * @todo Add support for searching for entries that contain items from any waitlist or a product/choice-specific waitlist.
 */
//add_filter( 'gform_field_filters', function( $field_filters, $form ) {
//	$field_filters[] = array(
//		'text' => 'Waiting List',
//		'operators' => array( 'is', 'isnot' ),
//		'key' => 'gpi_waiting_list',
//		'preventMultiple' => false,
//		'values' => array(
//			array(
//				'text' => 'Any Waitlist',
//				'value' => 'NOTNULL',
//			),
//		)
//	);
//	return $field_filters;
//}, 10, 2 );