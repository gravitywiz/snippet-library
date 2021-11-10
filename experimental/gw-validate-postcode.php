<?php
/**
 * Gravity Wiz // Gravity Forms // Validate UK Postcode
 * https://gravitywiz.com/
 *
 * Check to confirm if the postcode entered in the Address field is a valid UK postcode.
 */
// Update "123" to the Form ID and "4" to the Address field ID.
add_filter('gform_field_validation_123_4', 'custom_zip_validation', 10, 5);
function custom_zip_validation($result, $value, $form, $field)
{
    $country_to_check = 'United Kingdom';
    $regex_pattern = '/^([A-Z]{1,2}\d[A-Z\d]? ?\d[A-Z]{2}|GIR ?0A{2})$/';
    $country = rgar($value, $field->id . '.6');
    if ($country == $country_to_check) {
        if ($result['is_valid']) {
            $zip_value = rgar($value, $field->id . '.5');
            if (!preg_match($regex_pattern, strtoupper($zip_value))) {
                $result['is_valid'] = false;
                $result['message'] = 'Please enter a valid UK postcode.';
            }
        }
    }
    return $result;
}
