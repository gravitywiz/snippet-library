<?php
/**
 * Gravity Perks // Advanced Phone Field // Custom Phone Number Format
 * https://gravitywiz.com/documentation/gravity-forms-advanced-phone-field/
 *
 * Create a custom number format and use this format for the default format saved to the field value. This example will
 * uses the international number (e.g. `+1 757-123-4567`) as a base and formats it like so: `+1 757.123.4567`.
 */
// Update "123" to your form ID and "4" to your Phone field ID.
add_action( 'gform_save_field_value_123_4', function( $value, $entry, $field, $form, $input_id ) {

	if ( ! is_callable( 'gp_advanced_phone_field' ) || ! class_exists( '\libphonenumber\PhoneNumberUtil' ) ) {
		return $value;
	}

	$proto             = gp_advanced_phone_field()->get_phone_number_proto( $value );
	$phone_number_util = \libphonenumber\PhoneNumberUtil::getInstance();
	$international     = $phone_number_util->format( $proto, \libphonenumber\PhoneNumberFormat::INTERNATIONAL );

	$international_parts = preg_split( '/[\s-]+/', $international );
	$dialing_code        = array_shift( $international_parts );
	$formatted           = sprintf( '%s %s', $dialing_code, implode( '.', $international_parts ) );

	return $formatted;
}, 10, 5 );
