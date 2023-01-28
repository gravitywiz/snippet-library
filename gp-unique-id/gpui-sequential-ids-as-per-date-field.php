<?php
/**
 * GP Unique ID // Gravity Perks // Creates Sequential IDs as per Date Field selected.
 * http://gravitywiz.com/
 *
 * Make the sequence for a given Unique ID field specific to the date field value. The sequence will automatically
 * generate Unique IDs for a particular date.
 */
// Update "123" to your form ID and "4" to your Unique ID field.
add_filter( 'gpui_unique_id_attributes_123_4', 'gw_generate_unique_id_as_per_date_field', 10, 4 );
function gw_generate_unique_id_as_per_date_field( $atts, $form_id, $field_id, $entry ) {
	// Replace 5 with the Date field ID used for targetting Unique ID generation
	$date_field_id = 5;

	// Remove any special character like / - . which can be in the date field value.
	$date_string = str_replace( array( '/', '-', '.' ), '', $entry[ $date_field_id ] );

	// Update UID Attributes with the date string.
	$atts['form_id'] = (int) $date_string;

	return $atts;
}
