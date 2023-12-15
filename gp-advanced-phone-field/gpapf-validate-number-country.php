<?php
/**
 * Gravity Perks // Advanced Phone Field // Validate Number by Country
 * https://gravitywiz.com/documentation/gravity-forms-advanced-phone-field/
 *
 * If you are limiting available countries to Canada, US numbers are still accepted. This snippet will ensure that the
 * country of the submitted number is a country that is allowed according to your "Countries" setting.
 */
add_action( 'gform_field_validation', function( $result, $field_value, $form, $field, $context ) {

	if ( class_exists( 'GP_Advanced_Phone_Field' ) && gp_advanced_phone_field()->is_advanced_phone_field( $field ) ) {

		$countries_action = gp_advanced_phone_field()->get_plugin_setting( 'countries_action' );
		if ( $countries_action === 'all' ) {
			return $result;
		}

		$countries = gp_advanced_phone_field()->get_plugin_setting( 'countries' );
		$country   = gp_advanced_phone_field()->get_phone_number_proto( $field_value )->regionCode;
		$is_found  = in_array( $country, $countries );

		if ( $countries_action === 'include' && ! $is_found ) {
			$result['is_valid'] = false;
			$result['message']  = esc_html__( 'This phone number is not valid for the selected country.' );
		} elseif ( $countries_action === 'exclude' && $is_found ) {
			$result['is_valid'] = false;
			$result['message']  = esc_html__( 'This phone number is not valid for the selected country.' );
		}
	}

	return $result;
}, 10, 5 );
