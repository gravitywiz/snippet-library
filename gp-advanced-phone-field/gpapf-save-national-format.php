<?php
/**
 * Gravity Perks // Advanced Phone Field // Save Phone Number In National Format
 * https://gravitywiz.com/documentation/gravity-forms-advanced-phone-field/
 *
 * Save the phone number in the national format, rather than international.
 * 
 * For example, rather than a US number saving as +15555551234, the number would be saved as (555) 555-1234.
 */
// Update "123" to your form ID and "4" to your Phone field ID.
add_action( 'gform_save_field_value_123_4', function( $value, $entry, $field, $form, $input_id ) {

	if ( ! is_callable( 'gp_advanced_phone_field' ) || ! class_exists( '\libphonenumber\PhoneNumberUtil' ) ) {
		return $value;
	}

	$proto             = gp_advanced_phone_field()->get_phone_number_proto( $value );
	$phone_number_util = \libphonenumber\PhoneNumberUtil::getInstance();
	if ( ! $proto ) {
		return $value;
	}
	return $phone_number_util->format( $proto, \libphonenumber\PhoneNumberFormat::NATIONAL );

}, 10, 5 );
