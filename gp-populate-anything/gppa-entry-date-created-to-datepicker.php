<?php
/**
 * Gravity Perks // Populate Anything // Convert Entry Date Created to Datepicker Format
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Convert entry's "date_created" value from the database format (e.g. ISO 8601) to the datepicker format (e.g. m/d/Y)
 * when populating into a Date field.
 */
add_filter( 'gppa_process_template_value', function ( $value, $field, $template_name, $populate, $object, $object_type, $objects, $template ) {
	if ( $template === 'date_created' && $field->get_input_type() === 'date' && $field->dateType === 'datepicker' ) {
		$value = date_format( date_create_from_format( 'Y-m-d H:i:s', $value ), 'm/d/Y' );
	}
	return $value;
}, 10, 8 );
