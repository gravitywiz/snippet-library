<?php
/**
 * Gravity Perks // Populate Anything // Convert ACF Date Into Gravity Forms Date
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Instructions:
 *
 * 1. Download and install as a plugin.
 * 2. Go to the field settings for the Gravity Forms Date field you are populating from the ACF Date field.
 * 3. Add "gppa-format-acf-date" to the "Custom CSS Class" setting (on the Appearance tab).
 *
 * Plugin Name:  GPPA ACF Date to GF Date
 * Plugin URI:   http:///gravitywiz.com.com/documentation/gravity-forms-populate-anything/
 * Description:  This snippet will format the value from a ACF Date field that is retrieved from the database and convert it into a Gravity Forms format Date field.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   http://gravitywiz.com
 */
add_filter( 'gppa_process_template_value', function( $template_value, $field, $template_name, $populate, $object, $object_type, $objects ) {

	if ( strpos( $field->cssClass, 'gppa-format-acf-date' ) === false ) {
		return $template_value;
	}

	return wp_date( 'd/m/Y', strtotime( $template_value ) );
}, 10, 7 );
