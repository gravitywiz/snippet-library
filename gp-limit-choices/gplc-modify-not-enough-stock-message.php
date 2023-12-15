<?php
/**
 * GP Limit Choices // Gravity Perks // Modify the "Not Enough Stock" Message
 */
add_filter( 'gplc_not_enough_stock_message', function( $not_enough_stock_message, $form, $field, $inventory_data ) {
	// translators: both placeholders are numbers
	$my_custom_message = _n(
		'You ordered %1$d tickets but only %2$d is left!',  // message will be displayed if only 1 item is left
		'You ordered %1$d tickets but only %2$d are left!', // message will be displayed if there are 2 or more items left
		$inventory_data['remaining']
	);
	return $my_custom_message;
}, 10, 4 );

// only apply to form ID 1
add_filter( 'gplc_not_enough_stock_message_1', function() {
	/* custom code */
}, 10, 4 );

// only apply to field ID 2 on form ID 1
add_filter( 'gplc_not_enough_stock_message_1_2', function() {
	/* custom code */
}, 10, 4 );
