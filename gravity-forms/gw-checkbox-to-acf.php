<?php
/**
 * Gravity Wiz // Gravity Forms // Map GF Checkbox Field to ACF Checkbox Field (User Meta)
 * http://graivtywiz.com/
 */
add_filter( 'gform_user_registration_meta_value', function( $value, $meta_key ) {
	// Update "checkboxes" to your custom field's name.
	if( $meta_key === 'checkboxes' ) {
		$value = array_map( 'trim', explode( ',', $value ) );
	}
	return $value;
}, 10, 2 );
