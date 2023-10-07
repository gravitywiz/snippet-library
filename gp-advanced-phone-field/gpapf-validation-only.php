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
		if ( $field->get_input_type() === 'phone' && $field->phoneFormat === 'standard' && ! $field->gpapfEnable ) {
	        $field->gpapfEnable = true;
            // GPAPF unsets the `phoneFormat` to avoid format-specific validation messages. We need to store the original
            // format and reset it after GPAFP's validation. Unfortunately, since we need GF to initialize its input mask
            // based on the `phoneFormat`, we have to allow it to output its format-specific validation message. Which we
            // remove via DOM manipulation below.
            $field->origPhoneFormat = $field->phoneFormat;
        }
		return $result;
	}, 9, 4 );

	/**
	 * Disable GPAPF after validation so that it does not interfere with the default Phone field's input mask.
	 */
	add_filter( 'gform_field_validation', function( $result, $value, $form, $field ) {
		if ( $field->origPhoneFormat ) {
			$field->gpapfEnable = false;
			$field->phoneFormat = $field->origPhoneFormat;
		}
		return $result;
	}, 11, 4 );

	/**
	 * Remove the format-specific validation message that is added by Gravity Forms when a Phone field is marked as having
     * failed validation. See the more detailed comment above the setting of the `origPhoneFormat` property above.
	 */
    add_filter( 'gform_field_content', function( $content, $field ) {

	    if ( $field->get_input_type() !== 'phone' || $field->phoneFormat !== 'standard' || $field->gpapfEnable ) {
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
