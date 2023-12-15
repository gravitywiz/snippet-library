<?php
// Update "123" to your child form ID and "4" to the child File Upload field ID.
add_filter( 'gpnf_display_value_123_4', function( $value, $field, $form, $entry ) {
	$value['label'] = sprintf( '<img src="%s">', $value['value'] );
	return $value;
}, 10, 4 );
