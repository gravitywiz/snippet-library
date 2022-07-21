<?php
/**
 * Gravity Perks // Limit Choices // Customize Out of Stock Validation Message
 * https://gravitywiz.com/documentation/gravity-forms-limit-choices/
 */
add_filter( 'gplc_out_of_stock_message', function( $out_of_stock_message, $form, $field, $inventory_data ) {
	return 'Sorry, friends. This item is no longer available.';
}, 10, 4 );
