<?php
/**
 * Gravity Perks // Advanced Phone Field // Validation Only
 * https://gravitywiz.com/documentation/gravity-forms-advanced-phone-field/
 *
 * Use this snippet if you prefer GF's standard Phone field UX but would like to use GPAPF's real phone number validation.
 * This snippet will automatically apply to all Phone fields with the "standard" format (e.g. (###) ###-####) where GPAFP
 * is not enabled.
 */
add_action( 'init', function() {

	/**
	 * Enable GPAPF for all Phone fields during validation to allow GPAPF to handle validating the phone number's validity
     * without interfering with the Phone field's default UX.
	 */
	add_filter( 'gform_field_validation', function( $result, $value, $form, $field ) {
		if ( $field->get_input_type() === 'phone'
		     && $field->phoneFormat === 'standard'
		     && ! $field->gpapfEnable
		     && ! rgblank( $value )
		) {
			$cloned_field              = clone $field;
			$cloned_field->gpapfEnable = true;
			$parsed_phone_number       = '+1' . preg_replace( '/[^0-9]/', '', $value );
			$result                    = gp_advanced_phone_field()->validation( $result, $parsed_phone_number, $form, $cloned_field );
        }
		return $result;
	}, 10, 4 );

	/**
	 * Remove the format-specific validation message that is added by Gravity Forms when a Phone field is marked as having
     * failed validation. See the more detailed comment above the setting of the `origPhoneFormat` property above.
	 */
    add_filter( 'gform_field_content', function( $content, $field ) {

	    if ( $field->is_form_editor() || $field->get_input_type() !== 'phone' || $field->phoneFormat !== 'standard' || $field->gpapfEnable ) {
            return $content;
        }

        $dom = new DOMDocument();
	    $dom->loadHTML( $content );

        foreach ( $dom->getElementsByTagName( 'div' ) as $div ) {
            if ( strpos( $div->nodeValue, 'Phone format:' ) === 0 && in_array( 'instruction', explode( ' ', $div->getAttribute( 'class' ) ) ) ) {
                $div->parentNode->removeChild( $div );
            }
        }

        return $dom->saveHTML();
    }, 10, 2 );

} );
