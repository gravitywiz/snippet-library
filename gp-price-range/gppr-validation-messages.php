<?php
/**
 * Gravity Perks // GP Price Range // Modify Validation Messages
 * http://gravitywiz.com/documentation/gp-price-range/
 *
 * Instruction Video: https://www.loom.com/share/837c81c308e34858ae693d8a45d79a6e
 */
add_filter( 'gppr_validation_messages', function( $messages, $min, $max, $form_id, $field_id ) {

	$min = GFCommon::to_money( $min );
	$max = GFCommon::to_money( $max );

	$messages['min_and_max'] = sprintf( 'Please enter an amount between %s and %s.', $min, $max );
	$messages['min']         = sprintf( 'Please enter a price greater than or equal to %s.', $min );
	$messages['max']         = sprintf( 'Please enter a price less than or equal to %s.', $max );

	return $messages;
}, 10, 5 );
