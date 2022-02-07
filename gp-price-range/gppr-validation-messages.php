<?php
/**
 * Gravity Perks // GP Price Range // Modify Validation Messages
 * http://gravitywiz.com/documentation/gp-price-range/
 */
add_filter( 'gppr_validation_messages', function( $messages, $min, $max, $form_id, $field_id ) {

	$min = GFCommon::to_money( $min );
	$max = GFCommon::to_money( $max );

	$messages['min_and_max'] = sprintf( 'Please enter an amount between %s and %s.', $min, $max );
	$messages['min']         = sprintf( 'Please enter a price greater than or equal to %s.', $min );
	$messages['max']         = sprintf( 'Please enter a price less than or equal to %s.', $max );

	return $messages;
}, 10, 5 );
