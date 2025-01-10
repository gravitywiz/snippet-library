<?php
/**
 * Gravity Wiz // Gravity Forms // Validate Postcode
 * https://gravitywiz.com/
 *
 * Experimental Snippet 🧪
 *
 * Check to confirm if the postcode entered into the Address field is a valid postcode.
 * Currently limited to UK postcodes. If you need support for another country, let us
 * know [via support](https://gravitywiz.com/support/).
 */
// Update "123" to the Form ID and "4" to the Address field ID.
add_filter( 'gform_field_validation_123_4', 'gw_validate_postcode', 10, 5 );
function gw_validate_postcode( $result, $value, $form, $field ) {
	$country_to_check = 'United Kingdom';
	$regex_pattern    = '/^([A-Z]{1,2}\d[A-Z\d]? ?\d[A-Z]{2}|GIR ?0A{2})$/';
	$country          = rgar( $value, $field->id . '.6' );
	if ( $country == $country_to_check ) {
		if ( $result['is_valid'] ) {
			$zip_value = rgar( $value, $field->id . '.5' );
			if ( ! preg_match( $regex_pattern, strtoupper( $zip_value ) ) ) {
				$result['is_valid'] = false;
				$result['message']  = 'Please enter a valid UK postcode.';
			}
		}
	}
	return $result;
}
