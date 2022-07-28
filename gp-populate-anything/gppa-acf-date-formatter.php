<?php
/**
 * Gravity Perks // Populate Anything // Convert ACF Date Into Gravity Forms Date
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Plugin Name:  GPPA ACF Relationships
 * Plugin URI:   http:///gravitywiz.com.com/documentation/gravity-forms-populate-anything/
 * Description:  This snippet will format the value from a ACF Date field that is retrieved from the database and convert it into a Gravity Forms format Date field.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   http://gravitywiz.com
 *
 * Add "gppa-format-acf-date" to the "Custom CSS Class" setting (on the Appearance tab).
 */
// Update "123" to your form ID.
add_filter( 'gppa_process_template_value_123', function( $template_value, $field, $template_name, $populate, $object, $object_type, $objects ) {

    if ( strpos( $field->cssClass, 'gppa-format-acf-date' ) === false ) {
        return $template_value;
    }
    return date( 'd/m/Y', strtotime( $template_value ) );
}, 10, 7 );
